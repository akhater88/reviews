<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Outscraper API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Outscraper API used to fetch Google Reviews.
    | Get your API key from: https://outscraper.com/
    |
    */

    'api_key' => env('OUTSCRAPER_API_KEY'),

    'base_url' => env('OUTSCRAPER_BASE_URL', 'https://api.app.outscraper.com'),

    /*
    |--------------------------------------------------------------------------
    | Default Request Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for review fetching requests.
    |
    */

    'defaults' => [
        // Number of reviews to fetch per request
        'reviews_limit' => env('OUTSCRAPER_REVIEWS_LIMIT', 100),

        // Language for reviews (empty = all languages)
        'language' => env('OUTSCRAPER_LANGUAGE', ''),

        // Sort order: 'newest' or 'most_relevant'
        'sort' => env('OUTSCRAPER_SORT', 'newest'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting settings for API requests.
    |
    */

    'rate_limit' => [
        // Maximum requests per minute
        'requests_per_minute' => env('OUTSCRAPER_RATE_LIMIT', 60),

        // Delay between requests in milliseconds
        'delay_ms' => env('OUTSCRAPER_DELAY_MS', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | HTTP request timeout settings.
    |
    */

    'timeout' => [
        // Connection timeout in seconds
        'connect' => env('OUTSCRAPER_CONNECT_TIMEOUT', 30),

        // Request timeout in seconds
        'request' => env('OUTSCRAPER_REQUEST_TIMEOUT', 120),
    ],
];
