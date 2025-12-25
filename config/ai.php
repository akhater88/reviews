<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default AI provider that will be used for
    | sentiment analysis, reply generation, and other AI features.
    |
    | Supported: "openai", "anthropic"
    |
    */
    'default_provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Anthropic (Claude) Configuration
    |--------------------------------------------------------------------------
    */
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-3-haiku-20240307'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reply Generation Settings
    |--------------------------------------------------------------------------
    */
    'reply' => [
        // Available tones for reply generation
        'tones' => [
            'professional' => 'مهني',
            'friendly' => 'ودي',
            'apologetic' => 'اعتذاري',
            'grateful' => 'شكر وتقدير',
            'neutral' => 'محايد',
        ],

        // Default tone
        'default_tone' => env('AI_DEFAULT_TONE', 'professional'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sentiment Analysis Categories
    |--------------------------------------------------------------------------
    */
    'categories' => [
        'food' => 'الطعام',
        'service' => 'الخدمة',
        'price' => 'السعر',
        'ambiance' => 'الأجواء',
        'cleanliness' => 'النظافة',
        'speed' => 'السرعة',
        'staff' => 'الموظفين',
        'quality' => 'الجودة',
    ],
];
