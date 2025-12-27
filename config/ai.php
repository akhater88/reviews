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

    /*
    |--------------------------------------------------------------------------
    | Analysis Pipeline Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the 9-step review analysis pipeline that processes
    | restaurant reviews and generates actionable insights.
    |
    */
    'analysis' => [
        'timeout' => env('AI_ANALYSIS_TIMEOUT', 180),
        'max_tokens' => env('AI_ANALYSIS_MAX_TOKENS', 4000),
        'temperature' => env('AI_ANALYSIS_TEMPERATURE', 0.3),
        'queue' => env('AI_ANALYSIS_QUEUE', 'analysis'),
        'connection' => env('AI_ANALYSIS_CONNECTION', 'redis'),
    ],
];
