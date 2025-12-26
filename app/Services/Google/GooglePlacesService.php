<?php

namespace App\Services\Google;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    private string $apiKey;
    private string $baseUrl = 'https://maps.googleapis.com/maps/api/place';

    public function __construct()
    {
        $this->apiKey = config('services-google.google.places_api_key');
    }

    /**
     * Search for places using Google Places Text Search API
     */
    public function searchPlace(string $query, string $location = null): array
    {
        $searchQuery = $location ? "{$query}, {$location}" : $query;

        $response = Http::timeout(30)
            ->get($this->baseUrl . '/textsearch/json', [
                'query' => $searchQuery,
                'key' => $this->apiKey,
                'language' => 'ar',
                'region' => 'sa',
                'type' => 'restaurant|food|cafe',
            ]);

        if (!$response->successful()) {
            Log::error('Google Places text search failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في البحث عن الموقع');
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'OK' && ($data['status'] ?? '') !== 'ZERO_RESULTS') {
            Log::error('Google Places API error', [
                'status' => $data['status'] ?? 'unknown',
                'error_message' => $data['error_message'] ?? null,
            ]);
            throw new Exception($data['error_message'] ?? 'خطأ في Google Places API');
        }

        return $this->transformSearchResults($data['results'] ?? []);
    }

    /**
     * Get place details by Place ID
     */
    public function getPlaceDetails(string $placeId): ?array
    {
        $response = Http::timeout(30)
            ->get($this->baseUrl . '/details/json', [
                'place_id' => $placeId,
                'key' => $this->apiKey,
                'language' => 'ar',
                'fields' => 'place_id,name,formatted_address,formatted_phone_number,international_phone_number,website,geometry,address_components,rating,user_ratings_total',
            ]);

        if (!$response->successful()) {
            Log::error('Google Places details failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('فشل في جلب تفاصيل الموقع');
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'OK') {
            return null;
        }

        return $this->transformPlaceDetails($data['result'] ?? null);
    }

    /**
     * Transform search results to standard format
     */
    private function transformSearchResults(array $results): array
    {
        $places = [];

        foreach ($results as $result) {
            $places[] = [
                'place_id' => $result['place_id'] ?? null,
                'name' => $result['name'] ?? null,
                'address' => $result['formatted_address'] ?? null,
                'full_address' => $result['formatted_address'] ?? null,
                'city' => $this->extractCity($result['formatted_address'] ?? ''),
                'country' => $this->extractCountry($result['formatted_address'] ?? ''),
                'latitude' => $result['geometry']['location']['lat'] ?? null,
                'longitude' => $result['geometry']['location']['lng'] ?? null,
                'rating' => $result['rating'] ?? null,
                'reviews' => $result['user_ratings_total'] ?? null,
                'phone' => null, // Not available in text search
                'site' => null, // Not available in text search
                'website' => null,
            ];
        }

        return $places;
    }

    /**
     * Transform place details to standard format
     */
    private function transformPlaceDetails(?array $result): ?array
    {
        if (!$result) {
            return null;
        }

        $addressComponents = $result['address_components'] ?? [];

        return [
            'place_id' => $result['place_id'] ?? null,
            'name' => $result['name'] ?? null,
            'address' => $result['formatted_address'] ?? null,
            'full_address' => $result['formatted_address'] ?? null,
            'city' => $this->extractComponentByType($addressComponents, 'locality'),
            'country' => $this->extractComponentByType($addressComponents, 'country'),
            'latitude' => $result['geometry']['location']['lat'] ?? null,
            'longitude' => $result['geometry']['location']['lng'] ?? null,
            'phone' => $result['formatted_phone_number'] ?? $result['international_phone_number'] ?? null,
            'website' => $result['website'] ?? null,
            'site' => $result['website'] ?? null,
            'rating' => $result['rating'] ?? null,
            'reviews' => $result['user_ratings_total'] ?? null,
        ];
    }

    /**
     * Extract city from formatted address
     */
    private function extractCity(string $address): ?string
    {
        // Common Saudi cities
        $cities = ['الرياض', 'جدة', 'مكة', 'المدينة', 'الدمام', 'الخبر', 'الطائف', 'تبوك', 'بريدة', 'خميس مشيط', 'Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar'];

        foreach ($cities as $city) {
            if (str_contains($address, $city)) {
                return $city;
            }
        }

        // Try to extract from comma-separated parts
        $parts = explode(',', $address);
        if (count($parts) >= 2) {
            return trim($parts[count($parts) - 2]);
        }

        return null;
    }

    /**
     * Extract country from formatted address
     */
    private function extractCountry(string $address): string
    {
        if (str_contains($address, 'Saudi Arabia') || str_contains($address, 'السعودية')) {
            return 'Saudi Arabia';
        }

        // Try to get last part as country
        $parts = explode(',', $address);
        if (count($parts) >= 1) {
            return trim(end($parts));
        }

        return 'Saudi Arabia';
    }

    /**
     * Extract address component by type
     */
    private function extractComponentByType(array $components, string $type): ?string
    {
        foreach ($components as $component) {
            if (in_array($type, $component['types'] ?? [])) {
                return $component['long_name'] ?? null;
            }
        }

        return null;
    }
}
