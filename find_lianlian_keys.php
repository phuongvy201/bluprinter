<?php

echo "=== TÌM KEYS LIANLIAN PAY ===\n\n";

// Load environment variables
require_once 'vendor/autoload.php';

echo "1. 📋 KIỂM TRA FILE .ENV:\n";
$envFile = '.env';
if (file_exists($envFile)) {
    echo "   ✅ File .env tồn tại\n";

    $envContent = file_get_contents($envFile);

    // Check for LianLian keys
    $lianlianKeys = [
        'LIANLIAN_SANDBOX' => strpos($envContent, 'LIANLIAN_SANDBOX') !== false,
        'LIANLIAN_MERCHANT_ID' => strpos($envContent, 'LIANLIAN_MERCHANT_ID') !== false,
        'LIANLIAN_PUBLIC_KEY' => strpos($envContent, 'LIANLIAN_PUBLIC_KEY') !== false,
        'LIANLIAN_PRIVATE_KEY' => strpos($envContent, 'LIANLIAN_PRIVATE_KEY') !== false
    ];

    foreach ($lianlianKeys as $key => $exists) {
        echo "   - $key: " . ($exists ? '✅ Có' : '❌ Không có') . "\n";
    }
} else {
    echo "   ❌ File .env không tồn tại\n";
}

echo "\n2. 🔍 KIỂM TRA CẤU HÌNH HIỆN TẠI:\n";
$merchantId = $_ENV['LIANLIAN_MERCHANT_ID'] ?? '';
$sandbox = $_ENV['LIANLIAN_SANDBOX'] ?? '';
$publicKey = $_ENV['LIANLIAN_PUBLIC_KEY'] ?? '';
$privateKey = $_ENV['LIANLIAN_PRIVATE_KEY'] ?? '';

echo "   - Merchant ID: " . ($merchantId ? $merchantId : 'KHÔNG CÓ') . "\n";
echo "   - Sandbox: " . ($sandbox ?: 'KHÔNG CÓ') . "\n";
echo "   - Public Key: " . ($publicKey ? 'CÓ (' . strlen($publicKey) . ' ký tự)' : 'KHÔNG CÓ') . "\n";
echo "   - Private Key: " . ($privateKey ? 'CÓ (' . strlen($privateKey) . ' ký tự)' : 'KHÔNG CÓ') . "\n";

echo "\n3. 🎯 PHÂN TÍCH ENVIRONMENT:\n";
if ($sandbox === 'true') {
    echo "   🔍 SANDBOX MODE (Test)\n";
    echo "   - Cần sandbox keys từ LianLianPay Developer Portal\n";
    echo "   - URL: https://developer.lianlianpay.com/\n";
    echo "   - Merchant ID thường bắt đầu bằng 2025...\n";
} elseif ($sandbox === 'false') {
    echo "   🔍 PRODUCTION MODE (Live)\n";
    echo "   - Cần production keys từ LianLianPay\n";
    echo "   - Liên hệ support để lấy production keys\n";
    echo "   - Merchant ID: 202501130004033009\n";
} else {
    echo "   ⚠️  CHƯA CẤU HÌNH SANDBOX/PRODUCTION\n";
}

echo "\n4. 📍 NƠI LẤY KEYS:\n";
echo "   A. SANDBOX KEYS (Test):\n";
echo "      1. Đăng ký: https://developer.lianlianpay.com/\n";
echo "      2. Tạo sandbox account\n";
echo "      3. Lấy keys từ dashboard\n";
echo "      4. Cập nhật .env với sandbox keys\n\n";

echo "   B. PRODUCTION KEYS (Live):\n";
echo "      1. Hoàn thành merchant verification\n";
echo "      2. Liên hệ LianLianPay support\n";
echo "      3. Lấy production keys\n";
echo "      4. Cập nhật .env với production keys\n";

echo "\n5. 🔧 CÁCH CẬP NHẬT KEYS:\n";
echo "   - Mở file .env\n";
echo "   - Cập nhật các giá trị:\n";
echo "     LIANLIAN_SANDBOX=true/false\n";
echo "     LIANLIAN_MERCHANT_ID=your_merchant_id\n";
echo "     LIANLIAN_PUBLIC_KEY=your_public_key\n";
echo "     LIANLIAN_PRIVATE_KEY=your_private_key\n";
echo "   - Clear cache: php artisan config:clear\n";

echo "\n6. ⚠️  LƯU Ý QUAN TRỌNG:\n";
echo "   - Keys phải đúng format PEM\n";
echo "   - Private key và Public key phải match\n";
echo "   - Sandbox keys chỉ dùng cho test\n";
echo "   - Production keys chỉ dùng cho live\n";
echo "   - Không commit keys vào git\n";

echo "\n=== HOÀN THÀNH ===\n";
