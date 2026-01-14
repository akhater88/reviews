<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OutscraperService
{
    protected ?string $apiKey;
    protected string $baseUrl;

    // Polling settings for async requests
    protected int $maxPollingAttempts = 30;  // Max polling attempts
    protected int $pollingDelaySeconds = 5;   // Delay between polls

    public function __construct()
    {
        $this->apiKey = config('outscraper.api_key');
        $this->baseUrl = config('outscraper.base_url', 'https://api.app.outscraper.com');
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

        // If no API key configured, return mock data (allows testing without subscription)
        if (!$this->apiKey) {
            Log::info('OutscraperService: Using mock data (no API key configured)');
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

            Log::info('OutscraperService: API response received', [
                'place_id' => $placeId,
                'has_data' => isset($data['data']),
                'status' => $data['status'] ?? $data['data']['status'] ?? 'unknown',
            ]);

            // Check if response is async (Pending status)
            if ($this->isAsyncResponse($data)) {
                Log::info('OutscraperService: Async response, starting polling', [
                    'place_id' => $placeId,
                    'results_location' => $data['data']['results_location'] ?? $data['results_location'] ?? null,
                ]);

                return $this->pollForResults($placeId, $data);
            }

            // Process immediate response
            return $this->processReviewsData($placeId, $data);

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
     * Check if the response indicates an async/pending request.
     */
    protected function isAsyncResponse(array $data): bool
    {
        // Check various possible response structures
        $status = $data['status'] ?? $data['data']['status'] ?? null;

        return $status === 'Pending' || $status === 'pending';
    }

    /**
     * Poll the results_location URL until data is ready.
     */
    protected function pollForResults(string $placeId, array $initialData): array
    {
        $resultsLocation = $initialData['data']['results_location']
            ?? $initialData['results_location']
            ?? null;

        if (!$resultsLocation) {
            Log::error('OutscraperService: No results_location in async response', [
                'place_id' => $placeId,
                'data' => $initialData,
            ]);

            return [
                'success' => false,
                'reviews' => [],
                'error' => 'No results location provided for async request',
            ];
        }

        Log::info('OutscraperService: Polling for results', [
            'place_id' => $placeId,
            'results_location' => $resultsLocation,
        ]);

        for ($attempt = 1; $attempt <= $this->maxPollingAttempts; $attempt++) {
            // Wait before polling (except first attempt)
            if ($attempt > 1) {
                sleep($this->pollingDelaySeconds);
            }

            try {
                $response = Http::withHeaders([
                    'X-API-KEY' => $this->apiKey,
                ])
                ->timeout(60)
                ->get($resultsLocation);

                if (!$response->successful()) {
                    Log::warning('OutscraperService: Polling request failed', [
                        'place_id' => $placeId,
                        'attempt' => $attempt,
                        'status' => $response->status(),
                    ]);
                    continue;
                }

                $data = $response->json();

                Log::info('OutscraperService: Polling response', [
                    'place_id' => $placeId,
                    'attempt' => $attempt,
                    'status' => $data['status'] ?? 'unknown',
                ]);

                // Check if still pending
                $status = $data['status'] ?? null;

                if ($status === 'Pending' || $status === 'pending') {
                    Log::info('OutscraperService: Still pending, continuing to poll', [
                        'place_id' => $placeId,
                        'attempt' => $attempt,
                        'max_attempts' => $this->maxPollingAttempts,
                    ]);
                    continue;
                }

                // Check for error status
                if ($status === 'Error' || $status === 'error') {
                    return [
                        'success' => false,
                        'reviews' => [],
                        'error' => $data['error_message'] ?? 'Outscraper request failed',
                    ];
                }

                // Data should be ready - process it
                if ($status === 'Success' || $status === 'success' || isset($data['data'])) {
                    return $this->processReviewsData($placeId, $data);
                }

            } catch (Exception $e) {
                Log::warning('OutscraperService: Polling exception', [
                    'place_id' => $placeId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Max attempts reached
        Log::error('OutscraperService: Polling timeout', [
            'place_id' => $placeId,
            'max_attempts' => $this->maxPollingAttempts,
        ]);

        return [
            'success' => false,
            'reviews' => [],
            'error' => 'Timeout waiting for reviews data',
        ];
    }

    /**
     * Process the reviews data from Outscraper response.
     */
    protected function processReviewsData(string $placeId, array $data): array
    {
        // Handle different response structures
        $places = $data['data'] ?? $data;

        // If data is wrapped in another array
        if (isset($places[0]) && is_array($places[0])) {
            $placeData = $places[0];
        } elseif (isset($places['data']) && is_array($places['data'])) {
            $placeData = $places['data'][0] ?? $places['data'];
        } else {
            $placeData = $places;
        }

        // If placeData is an array of places, get the first one
        if (isset($placeData[0]) && is_array($placeData[0])) {
            $placeData = $placeData[0];
        }

        if (empty($placeData) || (!isset($placeData['reviews_data']) && !isset($placeData['name']))) {
            Log::warning('OutscraperService: No valid place data found', [
                'place_id' => $placeId,
                'data_keys' => is_array($placeData) ? array_keys($placeData) : 'not_array',
            ]);

            return [
                'success' => false,
                'reviews' => [],
                'error' => 'No place data found in response',
            ];
        }

        $reviews = $placeData['reviews_data'] ?? [];

        Log::info('OutscraperService: Reviews processed successfully', [
            'place_id' => $placeId,
            'reviews_count' => count($reviews),
            'place_name' => $placeData['name'] ?? 'unknown',
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

        // If no API key configured, return mock data (allows testing without subscription)
        if (!$this->apiKey) {
            Log::info('OutscraperService: Using mock places (no API key configured)');
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
