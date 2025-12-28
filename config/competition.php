<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Competition Scoring Weights
    |--------------------------------------------------------------------------
    | Total must equal 100
    */
    'scoring' => [
        'weights' => [
            'rating' => 25,        // Google rating (1-5 stars)
            'sentiment' => 30,     // AI sentiment analysis
            'response_rate' => 15, // Owner response rate
            'volume' => 10,        // Review volume/activity
            'trend' => 10,         // Improvement trend
            'keywords' => 10,      // Positive keyword frequency
        ],

        // Minimum requirements
        'min_reviews' => 10,
        'min_rating' => 1.0,

        // Normalization ranges
        'volume_max' => 500,      // Max reviews for 100% volume score
        'trend_period_days' => 30, // Days to calculate trend
    ],

    /*
    |--------------------------------------------------------------------------
    | Analysis Settings
    |--------------------------------------------------------------------------
    */
    'analysis' => [
        'provider' => env('COMPETITION_AI_PROVIDER', 'anthropic'), // openai or anthropic
        'model' => env('COMPETITION_AI_MODEL', 'claude-sonnet-4-20250514'),
        'max_reviews_per_batch' => 50,
        'batch_delay_seconds' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduling
    |--------------------------------------------------------------------------
    */
    'schedule' => [
        'full_recalculation' => 'daily',    // Full score recalculation
        'ranking_update' => 'hourly',        // Ranking position update
        'review_sync' => 'every_six_hours',  // Sync reviews from Google
    ],

    /*
    |--------------------------------------------------------------------------
    | Outscraper Integration
    |--------------------------------------------------------------------------
    */
    'outscraper' => [
        'api_key' => env('OUTSCRAPER_API_KEY'),
        'reviews_limit' => 100,
        'language' => 'ar',
    ],
];
