<?php

namespace App\Services\Google;

use App\Models\GoogleToken;
use App\Models\Tenant;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleBusinessService
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    
    // Google API endpoints
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const REVOKE_URL = 'https://oauth2.googleapis.com/revoke';
    private const USERINFO_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';
    private const BUSINESS_API_URL = 'https://mybusinessbusinessinformation.googleapis.com/v1';
    private const REVIEWS_API_URL = 'https://mybusiness.googleapis.com/v4';

    // Required OAuth scopes
    private const SCOPES = [
        'https://www.googleapis.com/auth/business.manage',  // Manage business info
        'https://www.googleapis.com/auth/userinfo.email',   // Get user email
        'https://www.googleapis.com/auth/userinfo.profile', // Get user profile
    ];

    public function __construct()
    {
        $this->clientId = config('services-google.google.client_id');
        $this->clientSecret = config('services-google.google.client_secret');
        $this->redirectUri = config('services-google.google.redirect_uri');
    }

    /**
     * Generate OAuth authorization URL
     */
    public function getAuthUrl(string $state = null): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'access_type' => 'offline',  // Get refresh token
            'prompt' => 'consent',        // Force consent to get refresh token
            'include_granted_scopes' => 'true',
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for tokens
     */
    public function exchangeCodeForTokens(string $code): array
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
        ]);

        if (!$response->successful()) {
            Log::error('Google OAuth token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في تبادل رمز التفويض: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $response = Http::asForm()->post(self::TOKEN_URL, [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            Log::error('Google OAuth token refresh failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في تحديث رمز الوصول');
        }

        return $response->json();
    }

    /**
     * Revoke access token
     */
    public function revokeToken(string $token): bool
    {
        $response = Http::asForm()->post(self::REVOKE_URL, [
            'token' => $token,
        ]);

        return $response->successful();
    }

    /**
     * Get user info from Google
     */
    public function getUserInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get(self::USERINFO_URL);

        if (!$response->successful()) {
            throw new Exception('فشل في جلب معلومات المستخدم');
        }

        return $response->json();
    }

    /**
     * Store tokens for a tenant
     */
    public function storeTokens(Tenant $tenant, array $tokenData, array $userInfo = []): GoogleToken
    {
        return GoogleToken::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
                'google_email' => $userInfo['email'] ?? null,
                'google_account_id' => $userInfo['id'] ?? null,
                'google_account_name' => $userInfo['name'] ?? null,
                'scopes' => $tokenData['scope'] ?? self::SCOPES,
                'status' => 'active',
                'connected_at' => now(),
            ]
        );
    }

    /**
     * Get valid access token for a tenant (refreshing if needed)
     */
    public function getValidAccessToken(Tenant $tenant): ?string
    {
        $googleToken = GoogleToken::where('tenant_id', $tenant->id)->first();

        if (!$googleToken) {
            return null;
        }

        if ($googleToken->status === 'revoked') {
            return null;
        }

        // Refresh if needed
        if ($googleToken->needsRefresh() && $googleToken->refresh_token) {
            try {
                $newTokens = $this->refreshAccessToken($googleToken->refresh_token);
                
                $googleToken->update([
                    'access_token' => $newTokens['access_token'],
                    'token_expires_at' => now()->addSeconds($newTokens['expires_in'] ?? 3600),
                    'status' => 'active',
                ]);
            } catch (Exception $e) {
                Log::error('Failed to refresh Google token', ['error' => $e->getMessage()]);
                $googleToken->markExpired();
                return null;
            }
        }

        $googleToken->touchLastUsed();
        return $googleToken->access_token;
    }

    /**
     * Get all Google Business accounts for the authenticated user
     */
    public function getAccounts(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get(self::BUSINESS_API_URL . '/accounts');

        if (!$response->successful()) {
            Log::error('Failed to fetch Google Business accounts', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في جلب حسابات Google Business');
        }

        return $response->json()['accounts'] ?? [];
    }

    /**
     * Get all locations (branches) for an account
     */
    public function getLocations(string $accessToken, string $accountId): array
    {
        $response = Http::withToken($accessToken)
            ->get(self::BUSINESS_API_URL . "/accounts/{$accountId}/locations", [
                'readMask' => 'name,title,storefrontAddress,metadata,profile,phoneNumbers,websiteUri',
            ]);

        if (!$response->successful()) {
            Log::error('Failed to fetch Google Business locations', [
                'status' => $response->status(),
                'body' => $response->body(),
                'account_id' => $accountId,
            ]);
            throw new Exception('فشل في جلب فروع Google Business');
        }

        return $response->json()['locations'] ?? [];
    }

    /**
     * Get reviews for a location
     */
    public function getReviews(string $accessToken, string $accountId, string $locationId, int $pageSize = 50, string $pageToken = null): array
    {
        $params = ['pageSize' => $pageSize];
        
        if ($pageToken) {
            $params['pageToken'] = $pageToken;
        }

        $response = Http::withToken($accessToken)
            ->get(self::REVIEWS_API_URL . "/accounts/{$accountId}/locations/{$locationId}/reviews", $params);

        if (!$response->successful()) {
            Log::error('Failed to fetch Google reviews', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في جلب المراجعات');
        }

        return $response->json();
    }

    /**
     * Reply to a review
     */
    public function replyToReview(string $accessToken, string $reviewName, string $comment): array
    {
        $response = Http::withToken($accessToken)
            ->put(self::REVIEWS_API_URL . "/{$reviewName}/reply", [
                'comment' => $comment,
            ]);

        if (!$response->successful()) {
            Log::error('Failed to reply to review', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في إرسال الرد');
        }

        return $response->json();
    }

    /**
     * Delete a review reply
     */
    public function deleteReviewReply(string $accessToken, string $reviewName): bool
    {
        $response = Http::withToken($accessToken)
            ->delete(self::REVIEWS_API_URL . "/{$reviewName}/reply");

        return $response->successful();
    }

    /**
     * Check if tenant has active Google connection
     */
    public function isConnected(Tenant $tenant): bool
    {
        $token = GoogleToken::where('tenant_id', $tenant->id)->first();
        return $token && $token->isActive();
    }

    /**
     * Disconnect Google account for tenant
     */
    public function disconnect(Tenant $tenant): bool
    {
        $token = GoogleToken::where('tenant_id', $tenant->id)->first();
        
        if (!$token) {
            return true;
        }

        // Try to revoke token with Google
        if ($token->access_token) {
            $this->revokeToken($token->access_token);
        }

        // Delete local token
        $token->delete();

        return true;
    }
}
