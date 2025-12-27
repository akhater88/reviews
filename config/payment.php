<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'stripe'),

    'gateways' => [
        'stripe' => [
            'publishable_key' => env('STRIPE_KEY'),
            'secret_key' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],

        'moyasar' => [
            'publishable_key' => env('MOYASAR_KEY'),
            'secret_key' => env('MOYASAR_SECRET'),
        ],

        'manual' => [
            'bank_name' => env('BANK_NAME', 'البنك الأهلي السعودي'),
            'account_name' => env('BANK_ACCOUNT_NAME', 'TABsense LLC'),
            'account_number' => env('BANK_ACCOUNT_NUMBER'),
            'iban' => env('BANK_IBAN'),
            'swift_code' => env('BANK_SWIFT'),
            'instructions_ar' => env('BANK_INSTRUCTIONS_AR'),
        ],
    ],

    'currencies' => [
        'SAR' => ['symbol' => 'ر.س', 'name_ar' => 'ريال سعودي'],
        'USD' => ['symbol' => '$', 'name_ar' => 'دولار أمريكي'],
    ],
];
