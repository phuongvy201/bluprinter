<?php

require_once 'vendor/autoload.php';

use lianlianpay\v3sdk\utils\LianLianSign;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get keys from environment
$privateKey = $_ENV['LIANLIAN_PRIVATE_KEY'] ?? '';
$publicKey = $_ENV['LIANLIAN_PUBLIC_KEY'] ?? '';
$merchantId = $_ENV['LIANLIAN_MERCHANT_ID'] ?? '';

echo "=== LIANLIAN PAY SIGNATURE DEBUG ===\n";
echo "Merchant ID: " . $merchantId . "\n";
echo "Private Key Length: " . strlen($privateKey) . "\n";
echo "Public Key Length: " . strlen($publicKey) . "\n\n";

// Test data similar to the request
$testData = [
    'merchant_id' => $merchantId,
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
            'city' => '1/34 Đại Lộ Độc Lập Thành Phố Dĩ An',
            'country' => 'US',
            'line1' => '1/34 Đại Lộ Độc Lập\ndfwedrfw',
            'line2' => '',
            'state' => 'Thành phố Hồ Chí Minh',
            'postal_code' => '75355'
        ]
    ],
    'merchant_order' => [
        'merchant_order_id' => 'Order-20251024023354',
        'merchant_order_time' => '20251024023354',
        'order_amount' => '0.37',
        'order_currency_code' => 'USD',
        'order_description' => 'Order from Bluprinter',
        'products' => [
            [
                'category' => 'general',
                'name' => 'Test Product',
                'price' => '0.20',
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
                'city' => '1/34 Đại Lộ Độc Lập Thành Phố Dĩ An',
                'country' => 'US',
                'line1' => '1/34 Đại Lộ Độc Lập\ndfwedrfw',
                'line2' => '',
                'state' => 'Thành phố Hồ Chí Minh',
                'postal_code' => '75355'
            ]
        ]
    ],
    'payment_data' => [
        'card' => [
            'card_token' => 'f9dae96988ef45638f92ba1039186039',
            'holder_name' => 'Admin User'
        ]
    ]
];

// Initialize signature tool
$signTool = new LianLianSign();

echo "=== TESTING SIGNATURE GENERATION ===\n";

try {
    // Test 1: Generate signature content
    $signContent = $signTool->gen_sign_content($testData);
    echo "Signature Content Length: " . strlen($signContent) . "\n";
    echo "Signature Content (first 200 chars): " . substr($signContent, 0, 200) . "...\n\n";

    // Test 2: Generate signature
    $signature = $signTool->sign($testData, $privateKey);
    echo "Generated Signature: " . $signature . "\n";
    echo "Signature Length: " . strlen($signature) . "\n\n";

    // Test 3: Verify signature
    $isValid = $signTool->verify($testData, $signature, $publicKey);
    echo "Signature Verification: " . ($isValid ? 'VALID' : 'INVALID') . "\n\n";

    // Test 4: Check key format
    echo "=== KEY FORMAT CHECK ===\n";

    // Check private key format
    if (strpos($privateKey, '-----BEGIN') === false) {
        echo "WARNING: Private key doesn't have PEM headers\n";
        echo "Adding PEM headers...\n";
        $privateKeyWithHeaders = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($privateKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
    } else {
        $privateKeyWithHeaders = $privateKey;
        echo "Private key has proper PEM format\n";
    }

    // Check public key format
    if (strpos($publicKey, '-----BEGIN') === false) {
        echo "WARNING: Public key doesn't have PEM headers\n";
        echo "Adding PEM headers...\n";
        $publicKeyWithHeaders = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($publicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
    } else {
        $publicKeyWithHeaders = $publicKey;
        echo "Public key has proper PEM format\n";
    }

    // Test 5: Test with proper key format
    echo "\n=== TESTING WITH PROPER KEY FORMAT ===\n";
    $signature2 = $signTool->sign($testData, $privateKey);
    $isValid2 = $signTool->verify($testData, $signature2, $publicKey);
    echo "Signature with proper keys: " . ($isValid2 ? 'VALID' : 'INVALID') . "\n";

    // Test 6: Manual signature generation
    echo "\n=== MANUAL SIGNATURE TEST ===\n";
    $manualSignContent = $signTool->gen_sign_content($testData);
    $manualSignature = $signTool->sign_text($manualSignContent, $privateKey);
    $manualVerify = $signTool->verify_text($manualSignContent, $manualSignature, $publicKey);
    echo "Manual signature verification: " . ($manualVerify ? 'VALID' : 'INVALID') . "\n";

    // Test 7: Check OpenSSL functions
    echo "\n=== OPENSSL CHECK ===\n";
    $key = openssl_get_privatekey($privateKeyWithHeaders);
    if ($key === false) {
        echo "ERROR: Cannot load private key\n";
        echo "OpenSSL Error: " . openssl_error_string() . "\n";
    } else {
        echo "Private key loaded successfully\n";
        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($key);
        }
    }

    $pubKey = openssl_pkey_get_public($publicKeyWithHeaders);
    if ($pubKey === false) {
        echo "ERROR: Cannot load public key\n";
        echo "OpenSSL Error: " . openssl_error_string() . "\n";
    } else {
        echo "Public key loaded successfully\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
