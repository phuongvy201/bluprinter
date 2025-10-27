<?php

require_once 'vendor/autoload.php';

echo "=== DEBUG PRIVATE KEY FORMAT ===\n\n";

// Load environment variables
$privateKey = getenv('LIANLIAN_PRIVATE_KEY') ?: '';

echo "1. 📋 THÔNG TIN PRIVATE KEY:\n";
echo "   - Length: " . strlen($privateKey) . "\n";
echo "   - Preview: " . substr($privateKey, 0, 100) . "...\n";
echo "   - Has headers: " . (strpos($privateKey, '-----BEGIN') !== false ? 'CÓ' : 'KHÔNG') . "\n\n";

if (empty($privateKey)) {
    echo "❌ PRIVATE KEY KHÔNG CÓ!\n";
    exit;
}

echo "2. 🔍 KIỂM TRA FORMAT:\n";

// Kiểm tra các format phổ biến
$formats = [
    'PEM with headers' => "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($privateKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----",
    'PEM without headers' => $privateKey,
    'PKCS#1 format' => "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($privateKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----",
    'PKCS#8 format' => "-----BEGIN PRIVATE KEY-----\n" . wordwrap($privateKey, 64, "\n", true) . "\n-----END PRIVATE KEY-----"
];

foreach ($formats as $formatName => $keyWithHeaders) {
    echo "   Testing $formatName...\n";

    try {
        $keyResource = openssl_get_privatekey($keyWithHeaders);
        if ($keyResource !== false) {
            echo "   ✅ $formatName: VALID\n";

            // Test signature
            $testData = "test_signature_data";
            $signature = '';
            if (openssl_sign($testData, $signature, $keyResource, OPENSSL_ALGO_SHA1)) {
                echo "   ✅ $formatName: Can sign data\n";
            } else {
                echo "   ❌ $formatName: Cannot sign data\n";
            }

            if (PHP_VERSION_ID < 80000) {
                openssl_free_key($keyResource);
            }
        } else {
            echo "   ❌ $formatName: INVALID\n";
        }
    } catch (Exception $e) {
        echo "   ❌ $formatName: ERROR - " . $e->getMessage() . "\n";
    }
}

echo "\n3. 🧪 TEST VỚI LIANLIAN SDK:\n";

try {
    $signTool = new \lianlianpay\v3sdk\utils\LianLianSign();

    $testData = [
        'merchant_id' => '202501130004033009',
        'biz_code' => 'EC',
        'country' => 'US'
    ];

    echo "   - Test data: " . json_encode($testData) . "\n";

    $signature = $signTool->sign($testData, $privateKey);
    echo "   - Signature generated: " . (strlen($signature) > 0 ? 'CÓ' : 'KHÔNG') . "\n";

    if (strlen($signature) > 0) {
        echo "   - Signature length: " . strlen($signature) . "\n";
        echo "   - Signature preview: " . substr($signature, 0, 50) . "...\n";
    }
} catch (Exception $e) {
    echo "   ❌ SDK Error: " . $e->getMessage() . "\n";
}

echo "\n4. 💡 HƯỚNG DẪN SỬA:\n";
echo "   - Kiểm tra private key từ LianLianPay\n";
echo "   - Đảm bảo key không có headers trong .env\n";
echo "   - Đảm bảo key không có line breaks\n";
echo "   - Đảm bảo key là RSA format\n";

echo "\n=== HOÀN THÀNH ===\n";
