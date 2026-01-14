<?php

return [
    'api_key' => env('INFOBIP_API_KEY'),
    'base_url' => env('INFOBIP_BASE_URL', 'https://api.infobip.com'),
    'sms_endpoint' => env('INFOBIP_SMS_ENDPOINT', '/sms/2/text/advanced'),
    'whatsapp' => [
        'api_key' => env('INFOBIP_WHATSAPP_API_KEY'),
        'sender_number' => env('INFOBIP_WHATSAPP_SENDER_NUMBER', '966557360481'),
        'template_name' => env('INFOBIP_WHATSAPP_TEMPLATE_NAME', 'reviewsotp'),
        'template_lang' => env('INFOBIP_WHATSAPP_TEMPLATE_LANG', 'ar'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Templates for Free Reports
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'magic_link' => env('INFOBIP_TEMPLATE_MAGIC_LINK', 'tabsense_free_report_link'),
        'report_ready' => env('INFOBIP_TEMPLATE_REPORT_READY', 'tabsense_report_ready'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'messages_per_hour' => env('INFOBIP_RATE_LIMIT', 10),
    ],
];
