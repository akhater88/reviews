<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutscraperService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('outscraper.api_key');
        $this->baseUrl = config('outscraper.base_url');
    }

    /**
     * Fetch Google reviews for a place.
     *
     * @param string $placeId Google Place ID
     * @param int $limit Maximum number of reviews to fetch
     * @param string|null $language Filter by language (null = all)
     * @param string $sort Sort order ('newest' or 'most_relevant')
     * @return array ['success' => bool, 'reviews' => array, 'error' => string|null]
     */
    public function fetchReviews(
        string $placeId,
        int $limit = null,
        ?string $language = null,
        string $sort = null
    ): array {
        $limit = $limit ?? config('outscraper.defaults.reviews_limit', 100);
        $sort = $sort ?? config('outscraper.defaults.sort', 'newest');
        $language = $language ?? config('outscraper.defaults.language', '');

        Log::info('OutscraperService: Fetching reviews', [
            'place_id' => $placeId,
            'limit' => $limit,
            'language' => $language,
            'sort' => $sort,
        ]);

        // In development without API key, return mock data
        if (!$this->apiKey && !app()->isProduction()) {
            Log::info('OutscraperService: Using mock data (no API key)');
            return $this->getMockReviews($placeId, $limit);
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
            ])
            ->timeout(config('outscraper.timeout.request', 120))
            ->connectTimeout(config('outscraper.timeout.connect', 30))
            ->get("{$this->baseUrl}/maps/reviews-v3", [
                'query' => $placeId,
                'reviewsLimit' => $limit,
                'language' => $language ?: null,
                'sort' => $sort,
                'ignoreEmpty' => true,
            ]);

            if (!$response->successful()) {
                Log::error('OutscraperService: API request failed', [
                    'place_id' => $placeId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'reviews' => [],
                    'error' => 'Failed to fetch reviews: ' . $response->status(),
                ];
            }

            $data = $response->json();

            // Outscraper returns an array of places, get the first one
            $placeData = $data['data'][0] ?? $data[0] ?? null;

            if (!$placeData) {
                return [
                    'success' => false,
                    'reviews' => [],
                    'error' => 'No place data found',
                ];
            }

            $reviews = $placeData['reviews_data'] ?? [];

            Log::info('OutscraperService: Reviews fetched successfully', [
                'place_id' => $placeId,
                'reviews_count' => count($reviews),
            ]);

            return [
                'success' => true,
                'reviews' => $reviews,
                'place_info' => [
                    'name' => $placeData['name'] ?? null,
                    'address' => $placeData['full_address'] ?? null,
                    'rating' => $placeData['rating'] ?? null,
                    'reviews_count' => $placeData['reviews'] ?? 0,
                ],
                'error' => null,
            ];

        } catch (Exception $e) {
            Log::error('OutscraperService: Exception', [
                'place_id' => $placeId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'reviews' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Search for places by query.
     *
     * @param string $query Search query (business name, address, etc.)
     * @param int $limit Maximum number of results
     * @return array ['success' => bool, 'places' => array, 'error' => string|null]
     */
    public function searchPlaces(string $query, int $limit = 5): array
    {
        Log::info('OutscraperService: Searching places', [
            'query' => $query,
            'limit' => $limit,
        ]);

        // In development without API key, return mock data
        if (!$this->apiKey && !app()->isProduction()) {
            Log::info('OutscraperService: Using mock places (no API key)');
            return $this->getMockPlaces($query);
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
            ])
            ->timeout(config('outscraper.timeout.request', 120))
            ->connectTimeout(config('outscraper.timeout.connect', 30))
            ->get("{$this->baseUrl}/maps/search-v3", [
                'query' => $query,
                'limit' => $limit,
            ]);

            if (!$response->successful()) {
                Log::error('OutscraperService: Search failed', [
                    'query' => $query,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'places' => [],
                    'error' => 'Search failed: ' . $response->status(),
                ];
            }

            $data = $response->json();
            $places = $data['data'] ?? $data ?? [];

            return [
                'success' => true,
                'places' => array_map(fn($place) => [
                    'place_id' => $place['place_id'] ?? null,
                    'name' => $place['name'] ?? null,
                    'address' => $place['full_address'] ?? null,
                    'rating' => $place['rating'] ?? null,
                    'reviews_count' => $place['reviews'] ?? 0,
                    'type' => $place['type'] ?? null,
                ], $places),
                'error' => null,
            ];

        } catch (Exception $e) {
            Log::error('OutscraperService: Search exception', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'places' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get mock reviews for development.
     */
    protected function getMockReviews(string $placeId, int $limit): array
    {
        $reviews = [];
        $sampleTexts = [
            'خدمة ممتازة وطعام لذيذ! سأعود بالتأكيد.',
            'المكان نظيف والموظفين ودودين جداً.',
            'الطعام كان بارداً والخدمة بطيئة.',
            'أفضل مطعم في المنطقة! أنصح به بشدة.',
            'الأسعار مرتفعة مقارنة بالجودة.',
            'تجربة رائعة! الأجواء مميزة.',
            'لم يعجبني الطعام، لن أعود مرة أخرى.',
            'خدمة سريعة وطعام طازج.',
        ];

        $count = min($limit, 20);
        for ($i = 0; $i < $count; $i++) {
            $reviews[] = [
                'review_id' => 'mock_' . $placeId . '_' . $i,
                'author_title' => 'مستخدم ' . ($i + 1),
                'author_image' => null,
                'review_rating' => rand(1, 5),
                'review_text' => $sampleTexts[array_rand($sampleTexts)],
                'review_datetime_utc' => now()->subDays(rand(1, 365))->toIso8601String(),
                'review_link' => null,
            ];
        }

        return [
            'success' => true,
            'reviews' => $reviews,
            'place_info' => [
                'name' => 'مطعم تجريبي',
                'address' => 'الرياض، المملكة العربية السعودية',
                'rating' => 4.2,
                'reviews_count' => count($reviews),
            ],
            'error' => null,
        ];
    }

    /**
     * Get mock places for development.
     */
    protected function getMockPlaces(string $query): array
    {
        return [
            'success' => true,
            'places' => [
                [
                    'place_id' => 'ChIJmock1234567890',
                    'name' => $query . ' - فرع الرياض',
                    'address' => 'حي العليا، الرياض',
                    'rating' => 4.5,
                    'reviews_count' => 150,
                    'type' => 'restaurant',
                ],
                [
                    'place_id' => 'ChIJmock0987654321',
                    'name' => $query . ' - فرع جدة',
                    'address' => 'حي الحمراء، جدة',
                    'rating' => 4.2,
                    'reviews_count' => 89,
                    'type' => 'restaurant',
                ],
            ],
            'error' => null,
        ];
    }
}
