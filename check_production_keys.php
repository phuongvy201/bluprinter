<?php

require_once 'vendor/autoload.php';

echo "=== PRODUCTION KEYS CHECK ===\n";

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$merchantId = $_ENV['LIANLIAN_MERCHANT_ID'] ?? '';
$privateKey = $_ENV['LIANLIAN_PRIVATE_KEY'] ?? '';
$publicKey = $_ENV['LIANLIAN_PUBLIC_KEY'] ?? '';
$sandbox = $_ENV['LIANLIAN_SANDBOX'] ?? 'true';

echo "Current Configuration:\n";
echo "- Merchant ID: $merchantId\n";
echo "- Sandbox: $sandbox\n";
echo "- Private Key Length: " . strlen($privateKey) . "\n";
echo "- Public Key Length: " . strlen($publicKey) . "\n\n";

// Check if this is production environment
if ($sandbox === 'false' && $merchantId === '202501130004033009') {
    echo "🔍 PRODUCTION ENVIRONMENT DETECTED\n";
    echo "This should use production keys\n\n";
} else {
    echo "⚠️  NOT PRODUCTION ENVIRONMENT\n";
    echo "Current: Sandbox=$sandbox, Merchant=$merchantId\n";
    echo "Expected: Sandbox=false, Merchant=202501130004033009\n\n";
}

// Test private key format
echo "=== PRIVATE KEY ANALYSIS ===\n";
echo "Key preview: " . substr($privateKey, 0, 50) . "...\n";
echo "Key ends with: " . substr($privateKey, -20) . "\n";

// Check if it's base64 encoded
$isBase64 = base64_encode(base64_decode($privateKey, true)) === $privateKey;
echo "Is Base64: " . ($isBase64 ? 'YES' : 'NO') . "\n";

// Try to decode
$decoded = base64_decode($privateKey, true);
if ($decoded !== false) {
    echo "Base64 decoded length: " . strlen($decoded) . "\n";
    echo "Decoded preview: " . substr($decoded, 0, 50) . "...\n";
} else {
    echo "❌ Base64 decode failed\n";
}

// Test with PEM headers
echo "\n=== TESTING WITH PEM HEADERS ===\n";
$privateKeyWithHeaders = "-----BEGIN RSA PRIVATE KEY-----\n" .
    wordwrap($privateKey, 64, "\n", true) .
    "\n-----END RSA PRIVATE KEY-----";

echo "Private key with headers (first 100 chars):\n";
echo substr($privateKeyWithHeaders, 0, 100) . "...\n";

// Test OpenSSL loading
$privateKeyResource = openssl_get_privatekey($privateKeyWithHeaders);
if ($privateKeyResource === false) {
    echo "❌ Private key loading failed\n";
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    echo "OpenSSL Errors: " . implode(', ', $errors) . "\n";
} else {
    echo "✅ Private key loaded successfully\n";

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

// Test public key
echo "\n=== PUBLIC KEY ANALYSIS ===\n";
$publicKeyWithHeaders = "-----BEGIN PUBLIC KEY-----\n" .
    wordwrap($publicKey, 64, "\n", true) .
    "\n-----END PUBLIC KEY-----";

$publicKeyResource = openssl_pkey_get_public($publicKeyWithHeaders);
if ($publicKeyResource === false) {
    echo "❌ Public key loading failed\n";
    $errors = [];
    while (($error = openssl_error_string()) !== false) {
        $errors[] = $error;
    }
    echo "OpenSSL Errors: " . implode(', ', $errors) . "\n";
} else {
    echo "✅ Public key loaded successfully\n";
}

// Test signature generation
echo "\n=== SIGNATURE TEST ===\n";
if ($privateKeyResource !== false && $publicKeyResource !== false) {
    $testData = "merchant_id=$merchantId&biz_code=EC&country=US";
    $signatureResult = openssl_sign($testData, $signature, $privateKeyResource, OPENSSL_ALGO_SHA1);

    if ($signatureResult) {
        $base64Signature = base64_encode($signature);
        echo "✅ Signature generated successfully\n";
        echo "Signature length: " . strlen($base64Signature) . "\n";

        // Test verification
        $verifyResult = openssl_verify($testData, $signature, $publicKeyResource, OPENSSL_ALGO_SHA1);
        echo "Verification result: " . $verifyResult . " (1=valid, 0=invalid, -1=error)\n";

        if ($verifyResult === 1) {
            echo "✅ SIGNATURE VERIFICATION SUCCESSFUL!\n";
            echo "Keys are working correctly\n";
        } else {
            echo "❌ SIGNATURE VERIFICATION FAILED!\n";
            echo "Keys don't match or are incorrect\n";
        }
    } else {
        echo "❌ Signature generation failed\n";
    }
} else {
    echo "❌ Cannot test signature - keys not loaded\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
if ($sandbox === 'false' && $merchantId === '202501130004033009') {
    echo "1. You're using production environment correctly\n";
    echo "2. But the private key is invalid\n";
    echo "3. You need to get the correct production private key\n";
    echo "4. Contact LianLianPay support for production keys\n";
    echo "5. Or check if you have the right key file\n";
} else {
    echo "1. Environment configuration is incorrect\n";
    echo "2. Should be: LIANLIAN_SANDBOX=false\n";
    echo "3. Should be: LIANLIAN_MERCHANT_ID=202501130004033009\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
