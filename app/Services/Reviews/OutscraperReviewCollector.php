<?php

namespace App\Services\Reviews;

use App\Models\Branch;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutscraperReviewCollector
{
    private string $apiKey;
    private string $baseUrl = 'https://api.outscraper.com';

    public function __construct()
    {
        $this->apiKey = config('services-google.outscraper.api_key');
    }

    /**
     * Collect reviews from Outscraper API
     *
     * @param Branch $branch The branch to collect reviews for
     * @param int|null $cutoffTimestamp Only get reviews after this timestamp (for incremental sync)
     * @param int $reviewsLimit 0 = all reviews, or specify a number
     * @return array Transformed reviews ready for database insertion
     */
    public function collect(Branch $branch, ?int $cutoffTimestamp = null, int $reviewsLimit = 0): array
    {
        // Validate branch has Google Place ID
        if (empty($branch->google_place_id)) {
            throw new Exception("Branch {$branch->id} has no Google Place ID");
        }

        // Build request parameters
        $params = [
            'query' => $branch->google_place_id,
            'limit' => 1,                    // Number of places (always 1)
            'reviewsLimit' => $reviewsLimit, // 0 = all reviews
            'async' => 'false',              // Wait for results
            'fields' => 'name,rating,reviews_count,reviews_data',
            'sort' => 'newest',
            'language' => 'ar',
        ];

        // Add cutoff for incremental sync
        if ($cutoffTimestamp) {
            $params['cutoff'] = $cutoffTimestamp;
        }

        Log::info('Outscraper: Starting review collection', [
            'branch_id' => $branch->id,
            'branch_name' => $branch->name,
            'place_id' => $branch->google_place_id,
            'cutoff' => $cutoffTimestamp ? date('Y-m-d H:i:s', $cutoffTimestamp) : 'none',
            'reviews_limit' => $reviewsLimit ?: 'all',
        ]);

        try {
            // Make API request (long timeout for large review sets)
            $response = Http::timeout(300)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->get($this->baseUrl . '/maps/reviews-v2', $params);

            // Handle errors
            if (!$response->successful()) {
                $this->handleApiError($response, $branch);
            }

            // Parse response
            $data = $response->json();

            if (!$data || !isset($data['data']) || empty($data['data'])) {
                Log::warning('Outscraper: No data returned', [
                    'branch_id' => $branch->id,
                    'response' => $data,
                ]);
                return [];
            }

            // Extract reviews from response
            $placeData = $data['data'][0];
            $rawReviews = $placeData['reviews_data']
                ?? $placeData['reviews']
                ?? $placeData['review_data']
                ?? [];

            Log::info('Outscraper: Reviews fetched', [
                'branch_id' => $branch->id,
                'total_reviews' => $placeData['reviews_count'] ?? 'unknown',
                'fetched_reviews' => count($rawReviews),
            ]);

            // Transform to our format
            return $this->transformReviews($rawReviews, $branch);

        } catch (Exception $e) {
            Log::error('Outscraper: Collection failed', [
                'branch_id' => $branch->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle API errors
     */
    private function handleApiError($response, Branch $branch): void
    {
        $status = $response->status();
        $body = $response->body();

        Log::error('Outscraper: API error', [
            'branch_id' => $branch->id,
            'status' => $status,
            'body' => $body,
        ]);

        if ($status === 402) {
            throw new Exception('Outscraper billing error: Insufficient credits. Please top up your Outscraper account.');
        }

        if ($status === 401) {
            throw new Exception('Outscraper authentication error: Invalid API key.');
        }

        if ($status === 429) {
            throw new Exception('Outscraper rate limit: Too many requests. Please try again later.');
        }

        throw new Exception("Outscraper API error: {$status} - {$body}");
    }

    /**
     * Transform Outscraper reviews to TABsense format
     */
    private function transformReviews(array $rawReviews, Branch $branch): array
    {
        return collect($rawReviews)
            ->map(function ($review) use ($branch) {
                $text = trim($review['review_text'] ?? '');

                return [
                    // IDs
                    'outscraper_review_id' => $review['review_id'] ?? $this->generateFallbackId($review),
                    'google_review_id' => null,

                    // Author
                    'reviewer_name' => $review['author_title'] ?? $review['author_name'] ?? 'مجهول',
                    'reviewer_photo_url' => $review['author_thumbnail'] ?? null,
                    'author_url' => $review['author_url'] ?? null,

                    // Content
                    'rating' => (int) ($review['review_rating'] ?? 5),
                    'text' => $text ?: null,
                    'language' => $this->detectLanguage($text),

                    // Dates
                    'review_date' => $this->parseDate($review['review_datetime_utc'] ?? $review['review_date'] ?? null),
                    'collected_at' => now(),

                    // Source
                    'source' => 'outscraper',

                    // Owner reply
                    'owner_reply' => $review['owner_answer'] ?? null,
                    'owner_reply_date' => isset($review['owner_answer_timestamp'])
                        ? now()->createFromTimestamp($review['owner_answer_timestamp'])
                        : null,
                    'replied_via_tabsense' => false,

                    // Reply status based on owner_reply
                    'is_replied' => !empty($review['owner_answer']),
                    'needs_reply' => empty($review['owner_answer']),

                    // Quality
                    'quality_score' => $this->calculateQualityScore($text),
                    'is_spam' => false,
                    'is_hidden' => false,

                    // Metadata
                    'metadata' => [
                        'review_likes' => $review['review_likes'] ?? 0,
                        'author_id' => $review['author_id'] ?? null,
                        'review_timestamp' => $review['review_timestamp'] ?? null,
                        'owner_answer_timestamp' => $review['owner_answer_timestamp'] ?? null,
                    ],
                ];
            })
            ->filter(function ($review) {
                // Validate: must have author name and valid rating
                return !empty($review['reviewer_name'])
                    && $review['rating'] >= 1
                    && $review['rating'] <= 5;
            })
            ->values()
            ->toArray();
    }

    /**
     * Generate fallback ID if review_id is missing
     */
    private function generateFallbackId(array $review): string
    {
        $base = ($review['author_id'] ?? '')
            . ($review['review_timestamp'] ?? time())
            . ($review['review_rating'] ?? 5);
        return 'outscraper_' . md5($base);
    }

    /**
     * Detect language (Arabic or English)
     */
    private function detectLanguage(string $text): string
    {
        if (empty($text)) {
            return 'ar'; // Default to Arabic for Saudi market
        }

        // Check for Arabic characters
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }

        return 'en';
    }

    /**
     * Parse date from various formats
     */
    private function parseDate(?string $dateString): \DateTime
    {
        if (empty($dateString)) {
            return now()->toDateTime();
        }

        try {
            return new \DateTime($dateString);
        } catch (Exception $e) {
            Log::warning('Outscraper: Could not parse date', ['date' => $dateString]);
            return now()->toDateTime();
        }
    }

    /**
     * Calculate quality score based on text length and content
     */
    private function calculateQualityScore(string $text): float
    {
        if (empty($text)) {
            return 0.50; // Star-only reviews get lower score
        }

        $length = mb_strlen($text);

        if ($length > 200) return 1.00;
        if ($length > 100) return 0.90;
        if ($length > 50) return 0.80;
        if ($length > 20) return 0.70;

        return 0.60;
    }

    /**
     * Check API balance/credits (optional utility method)
     */
    public function checkBalance(): array
    {
        try {
            $response = Http::withHeaders(['X-API-KEY' => $this->apiKey])
                ->get($this->baseUrl . '/billing/usage');

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return ['success' => false, 'error' => 'Failed to fetch balance'];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
