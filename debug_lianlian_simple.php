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

// Test simple data
$testData = [
    'merchant_id' => $merchantId,
    'biz_code' => 'EC',
    'country' => 'US'
];

$signTool = new LianLianSign();

echo "=== TESTING SIGNATURE GENERATION ===\n";

try {
    // Test signature generation
    $signature = $signTool->sign($testData, $privateKey);
    echo "Generated Signature: " . $signature . "\n";
    echo "Signature Length: " . strlen($signature) . "\n\n";

    // Test signature verification
    $isValid = $signTool->verify($testData, $signature, $publicKey);
    echo "Signature Verification: " . ($isValid ? 'VALID' : 'INVALID') . "\n\n";

    // Test with text signature
    $testText = "merchant_id=" . $merchantId . "&biz_code=EC&country=US";
    $textSignature = $signTool->sign_text($testText, $privateKey);
    $textVerify = $signTool->verify_text($testText, $textSignature, $publicKey);
    echo "Text Signature: " . $textSignature . "\n";
    echo "Text Verification: " . ($textVerify ? 'VALID' : 'INVALID') . "\n\n";

    // Check key format
    echo "=== KEY FORMAT CHECK ===\n";

    // Check if keys have proper PEM format
    $privateKeyHasHeaders = strpos($privateKey, '-----BEGIN') !== false;
    $publicKeyHasHeaders = strpos($publicKey, '-----BEGIN') !== false;

    echo "Private key has PEM headers: " . ($privateKeyHasHeaders ? 'YES' : 'NO') . "\n";
    echo "Public key has PEM headers: " . ($publicKeyHasHeaders ? 'YES' : 'NO') . "\n\n";

    // Test OpenSSL functions directly
    echo "=== OPENSSL DIRECT TEST ===\n";

    // Prepare private key with headers if needed
    if (!$privateKeyHasHeaders) {
        $privateKeyWithHeaders = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($privateKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
    } else {
        $privateKeyWithHeaders = $privateKey;
    }

    // Prepare public key with headers if needed
    if (!$publicKeyHasHeaders) {
        $publicKeyWithHeaders = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($publicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
    } else {
        $publicKeyWithHeaders = $publicKey;
    }

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
        echo "Private key loaded successfully\n";
        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($privateKeyResource);
        }
    }

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
        echo "Public key loaded successfully\n";
    }

    // Test manual signature
    echo "\n=== MANUAL SIGNATURE TEST ===\n";
    $testString = "merchant_id=" . $merchantId . "&biz_code=EC&country=US";
    echo "Test string: " . $testString . "\n";

    if ($privateKeyResource !== false) {
        $signatureResult = openssl_sign($testString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA1);
        if ($signatureResult) {
            $base64Signature = base64_encode($signature);
            echo "Manual signature: " . $base64Signature . "\n";

            // Verify with public key
            if ($publicKeyResource !== false) {
                $verifyResult = openssl_verify($testString, $signature, $publicKeyResource, OPENSSL_ALGO_SHA1);
                echo "Manual verification result: " . $verifyResult . " (1=valid, 0=invalid, -1=error)\n";
            }
        } else {
            echo "ERROR: Failed to create signature\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
