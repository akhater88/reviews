<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google OAuth Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Business Profile API integration
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
        'places_api_key' => env('GOOGLE_PLACES_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Outscraper Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Outscraper API (fallback for manual branches)
    |
    */

    'outscraper' => [
        'api_key' => env('OUTSCRAPER_API_KEY'),
        'base_url' => env('OUTSCRAPER_BASE_URL', 'https://api.outscraper.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Place Search Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which service to use for searching Google Places.
    | Options: 'google' (Google Places API) or 'outscraper' (Outscraper API)
    |
    | - google: Uses Google Places API directly (free tier available)
    | - outscraper: Uses Outscraper API (paid per request)
    |
    */

    'place_search' => [
        'provider' => env('PLACE_SEARCH_PROVIDER', 'outscraper'),
        'fallback_enabled' => env('PLACE_SEARCH_FALLBACK', false),
    ],

];
