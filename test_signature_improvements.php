<?php

require_once 'vendor/autoload.php';

use lianlianpay\v3sdk\utils\LianLianSign;

echo "=== TESTING SIGNATURE IMPROVEMENTS ===\n";

// Test data with proper decimal precision and no NULL values
$testData = [
    'merchant_id' => '202501130004033009',
    'biz_code' => 'EC',
    'country' => 'US',
    'merchant_transaction_id' => 'Order-20251024023354',
    'payment_method' => 'inter_credit_card',
    'redirect_url' => 'http://bluprinter.com/payment/lianlian/return',
    'notification_url' => 'http://bluprinter.com/payment/lianlian/webhook-v2',
    'customer' => [
        'customer_type' => 'I',
        'full_name' => 'Admin User',
        'email' => 'admin@bluprinter.com',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'address' => [
            'city' => 'Test City',
            'country' => 'US',
            'line1' => '123 Test Street',
            'line2' => '',
            'state' => 'Test State',
            'postal_code' => '12345'
        ]
    ],
    'merchant_order' => [
        'merchant_order_id' => 'Order-20251024023354',
        'merchant_order_time' => '20251024023354',
        'order_amount' => '0.37',  // Properly formatted with 2 decimal places
        'order_currency_code' => 'USD',
        'order_description' => 'Order from Bluprinter',
        'products' => [
            [
                'category' => 'general',
                'name' => 'Test Product',
                'price' => '0.20',  // Properly formatted with 2 decimal places
                'product_id' => '1',
                'quantity' => 1,
                'shipping_provider' => 'other',
                'sku' => 'SKU-1',
                'url' => 'http://bluprinter.com/products/1'
            ]
        ],
        'shipping' => [
            'name' => 'Admin User',
            'phone' => '+84774255690',
            'cycle' => '48h',
            'address' => [
                'city' => 'Test City',
                'country' => 'US',
                'line1' => '123 Test Street',
                'line2' => '',
                'state' => 'Test State',
                'postal_code' => '12345'
            ]
        ]
    ],
    'terminal_data' => [
        'user_order_ip' => '127.0.0.1',
        'user_client_mode' => '13',
        'user_client_app_type' => '0'
    ],
    'payment_data' => [
        'card' => [
            'card_token' => 'f9dae96988ef45638f92ba1039186039',
            'holder_name' => 'Admin User'
        ]
    ]
];

$signTool = new LianLianSign();

echo "=== TESTING WITH IMPROVED DATA ===\n";

try {
    // Test signature generation
    $signature = $signTool->sign($testData, getenv('LIANLIAN_PRIVATE_KEY') ?: '');
    echo "Generated Signature: " . substr($signature, 0, 50) . "...\n";
    echo "Signature Length: " . strlen($signature) . "\n";

    // Test signature verification
    $isValid = $signTool->verify($testData, $signature, getenv('LIANLIAN_PUBLIC_KEY') ?: '');
    echo "Signature Verification: " . ($isValid ? 'VALID' : 'INVALID') . "\n";

    if ($isValid) {
        echo "✅ IMPROVEMENTS WORKING - Signature is valid!\n";
    } else {
        echo "❌ Still having issues with signature verification\n";
    }

    // Test with text signature
    $testText = "merchant_id=202501130004033009&biz_code=EC&country=US&order_amount=0.37";
    $textSignature = $signTool->sign_text($testText, getenv('LIANLIAN_PRIVATE_KEY') ?: '');
    $textVerify = $signTool->verify_text($testText, $textSignature, getenv('LIANLIAN_PUBLIC_KEY') ?: '');
    echo "Text Signature Verification: " . ($textVerify ? 'VALID' : 'INVALID') . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING DECIMAL PRECISION ===\n";

// Test different decimal formats
$amounts = [0.37, 0.370, 0.3700, '0.37', '0.370'];
foreach ($amounts as $amount) {
    $formatted = number_format($amount, 2, '.', '');
    echo "Amount: $amount -> Formatted: $formatted\n";
}

echo "\n=== TESTING NULL VS EMPTY STRING ===\n";

$nullTest = [
    'merchant_id' => '202501130004033009',
    'biz_code' => 'EC',
    'country' => 'US',
    'empty_field' => '',  // Empty string (will be signed)
    'null_field' => null   // NULL (will not be signed)
];

echo "Testing NULL vs empty string handling...\n";
try {
    $nullSignature = $signTool->sign($nullTest, getenv('LIANLIAN_PRIVATE_KEY') ?: '');
    echo "NULL test signature: " . substr($nullSignature, 0, 50) . "...\n";
} catch (Exception $e) {
    echo "NULL test error: " . $e->getMessage() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
