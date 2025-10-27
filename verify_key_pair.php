<?php

require_once 'vendor/autoload.php';

echo "=== KIỂM TRA KEY PAIR ===\n\n";

// Load environment variables
$merchantId = getenv('LIANLIAN_MERCHANT_ID') ?: '';
$privateKey = getenv('LIANLIAN_PRIVATE_KEY') ?: '';
$publicKey = getenv('LIANLIAN_PUBLIC_KEY') ?: '';

echo "1. 📋 THÔNG TIN KEYS:\n";
echo "   - Merchant ID: $merchantId\n";
echo "   - Private Key Length: " . strlen($privateKey) . "\n";
echo "   - Public Key Length: " . strlen($publicKey) . "\n\n";

// Test key pair matching
echo "2. 🔍 KIỂM TRA KEY PAIR:\n";

try {
    // LianLianPay chỉ cần key content, không cần headers
    // SDK sẽ tự động thêm headers
    $privateKeyWithHeaders = "-----BEGIN RSA PRIVATE KEY-----\n" .
        wordwrap($privateKey, 64, "\n", true) .
        "\n-----END RSA PRIVATE KEY-----";

    $publicKeyWithHeaders = "-----BEGIN PUBLIC KEY-----\n" .
        wordwrap($publicKey, 64, "\n", true) .
        "\n-----END PUBLIC KEY-----";

    // Load keys
    $privateKeyResource = openssl_get_privatekey($privateKeyWithHeaders);
    $publicKeyResource = openssl_pkey_get_public($publicKeyWithHeaders);

    if ($privateKeyResource === false) {
        echo "   ❌ Private key không hợp lệ\n";
        exit;
    }

    if ($publicKeyResource === false) {
        echo "   ❌ Public key không hợp lệ\n";
        exit;
    }

    echo "   ✅ Cả 2 keys đều hợp lệ\n";

    // Test signature generation and verification
    $testData = "merchant_id=$merchantId&biz_code=EC&country=US";

    // Generate signature
    $signatureResult = openssl_sign($testData, $signature, $privateKeyResource, OPENSSL_ALGO_SHA1);

    if (!$signatureResult) {
        echo "   ❌ Không thể tạo signature\n";
        exit;
    }

    echo "   ✅ Signature được tạo thành công\n";

    // Verify signature
    $verifyResult = openssl_verify($testData, $signature, $publicKeyResource, OPENSSL_ALGO_SHA1);

    if ($verifyResult === 1) {
        echo "   ✅ KEY PAIR MATCH! Keys hoạt động đúng\n";
        echo "   🎉 Vấn đề không phải ở keys\n";
    } elseif ($verifyResult === 0) {
        echo "   ❌ KEY PAIR KHÔNG MATCH!\n";
        echo "   🔍 Nguyên nhân: Private key và Public key không cùng cặp\n";
        echo "   💡 Giải pháp: Lấy đúng cặp keys từ LianLianPay\n";
    } else {
        echo "   ❌ Lỗi verification: $verifyResult\n";
    }

    // Clean up
    if (PHP_VERSION_ID < 80000) {
        openssl_free_key($privateKeyResource);
    }
} catch (Exception $e) {
    echo "   ❌ Lỗi: " . $e->getMessage() . "\n";
}

echo "\n3. 🔧 HƯỚNG DẪN SỬA LỖI:\n";

if ($verifyResult === 0) {
    echo "   ❌ KEYS KHÔNG MATCH - Cần làm:\n";
    echo "   1. Kiểm tra lại keys từ LianLianPay dashboard\n";
    echo "   2. Đảm bảo Private key và Public key cùng cặp\n";
    echo "   3. Copy lại keys chính xác (không thiếu ký tự)\n";
    echo "   4. Kiểm tra format PEM\n";
    echo "   5. Clear cache: php artisan config:clear\n";
} else {
    echo "   ✅ KEYS HOẠT ĐỘNG ĐÚNG\n";
    echo "   🔍 Vấn đề có thể ở:\n";
    echo "   1. Environment configuration\n";
    echo "   2. API endpoints\n";
    echo "   3. Request data format\n";
}

echo "\n4. 📞 LIÊN HỆ HỖ TRỢ:\n";
echo "   - LianLianPay Support: support@lianlianpay.com\n";
echo "   - Developer Portal: https://developer.lianlianpay.com/\n";
echo "   - Kiểm tra keys trong merchant dashboard\n";

echo "\n=== HOÀN THÀNH ===\n";
