<?php

namespace App\Services\Google;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutscraperService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.outscraper.com';

    public function __construct()
    {
        $this->apiKey = config('services.outscraper.api_key');
    }

    /**
     * Search for a place by name and location
     */
    public function searchPlace(string $query, string $location = null): array
    {
        $searchQuery = $location ? "{$query}, {$location}" : $query;

        $response = Http::timeout(30)
            ->withHeaders(['X-API-KEY' => $this->apiKey])
            ->get($this->baseUrl . '/maps/search-v3', [
                'query' => $searchQuery,
                'limit' => 10,
                'language' => 'ar',
                'region' => 'SA',
            ]);

        if (!$response->successful()) {
            Log::error('Outscraper place search failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في البحث عن الموقع');
        }

        $data = $response->json();
        
        return $data['data'] ?? [];
    }

    /**
     * Get place details by Google Place ID
     */
    public function getPlaceDetails(string $placeId): ?array
    {
        $response = Http::timeout(30)
            ->withHeaders(['X-API-KEY' => $this->apiKey])
            ->get($this->baseUrl . '/maps/details-v2', [
                'query' => $placeId,
                'language' => 'ar',
            ]);

        if (!$response->successful()) {
            Log::error('Outscraper place details failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في جلب تفاصيل الموقع');
        }

        $data = $response->json();
        
        return $data['data'][0] ?? null;
    }

    /**
     * Collect reviews for a place
     */
    public function collectReviews(string $placeId, array $options = []): array
    {
        $defaultOptions = [
            'reviewsLimit' => 0,  // 0 = all reviews
            'sort' => 'newest',
            'cutoff' => null,     // Unix timestamp to filter reviews after this date
        ];

        $options = array_merge($defaultOptions, $options);

        $params = [
            'query' => $placeId,
            'limit' => 1,
            'reviewsLimit' => $options['reviewsLimit'],
            'async' => 'false',
            'fields' => 'name,rating,reviews_count,reviews_data',
            'sort' => $options['sort'],
            'language' => 'ar',
        ];

        if ($options['cutoff']) {
            $params['cutoff'] = $options['cutoff'];
        }

        $response = Http::timeout(0)  // No timeout for large review sets
            ->withHeaders(['X-API-KEY' => $this->apiKey])
            ->get($this->baseUrl . '/maps/reviews-v2', $params);

        if (!$response->successful()) {
            // Check for billing issues
            if ($response->status() === 402) {
                throw new Exception('رصيد Outscraper غير كافي');
            }
            
            Log::error('Outscraper review collection failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في جلب المراجعات من Outscraper');
        }

        $data = $response->json();

        if (!$data || !isset($data['data']) || empty($data['data'])) {
            return [];
        }

        $placeData = $data['data'][0];

        // Get reviews from various possible fields
        $reviews = $placeData['reviews_data'] 
            ?? $placeData['reviews'] 
            ?? $placeData['review_data'] 
            ?? [];

        return $this->transformReviews($reviews, $placeId);
    }

    /**
     * Transform Outscraper reviews to standard format
     */
    private function transformReviews(array $apiReviews, string $placeId): array
    {
        $reviews = [];

        foreach ($apiReviews as $apiReview) {
            $reviewText = trim($apiReview['review_text'] ?? '');
            $reviewDate = $this->parseReviewDate($apiReview['review_datetime_utc'] ?? $apiReview['review_date'] ?? null);

            $review = [
                'id' => $this->generateReviewId($apiReview),
                'place_id' => $placeId,
                'author_name' => $apiReview['author_title'] ?? $apiReview['author_name'] ?? 'مجهول',
                'author_photo' => $apiReview['author_thumbnail'] ?? null,
                'rating' => intval($apiReview['review_rating'] ?? 5),
                'text' => $reviewText,
                'review_date' => $reviewDate,
                'language' => $this->detectLanguage($reviewText),
                'source' => 'outscraper',
                'quality_score' => $this->assessQuality($reviewText),
                'metadata' => [
                    'outscraper_id' => $apiReview['review_id'] ?? null,
                    'author_id' => $apiReview['author_id'] ?? null,
                    'author_url' => $apiReview['author_url'] ?? null,
                    'review_likes' => $apiReview['review_likes'] ?? 0,
                    'owner_answer' => $apiReview['owner_answer'] ?? null,
                    'owner_answer_timestamp' => $apiReview['owner_answer_timestamp'] ?? null,
                ],
            ];

            if ($this->isValidReview($review)) {
                $reviews[] = $review;
            }
        }

        return $reviews;
    }

    /**
     * Parse review date from various formats
     */
    private function parseReviewDate(?string $dateString): string
    {
        if (empty($dateString)) {
            return now()->toDateTimeString();
        }

        try {
            return now()->parse($dateString)->toDateTimeString();
        } catch (Exception $e) {
            return now()->toDateTimeString();
        }
    }

    /**
     * Detect language (Arabic or English)
     */
    private function detectLanguage(string $text): string
    {
        if (empty($text)) {
            return 'ar';
        }

        // Check for Arabic characters
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }

        return 'en';
    }

    /**
     * Assess review quality based on text length
     */
    private function assessQuality(string $text): float
    {
        if (empty($text)) {
            return 0.7;
        }

        $score = 0.8;

        if (mb_strlen($text) > 50) {
            $score += 0.1;
        }
        if (mb_strlen($text) > 150) {
            $score += 0.1;
        }

        return min(1.0, $score);
    }

    /**
     * Validate review data
     */
    private function isValidReview(array $review): bool
    {
        if (empty($review['author_name'])) {
            return false;
        }
        if ($review['rating'] < 1 || $review['rating'] > 5) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique review ID
     */
    private function generateReviewId(array $apiReview): string
    {
        $baseId = $apiReview['review_id'] ?? $apiReview['author_id'] ?? uniqid();
        return 'outscraper_' . $baseId . '_' . time();
    }

    /**
     * Check API balance/credits
     */
    public function checkBalance(): array
    {
        $response = Http::withHeaders(['X-API-KEY' => $this->apiKey])
            ->get($this->baseUrl . '/billing/usage');

        if (!$response->successful()) {
            return ['available' => false, 'credits' => 0];
        }

        $data = $response->json();
        
        return [
            'available' => true,
            'credits' => $data['credits'] ?? 0,
            'used' => $data['used'] ?? 0,
        ];
    }
}
