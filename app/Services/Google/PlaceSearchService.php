<?php

namespace App\Services\Google;

use Exception;
use Illuminate\Support\Facades\Log;

class PlaceSearchService
{
    private string $provider;
    private ?GooglePlacesService $googlePlacesService = null;
    private ?OutscraperService $outscraperService = null;

    public function __construct()
    {
        $this->provider = config('services-google.place_search.provider', 'outscraper');
    }

    /**
     * Get the current provider name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * Search for places using configured provider
     */
    public function searchPlace(string $query, ?string $location = null): array
    {
        try {
            if ($this->provider === 'google') {
                return $this->getGooglePlacesService()->searchPlace($query, $location);
            }

            return $this->getOutscraperService()->searchPlace($query, $location);
        } catch (Exception $e) {
            Log::error('Place search failed', [
                'provider' => $this->provider,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            // If primary provider fails, try fallback
            return $this->searchWithFallback($query, $location, $e);
        }
    }

    /**
     * Get place details by Place ID
     */
    public function getPlaceDetails(string $placeId): ?array
    {
        try {
            if ($this->provider === 'google') {
                return $this->getGooglePlacesService()->getPlaceDetails($placeId);
            }

            return $this->getOutscraperService()->getPlaceDetails($placeId);
        } catch (Exception $e) {
            Log::error('Place details fetch failed', [
                'provider' => $this->provider,
                'place_id' => $placeId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Try fallback provider if primary fails
     */
    private function searchWithFallback(string $query, ?string $location, Exception $originalException): array
    {
        $fallbackEnabled = config('services-google.place_search.fallback_enabled', false);

        if (!$fallbackEnabled) {
            throw $originalException;
        }

        try {
            // Switch to the other provider
            if ($this->provider === 'google') {
                Log::info('Falling back to Outscraper for place search');
                return $this->getOutscraperService()->searchPlace($query, $location);
            } else {
                Log::info('Falling back to Google Places for place search');
                return $this->getGooglePlacesService()->searchPlace($query, $location);
            }
        } catch (Exception $e) {
            Log::error('Fallback provider also failed', [
                'error' => $e->getMessage(),
            ]);

            // Both providers failed, throw the original exception
            throw $originalException;
        }
    }

    /**
     * Get GooglePlacesService instance
     */
    private function getGooglePlacesService(): GooglePlacesService
    {
        if (!$this->googlePlacesService) {
            $this->googlePlacesService = app(GooglePlacesService::class);
        }

        return $this->googlePlacesService;
    }

    /**
     * Get OutscraperService instance
     */
    private function getOutscraperService(): OutscraperService
    {
        if (!$this->outscraperService) {
            $this->outscraperService = app(OutscraperService::class);
        }

        return $this->outscraperService;
    }
}
