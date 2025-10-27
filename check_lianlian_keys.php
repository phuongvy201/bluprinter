<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get keys from environment
$privateKey = $_ENV['LIANLIAN_PRIVATE_KEY'] ?? '';
$publicKey = $_ENV['LIANLIAN_PUBLIC_KEY'] ?? '';
$merchantId = $_ENV['LIANLIAN_MERCHANT_ID'] ?? '';

echo "=== LIANLIAN PAY KEY ANALYSIS ===\n";
echo "Merchant ID: " . $merchantId . "\n\n";

// Analyze private key
echo "=== PRIVATE KEY ANALYSIS ===\n";
echo "Length: " . strlen($privateKey) . "\n";
echo "First 50 chars: " . substr($privateKey, 0, 50) . "...\n";
echo "Last 50 chars: " . substr($privateKey, -50) . "\n";

// Check if it's base64 encoded
$isBase64 = base64_encode(base64_decode($privateKey, true)) === $privateKey;
echo "Is Base64: " . ($isBase64 ? 'YES' : 'NO') . "\n";

// Try to decode and check format
$decodedPrivate = base64_decode($privateKey, true);
if ($decodedPrivate !== false) {
    echo "Base64 decoded length: " . strlen($decodedPrivate) . "\n";
    echo "Decoded first 50 chars: " . substr($decodedPrivate, 0, 50) . "...\n";
}

// Add PEM headers and test
$privateKeyWithHeaders = "-----BEGIN RSA PRIVATE KEY-----\n" .
    wordwrap($privateKey, 64, "\n", true) .
    "\n-----END RSA PRIVATE KEY-----";

echo "\nPrivate key with headers (first 100 chars):\n";
echo substr($privateKeyWithHeaders, 0, 100) . "...\n";

// Test private key loading
$privateKeyResource = openssl_get_privatekey($privateKeyWithHeaders);
if ($privateKeyResource === false) {
    echo "ERROR: Cannot load private key\n";
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    echo "OpenSSL Errors: " . implode(', ', $errors) . "\n";
} else {
    echo "SUCCESS: Private key loaded successfully\n";

    // Get key details
    $keyDetails = openssl_pkey_get_details($privateKeyResource);
    if ($keyDetails) {
        echo "Key type: " . $keyDetails['type'] . "\n";
        echo "Key size: " . $keyDetails['bits'] . " bits\n";
    }

    if (PHP_VERSION_ID < 80000) {
        openssl_free_key($privateKeyResource);
    }
}

echo "\n=== PUBLIC KEY ANALYSIS ===\n";
echo "Length: " . strlen($publicKey) . "\n";
echo "First 50 chars: " . substr($publicKey, 0, 50) . "...\n";
echo "Last 50 chars: " . substr($publicKey, -50) . "\n";

// Check if it's base64 encoded
$isBase64 = base64_encode(base64_decode($publicKey, true)) === $publicKey;
echo "Is Base64: " . ($isBase64 ? 'YES' : 'NO') . "\n";

// Try to decode and check format
$decodedPublic = base64_decode($publicKey, true);
if ($decodedPublic !== false) {
    echo "Base64 decoded length: " . strlen($decodedPublic) . "\n";
    echo "Decoded first 50 chars: " . substr($decodedPublic, 0, 50) . "...\n";
}

// Add PEM headers and test
$publicKeyWithHeaders = "-----BEGIN PUBLIC KEY-----\n" .
    wordwrap($publicKey, 64, "\n", true) .
    "\n-----END PUBLIC KEY-----";

echo "\nPublic key with headers (first 100 chars):\n";
echo substr($publicKeyWithHeaders, 0, 100) . "...\n";

// Test public key loading
$publicKeyResource = openssl_pkey_get_public($publicKeyWithHeaders);
if ($publicKeyResource === false) {
    echo "ERROR: Cannot load public key\n";
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    echo "OpenSSL Errors: " . implode(', ', $errors) . "\n";
} else {
    echo "SUCCESS: Public key loaded successfully\n";

    // Get key details
    $keyDetails = openssl_pkey_get_details($publicKeyResource);
    if ($keyDetails) {
        echo "Key type: " . $keyDetails['type'] . "\n";
        echo "Key size: " . $keyDetails['bits'] . " bits\n";
    }
}

echo "\n=== SIGNATURE TEST ===\n";

// Test with simple data
$testData = "merchant_id=" . $merchantId . "&biz_code=EC&country=US";
echo "Test data: " . $testData . "\n";

if ($privateKeyResource !== false && $publicKeyResource !== false) {
    // Test signature generation
    $signatureResult = openssl_sign($testData, $signature, $privateKeyResource, OPENSSL_ALGO_SHA1);
    if ($signatureResult) {
        $base64Signature = base64_encode($signature);
        echo "Generated signature: " . $base64Signature . "\n";

        // Test verification
        $verifyResult = openssl_verify($testData, $signature, $publicKeyResource, OPENSSL_ALGO_SHA1);
        echo "Verification result: " . $verifyResult . " (1=valid, 0=invalid, -1=error)\n";

        if ($verifyResult === 1) {
            echo "SUCCESS: Signature generation and verification working!\n";
        } else {
            echo "ERROR: Signature verification failed\n";
        }
    } else {
        echo "ERROR: Failed to generate signature\n";
    }
} else {
    echo "ERROR: Cannot test signature - keys not loaded properly\n";
}

echo "\n=== ENVIRONMENT CHECK ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";
echo "OpenSSL Version Number: " . OPENSSL_VERSION_NUMBER . "\n";

// Check if OpenSSL is properly configured
$config = openssl_get_config();
echo "OpenSSL Config: " . (isset($config['openssl']) ? 'Available' : 'Not available') . "\n";

echo "\n=== DEBUG COMPLETE ===\n";
