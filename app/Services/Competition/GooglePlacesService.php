<?php

namespace App\Services\Competition;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://maps.googleapis.com/maps/api/place';

    // Cache settings
    protected const CACHE_TTL_SEARCH = 300; // 5 minutes
    protected const CACHE_TTL_DETAILS = 3600; // 1 hour

    // Search settings
    protected const DEFAULT_COUNTRY = 'sa'; // Saudi Arabia
    protected const DEFAULT_LANGUAGE = 'ar';
    protected const RESTAURANT_TYPES = ['restaurant', 'cafe', 'bakery', 'meal_takeaway', 'meal_delivery'];

    public function __construct()
    {
        $this->apiKey = config('services.google.places_api_key');

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Google Places API key not configured');
        }
    }

    /**
     * Search for restaurants by text query
     */
    public function searchRestaurants(string $query, ?string $location = null): array
    {
        $cacheKey = $this->getSearchCacheKey($query, $location);

        return Cache::remember($cacheKey, self::CACHE_TTL_SEARCH, function () use ($query, $location) {
            return $this->performSearch($query, $location);
        });
    }

    /**
     * Perform the actual search API call
     */
    protected function performSearch(string $query, ?string $location = null): array
    {
        try {
            $params = [
                'query' => $query . ' restaurant',
                'key' => $this->apiKey,
                'language' => self::DEFAULT_LANGUAGE,
                'region' => self::DEFAULT_COUNTRY,
                'type' => 'restaurant',
            ];

            // Add location bias if provided
            if ($location) {
                $params['location'] = $location;
                $params['radius'] = 50000; // 50km radius
            }

            $response = Http::timeout(10)->get("{$this->baseUrl}/textsearch/json", $params);

            if (!$response->successful()) {
                Log::error('Google Places search failed', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return ['success' => false, 'results' => [], 'error' => 'Search request failed'];
            }

            $data = $response->json();

            if ($data['status'] !== 'OK' && $data['status'] !== 'ZERO_RESULTS') {
                Log::error('Google Places API error', [
                    'status' => $data['status'],
                    'error_message' => $data['error_message'] ?? null,
                ]);
                return ['success' => false, 'results' => [], 'error' => $data['status']];
            }

            $results = collect($data['results'] ?? [])
                ->filter(fn ($place) => $this->isValidRestaurant($place))
                ->take(10)
                ->map(fn ($place) => $this->formatSearchResult($place))
                ->values()
                ->toArray();

            return [
                'success' => true,
                'results' => $results,
                'count' => count($results),
            ];

        } catch (\Exception $e) {
            Log::error('Google Places search exception', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);
            return ['success' => false, 'results' => [], 'error' => 'Search failed'];
        }
    }

    /**
     * Get detailed place information
     */
    public function getPlaceDetails(string $placeId): array
    {
        $cacheKey = "google_place_details:{$placeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL_DETAILS, function () use ($placeId) {
            return $this->fetchPlaceDetails($placeId);
        });
    }

    /**
     * Fetch place details from API
     */
    protected function fetchPlaceDetails(string $placeId): array
    {
        try {
            $fields = [
                'place_id',
                'name',
                'formatted_address',
                'formatted_phone_number',
                'international_phone_number',
                'website',
                'url',
                'rating',
                'user_ratings_total',
                'reviews',
                'photos',
                'geometry',
                'types',
                'opening_hours',
                'price_level',
                'address_components',
                'business_status',
            ];

            $response = Http::timeout(10)->get("{$this->baseUrl}/details/json", [
                'place_id' => $placeId,
                'key' => $this->apiKey,
                'language' => self::DEFAULT_LANGUAGE,
                'fields' => implode(',', $fields),
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'place' => null, 'error' => 'Details request failed'];
            }

            $data = $response->json();

            if ($data['status'] !== 'OK') {
                return ['success' => false, 'place' => null, 'error' => $data['status']];
            }

            $place = $this->formatPlaceDetails($data['result']);

            return [
                'success' => true,
                'place' => $place,
            ];

        } catch (\Exception $e) {
            Log::error('Google Places details exception', [
                'error' => $e->getMessage(),
                'place_id' => $placeId,
            ]);
            return ['success' => false, 'place' => null, 'error' => 'Failed to get details'];
        }
    }

    /**
     * Check if place is a valid restaurant
     */
    protected function isValidRestaurant(array $place): bool
    {
        $types = $place['types'] ?? [];

        // Must have at least one restaurant-related type
        $hasRestaurantType = !empty(array_intersect($types, self::RESTAURANT_TYPES));

        // Must be operational
        $isOperational = ($place['business_status'] ?? 'OPERATIONAL') === 'OPERATIONAL';

        // Must have a rating (indicates established business)
        $hasRating = isset($place['rating']);

        return $hasRestaurantType && $isOperational && $hasRating;
    }

    /**
     * Format search result for frontend
     */
    protected function formatSearchResult(array $place): array
    {
        return [
            'place_id' => $place['place_id'],
            'name' => $place['name'],
            'address' => $place['formatted_address'] ?? '',
            'rating' => $place['rating'] ?? null,
            'reviews_count' => $place['user_ratings_total'] ?? 0,
            'photo_url' => $this->getPhotoUrl($place['photos'] ?? []),
            'types' => $place['types'] ?? [],
            'open_now' => $place['opening_hours']['open_now'] ?? null,
            'price_level' => $place['price_level'] ?? null,
        ];
    }

    /**
     * Format detailed place info
     */
    protected function formatPlaceDetails(array $place): array
    {
        return [
            'place_id' => $place['place_id'],
            'name' => $place['name'],
            'name_ar' => $place['name'], // Same for now, could be translated
            'address' => $place['formatted_address'] ?? '',
            'city' => $this->extractCity($place['address_components'] ?? []),
            'country' => $this->extractCountry($place['address_components'] ?? []),
            'phone' => $place['formatted_phone_number'] ?? $place['international_phone_number'] ?? null,
            'website' => $place['website'] ?? null,
            'google_maps_url' => $place['url'] ?? null,
            'rating' => $place['rating'] ?? null,
            'reviews_count' => $place['user_ratings_total'] ?? 0,
            'photo_url' => $this->getPhotoUrl($place['photos'] ?? []),
            'photos' => $this->getPhotoUrls($place['photos'] ?? [], 5),
            'latitude' => $place['geometry']['location']['lat'] ?? null,
            'longitude' => $place['geometry']['location']['lng'] ?? null,
            'types' => $place['types'] ?? [],
            'price_level' => $this->formatPriceLevel($place['price_level'] ?? null),
            'opening_hours' => $place['opening_hours']['weekday_text'] ?? [],
            'is_open' => $place['opening_hours']['open_now'] ?? null,
            'business_status' => $place['business_status'] ?? 'OPERATIONAL',
        ];
    }

    /**
     * Get photo URL from photo reference
     */
    protected function getPhotoUrl(array $photos, int $maxWidth = 400): ?string
    {
        if (empty($photos)) {
            return null;
        }

        $photoReference = $photos[0]['photo_reference'] ?? null;

        if (!$photoReference) {
            return null;
        }

        return "{$this->baseUrl}/photo?maxwidth={$maxWidth}&photo_reference={$photoReference}&key={$this->apiKey}";
    }

    /**
     * Get multiple photo URLs
     */
    protected function getPhotoUrls(array $photos, int $limit = 5): array
    {
        return collect($photos)
            ->take($limit)
            ->map(fn ($photo) => $this->getPhotoUrl([$photo], 800))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Extract city from address components
     */
    protected function extractCity(array $components): ?string
    {
        foreach ($components as $component) {
            if (in_array('locality', $component['types'])) {
                return $component['long_name'];
            }
        }

        foreach ($components as $component) {
            if (in_array('administrative_area_level_2', $component['types'])) {
                return $component['long_name'];
            }
        }

        foreach ($components as $component) {
            if (in_array('administrative_area_level_1', $component['types'])) {
                return $component['long_name'];
            }
        }

        return null;
    }

    /**
     * Extract country from address components
     */
    protected function extractCountry(array $components): string
    {
        foreach ($components as $component) {
            if (in_array('country', $component['types'])) {
                return $component['long_name'];
            }
        }

        return 'Saudi Arabia';
    }

    /**
     * Format price level
     */
    protected function formatPriceLevel(?int $level): ?string
    {
        if ($level === null) {
            return null;
        }

        return str_repeat('$', $level + 1);
    }

    /**
     * Generate cache key for search
     */
    protected function getSearchCacheKey(string $query, ?string $location): string
    {
        $normalizedQuery = strtolower(trim($query));
        $locationHash = $location ? md5($location) : 'default';

        return "google_places_search:{$locationHash}:" . md5($normalizedQuery);
    }

    /**
     * Clear cache for a place
     */
    public function clearPlaceCache(string $placeId): void
    {
        Cache::forget("google_place_details:{$placeId}");
    }
}
