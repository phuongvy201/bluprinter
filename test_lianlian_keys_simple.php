<?php

require_once 'vendor/autoload.php';

echo "=== TEST LIANLIAN KEYS (SIMPLE) ===\n\n";

// Load environment variables
$merchantId = getenv('LIANLIAN_MERCHANT_ID') ?: '';
$privateKey = getenv('LIANLIAN_PRIVATE_KEY') ?: '';
$publicKey = getenv('LIANLIAN_PUBLIC_KEY') ?: '';

echo "1. 📋 THÔNG TIN KEYS:\n";
echo "   - Merchant ID: " . ($merchantId ?: 'KHÔNG CÓ') . "\n";
echo "   - Private Key: " . (strlen($privateKey) > 0 ? 'CÓ (' . strlen($privateKey) . ' ký tự)' : 'KHÔNG CÓ') . "\n";
echo "   - Public Key: " . (strlen($publicKey) > 0 ? 'CÓ (' . strlen($publicKey) . ' ký tự)' : 'KHÔNG CÓ') . "\n\n";

if (empty($privateKey) || empty($publicKey)) {
    echo "❌ KEYS CHƯA ĐƯỢC CẤU HÌNH!\n";
    echo "💡 Cần cập nhật file .env với keys từ LianLianPay\n";
    exit;
}

echo "2. 🔍 KIỂM TRA FORMAT KEYS:\n";

// Kiểm tra private key
$privateKeyPreview = substr($privateKey, 0, 50) . '...';
echo "   - Private Key Preview: $privateKeyPreview\n";

// Kiểm tra public key  
$publicKeyPreview = substr($publicKey, 0, 50) . '...';
echo "   - Public Key Preview: $publicKeyPreview\n";

// Kiểm tra có headers không
$privateHasHeaders = strpos($privateKey, '-----BEGIN') !== false;
$publicHasHeaders = strpos($publicKey, '-----BEGIN') !== false;

echo "   - Private Key có headers: " . ($privateHasHeaders ? 'CÓ' : 'KHÔNG') . "\n";
echo "   - Public Key có headers: " . ($publicHasHeaders ? 'CÓ' : 'KHÔNG') . "\n";

if ($privateHasHeaders || $publicHasHeaders) {
    echo "   ⚠️  Keys có headers - LianLianPay không cần headers\n";
    echo "   💡 Chỉ cần key content thuần túy\n";
}

echo "\n3. 🧪 TEST SIGNATURE VỚI LIANLIAN SDK:\n";

try {
    $signTool = new \lianlianpay\v3sdk\utils\LianLianSign();

    // Test data đơn giản
    $testData = [
        'merchant_id' => $merchantId,
        'biz_code' => 'EC',
        'country' => 'US'
    ];

    echo "   - Test data: " . json_encode($testData) . "\n";

    // Test signature generation
    $signature = $signTool->sign($testData, $privateKey);
    echo "   - Signature generated: " . (strlen($signature) > 0 ? 'CÓ' : 'KHÔNG') . "\n";
    echo "   - Signature length: " . strlen($signature) . "\n";

    if (strlen($signature) > 0) {
        // Test verification
        $isValid = $signTool->verify($testData, $signature, $publicKey);
        echo "   - Signature verification: " . ($isValid ? '✅ VALID' : '❌ INVALID') . "\n";

        if ($isValid) {
            echo "   🎉 KEYS HOẠT ĐỘNG ĐÚNG!\n";
            echo "   🎉 KEYS HOẠT ĐỘNG ĐÚNG!\n";
            echo "   🎉 KEYS HOẠT ĐỘNG ĐÚNG!\n";
        } else {
            echo "   ❌ KEYS KHÔNG MATCH!\n";
            echo "   💡 Cần kiểm tra lại keys từ LianLianPay\n";
        }
    } else {
        echo "   ❌ Không thể tạo signature\n";
    }
} catch (Exception $e) {
    echo "   ❌ Lỗi: " . $e->getMessage() . "\n";
}

echo "\n4. 📝 HƯỚNG DẪN CẬP NHẬT .ENV:\n";
echo "   Format đúng cho .env:\n";
echo "   LIANLIAN_MERCHANT_ID=your_merchant_id\n";
echo "   LIANLIAN_PUBLIC_KEY=your_public_key_content_only\n";
echo "   LIANLIAN_PRIVATE_KEY=your_private_key_content_only\n";
echo "   \n";
echo "   ⚠️  LƯU Ý:\n";
echo "   - Chỉ cần key content, không cần headers\n";
echo "   - Không cần -----BEGIN RSA PRIVATE KEY-----\n";
echo "   - Không cần -----END RSA PRIVATE KEY-----\n";
echo "   - Chỉ cần nội dung key thuần túy\n";

echo "\n=== HOÀN THÀNH ===\n";
