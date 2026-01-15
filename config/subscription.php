<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trial Period
    |--------------------------------------------------------------------------
    */
    'trial_days' => (int) env('TRIAL_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Grace Period
    |--------------------------------------------------------------------------
    | Days after expiry before features are disabled
    */
    'grace_period_days' => (int) env('GRACE_PERIOD_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    */
    'default_currency' => env('DEFAULT_CURRENCY', 'SAR'),

    'currencies' => [
        'SAR' => [
            'code' => 'SAR',
            'symbol' => 'ر.س',
            'name' => 'Saudi Riyal',
            'name_ar' => 'ريال سعودي',
            'decimal_places' => 2,
        ],
        'USD' => [
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
            'name_ar' => 'دولار أمريكي',
            'decimal_places' => 2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway
    |--------------------------------------------------------------------------
    */
    'payment_gateway' => env('PAYMENT_GATEWAY', 'manual'),

    'gateways' => [
        'stripe' => [
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
        'moyasar' => [
            'api_key' => env('MOYASAR_API_KEY'),
            'publishable_key' => env('MOYASAR_PUBLISHABLE_KEY'),
            'webhook_secret' => env('MOYASAR_WEBHOOK_SECRET'),
        ],
        'manual' => [
            // No config needed
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    */
    'invoice' => [
        'prefix' => 'INV-',
        'tax_rate' => 15, // VAT percentage
        'company_name' => 'سُمعة',
        'company_address' => 'Riyadh, Saudi Arabia',
        'company_tax_number' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Saudi Arabia IP Detection
    |--------------------------------------------------------------------------
    | Countries that should see SAR currency
    */
    'sar_countries' => ['SA', 'AE', 'BH', 'KW', 'OM', 'QA'],

    /*
    |--------------------------------------------------------------------------
    | Usage Limits Reset
    |--------------------------------------------------------------------------
    */
    'usage_reset_day' => 1, // Day of month to reset usage counters

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'expiry_reminder_days' => [7, 3, 1], // Days before expiry to send reminders
        'usage_warning_percentage' => 80, // Warn when usage reaches this %
    ],
];
