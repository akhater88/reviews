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
    ],

];
