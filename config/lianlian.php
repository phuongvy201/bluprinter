<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LianLian Pay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for LianLian Pay payment gateway integration
    |
    */

    // Environment settings
    'sandbox' => env('LIANLIAN_SANDBOX', true),

    // Merchant credentials
    'merchant_id' => env('LIANLIAN_MERCHANT_ID', ''),
    'sub_merchant_id' => env('LIANLIAN_SUB_MERCHANT_ID', ''),

    // Keys
    'public_key' => env('LIANLIAN_PUBLIC_KEY', ''),
    'private_key' => env('LIANLIAN_PRIVATE_KEY', ''),

    // URLs
    'sandbox_url' => 'https://test-api.lianlianpay.com',
    'production_url' => 'https://api.lianlianpay.com',

    // Payment settings
    'currency' => 'USD',
    'timeout' => 30, // seconds

    // Log file path
    'log_file' => storage_path('logs/lianlian.log'),

    // Webhook settings
    'webhook_url' => env('LIANLIAN_WEBHOOK_URL', '/payment/lianlian/webhook'),

    // Return URLs
    'return_url' => env('LIANLIAN_RETURN_URL', '/payment/lianlian/return'),
    'cancel_url' => env('LIANLIAN_CANCEL_URL', '/payment/lianlian/cancel'),
];













