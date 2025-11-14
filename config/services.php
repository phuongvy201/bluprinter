<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'meta' => [
        'pixel_id' => env('META_PIXEL_ID', '663127653502118'),
    ],

    'google' => [
        'tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID', 'GTM-5T5M2NG4'),
        'ads_id' => env('GOOGLE_ADS_ID', 'AW-17718009492'),
        'analytics' => [
            'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),
            'credentials_path' => env('GOOGLE_ANALYTICS_CREDENTIALS_PATH', storage_path('app/google-analytics-credentials.json')),
        ],
    ],

    'tiktok' => [
        'pixel_id' => env('TIKTOK_PIXEL_ID'),
        'test_event_code' => env('TIKTOK_TEST_EVENT_CODE'),
        'access_token' => env('TIKTOK_ACCESS_TOKEN'),
        'endpoint' => env('TIKTOK_EVENTS_ENDPOINT', 'https://business-api.tiktok.com/open_api/v1.3/event/track/'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

];
