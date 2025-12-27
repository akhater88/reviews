<?php

namespace App\Services;

use App\Enums\ReplyStatus;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Models\GoogleConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleReplyService
{
    /**
     * Publish reply to Google Business Profile.
     */
    public function publishReply(Review $review, string $replyText): array
    {
        // Validate review has Google ID
        if (empty($review->google_review_id)) {
            throw new Exception('هذه المراجعة ليست من Google ولا يمكن الرد عليها مباشرة');
        }

        // Get Google connection for this branch
        $connection = GoogleConnection::where('branch_id', $review->branch_id)
            ->where('status', 'connected')
            ->first();

        if (!$connection) {
            throw new Exception('لا يوجد اتصال Google نشط لهذا الفرع. يرجى ربط حساب Google Business أولاً.');
        }

        // Validate connection has required fields
        if (empty($connection->google_account_id) || empty($connection->google_location_name)) {
            throw new Exception('بيانات اتصال Google غير مكتملة. يرجى إعادة ربط الحساب.');
        }

        // Refresh token if needed
        $accessToken = $this->getValidAccessToken($connection);

        // Build the API request
        $accountId = $connection->google_account_id;
        $locationName = $connection->google_location_name;
        $reviewId = $review->google_review_id;

        // Google Business Profile API v1 endpoint
        // Format: accounts/{account}/locations/{location}/reviews/{review}/reply
        $url = "https://mybusiness.googleapis.com/v4/{$locationName}/reviews/{$reviewId}/reply";

        Log::info('Publishing reply to Google', [
            'review_id' => $review->id,
            'google_review_id' => $reviewId,
            'url' => $url,
        ]);

        try {
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->put($url, [
                    'comment' => $replyText,
                ]);

            if (!$response->successful()) {
                $errorMessage = $response->json('error.message', 'خطأ غير معروف من Google');
                $errorCode = $response->json('error.code', $response->status());

                Log::error('Google Reply API Error', [
                    'status' => $response->status(),
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'body' => $response->body(),
                    'review_id' => $review->id,
                ]);

                // Handle specific error codes
                $userMessage = match($errorCode) {
                    401, 403 => 'انتهت صلاحية الاتصال بـ Google. يرجى إعادة ربط الحساب.',
                    404 => 'لم يتم العثور على المراجعة في Google. قد تكون محذوفة.',
                    429 => 'تم تجاوز حد الطلبات. يرجى المحاولة بعد قليل.',
                    default => 'فشل في نشر الرد: ' . $errorMessage,
                };

                throw new Exception($userMessage);
            }

            // Increment reply count for the connection
            $connection->incrementReplyCount();

            Log::info('Reply published to Google successfully', [
                'review_id' => $review->id,
                'google_review_id' => $reviewId,
                'response' => $response->json(),
            ]);

            return [
                'success' => true,
                'reply_id' => $response->json('name'),
                'comment' => $response->json('comment'),
            ];

        } catch (Exception $e) {
            Log::error('Failed to publish reply to Google', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update existing reply on Google.
     */
    public function updateReply(Review $review, string $replyText): array
    {
        // Same as publish - PUT request updates the reply
        return $this->publishReply($review, $replyText);
    }

    /**
     * Delete reply from Google.
     */
    public function deleteReply(Review $review): bool
    {
        if (empty($review->google_review_id)) {
            throw new Exception('هذه المراجعة ليست من Google');
        }

        $connection = GoogleConnection::where('branch_id', $review->branch_id)
            ->where('status', 'connected')
            ->first();

        if (!$connection) {
            throw new Exception('لا يوجد اتصال Google نشط');
        }

        $accessToken = $this->getValidAccessToken($connection);

        $url = "https://mybusiness.googleapis.com/v4/{$connection->google_location_name}/reviews/{$review->google_review_id}/reply";

        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->delete($url);

        if ($response->successful()) {
            Log::info('Reply deleted from Google', ['review_id' => $review->id]);
            return true;
        }

        Log::error('Failed to delete reply from Google', [
            'review_id' => $review->id,
            'response' => $response->body(),
        ]);

        return false;
    }

    /**
     * Get valid access token, refreshing if needed.
     */
    private function getValidAccessToken(GoogleConnection $connection): string
    {
        // Check if token is expired
        if ($connection->isTokenExpired()) {
            Log::info('Refreshing expired Google token', ['connection_id' => $connection->id]);

            if (!$this->refreshAccessToken($connection)) {
                throw new Exception('فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Google.');
            }
        }

        return $connection->access_token;
    }

    /**
     * Refresh access token using refresh token.
     */
    private function refreshAccessToken(GoogleConnection $connection): bool
    {
        if (empty($connection->refresh_token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'refresh_token' => $connection->refresh_token,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $connection->update([
                    'access_token' => $response->json('access_token'),
                    'token_expires_at' => now()->addSeconds($response->json('expires_in', 3600)),
                    'status' => 'connected',
                ]);

                Log::info('Google token refreshed successfully', ['connection_id' => $connection->id]);
                return true;
            }

            Log::error('Failed to refresh Google token', [
                'connection_id' => $connection->id,
                'response' => $response->body(),
            ]);

            // Mark connection as expired
            $connection->markAsExpired();

            return false;

        } catch (Exception $e) {
            Log::error('Exception refreshing Google token', [
                'connection_id' => $connection->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if branch can publish replies (has valid Google connection).
     */
    public function canPublish(int $branchId): bool
    {
        return GoogleConnection::where('branch_id', $branchId)
            ->where('status', 'connected')
            ->whereNotNull('google_account_id')
            ->whereNotNull('google_location_name')
            ->exists();
    }

    /**
     * Get connection status message.
     */
    public function getConnectionStatus(int $branchId): array
    {
        $connection = GoogleConnection::where('branch_id', $branchId)->first();

        if (!$connection) {
            return [
                'connected' => false,
                'message' => 'لم يتم ربط حساب Google Business',
                'can_publish' => false,
            ];
        }

        if ($connection->status !== 'connected') {
            return [
                'connected' => false,
                'message' => match($connection->status) {
                    'expired' => 'انتهت صلاحية الاتصال',
                    'disconnected' => 'تم قطع الاتصال',
                    default => 'اتصال Google غير نشط',
                },
                'can_publish' => false,
            ];
        }

        if ($connection->isTokenExpired()) {
            return [
                'connected' => true,
                'message' => 'انتهت صلاحية الرمز - سيتم التجديد تلقائياً',
                'can_publish' => true,
            ];
        }

        return [
            'connected' => true,
            'message' => 'متصل بـ Google Business',
            'can_publish' => true,
        ];
    }
}
