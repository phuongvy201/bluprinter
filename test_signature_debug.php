<?php

require_once 'vendor/autoload.php';

use lianlianpay\v3sdk\utils\LianLianSign;

echo "=== SIGNATURE DEBUG TEST ===\n";

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$merchantId = $_ENV['LIANLIAN_MERCHANT_ID'] ?? '';
$privateKey = $_ENV['LIANLIAN_PRIVATE_KEY'] ?? '';
$publicKey = $_ENV['LIANLIAN_PUBLIC_KEY'] ?? '';
$sandbox = $_ENV['LIANLIAN_SANDBOX'] ?? 'true';

echo "Configuration:\n";
echo "- Merchant ID: $merchantId\n";
echo "- Sandbox: $sandbox\n";
echo "- Private Key Length: " . strlen($privateKey) . "\n";
echo "- Public Key Length: " . strlen($publicKey) . "\n\n";

// Test 1: Check key format
echo "=== KEY FORMAT CHECK ===\n";
$privateHasHeaders = strpos($privateKey, '-----BEGIN') !== false;
$publicHasHeaders = strpos($publicKey, '-----BEGIN') !== false;

echo "Private key has headers: " . ($privateHasHeaders ? 'YES' : 'NO') . "\n";
echo "Public key has headers: " . ($publicHasHeaders ? 'YES' : 'NO') . "\n";

if (!$privateHasHeaders) {
    echo "⚠️  Private key missing PEM headers - SDK will add them\n";
}
if (!$publicHasHeaders) {
    echo "⚠️  Public key missing PEM headers - SDK will add them\n";
}

// Test 2: Check key validity
echo "\n=== KEY VALIDITY CHECK ===\n";
try {
    $privateKeyResource = openssl_get_privatekey($privateKey);
    if ($privateKeyResource === false) {
        echo "❌ Private key is invalid\n";
        $errors = [];
        while (($error = openssl_error_string()) !== false) {
            $errors[] = $error;
        }
        echo "OpenSSL Errors: " . implode(', ', $errors) . "\n";
    } else {
        echo "✅ Private key is valid\n";
        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($privateKeyResource);
        }
    }
} catch (Exception $e) {
    echo "❌ Private key error: " . $e->getMessage() . "\n";
}

try {
    $publicKeyResource = openssl_pkey_get_public($publicKey);
    if ($publicKeyResource === false) {
        echo "❌ Public key is invalid\n";
        $errors = [];
        while (($error = openssl_error_string()) !== false) {
            $errors[] = $error;
        }
        echo "OpenSSL Errors: " . implode(', ', $errors) . "\n";
    } else {
        echo "✅ Public key is valid\n";
    }
} catch (Exception $e) {
    echo "❌ Public key error: " . $e->getMessage() . "\n";
}

// Test 3: Test signature generation
echo "\n=== SIGNATURE GENERATION TEST ===\n";
$testData = [
    'merchant_id' => $merchantId,
    'biz_code' => 'EC',
    'country' => 'US'
];

$signTool = new LianLianSign();

try {
    $signature = $signTool->sign($testData, $privateKey);
    echo "Signature generated: " . (strlen($signature) > 0 ? 'YES' : 'NO') . "\n";
    echo "Signature length: " . strlen($signature) . "\n";
    echo "Signature preview: " . substr($signature, 0, 50) . "...\n";

    // Test verification
    $isValid = $signTool->verify($testData, $signature, $publicKey);
    echo "Signature verification: " . ($isValid ? 'VALID' : 'INVALID') . "\n";

    if (!$isValid) {
        echo "❌ Signature verification failed!\n";
        echo "This indicates a key mismatch or format issue\n";
    }
} catch (Exception $e) {
    echo "❌ Signature generation failed: " . $e->getMessage() . "\n";
}

// Test 4: Test with different data formats
echo "\n=== DATA FORMAT TEST ===\n";
$testCases = [
    'simple' => [
        'merchant_id' => $merchantId,
        'biz_code' => 'EC',
        'country' => 'US'
    ],
    'with_amount' => [
        'merchant_id' => $merchantId,
        'biz_code' => 'EC',
        'country' => 'US',
        'order_amount' => '0.37'
    ],
    'with_transaction' => [
        'merchant_id' => $merchantId,
        'biz_code' => 'EC',
        'country' => 'US',
        'merchant_transaction_id' => 'Order-20251024023354',
        'order_amount' => '0.37'
    ]
];

foreach ($testCases as $name => $data) {
    try {
        $signature = $signTool->sign($data, $privateKey);
        $isValid = $signTool->verify($data, $signature, $publicKey);
        echo "$name: " . ($isValid ? 'VALID' : 'INVALID') . "\n";
    } catch (Exception $e) {
        echo "$name: ERROR - " . $e->getMessage() . "\n";
    }
}

// Test 5: Environment comparison
echo "\n=== ENVIRONMENT COMPARISON ===\n";
if ($sandbox === 'true') {
    echo "🔍 SANDBOX MODE DETECTED\n";
    echo "This should work with sandbox keys\n";
    echo "If this fails, check:\n";
    echo "1. Keys are correct for sandbox\n";
    echo "2. Merchant ID matches sandbox\n";
    echo "3. API endpoints are sandbox\n";
} else {
    echo "🔍 PRODUCTION MODE DETECTED\n";
    echo "This should work with production keys\n";
    echo "If this fails, check:\n";
    echo "1. Keys are correct for production\n";
    echo "2. Merchant ID matches production\n";
    echo "3. API endpoints are production\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
