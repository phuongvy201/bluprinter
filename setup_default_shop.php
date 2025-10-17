<?php

/**
 * Setup Default Shop Configuration Script
 * 
 * Script này giúp bạn cấu hình shop mặc định cho API một cách nhanh chóng
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║        🏪 SETUP DEFAULT SHOP CONFIGURATION                    ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// ============================================================================
// Step 1: Xem danh sách shops
// ============================================================================
echo "📋 Danh sách shops hiện có:\n";
echo "─────────────────────────────────────────────────────────────────\n";

try {
    $shops = DB::table('shops')
        ->select('id', 'shop_name', 'shop_status', 'total_products')
        ->orderBy('id')
        ->get();

    if ($shops->isEmpty()) {
        echo "⚠️  Không tìm thấy shop nào trong database!\n";
        echo "    Vui lòng tạo shop trước khi cấu hình.\n\n";
        exit(1);
    }

    printf("%-6s %-30s %-12s %-15s\n", 'ID', 'Shop Name', 'Status', 'Products');
    echo "─────────────────────────────────────────────────────────────────\n";

    foreach ($shops as $shop) {
        $statusIcon = $shop->shop_status === 'active' ? '✓' : '✗';
        printf(
            "%-6s %-30s %-12s %-15s\n",
            $shop->id,
            substr($shop->shop_name, 0, 28),
            "$statusIcon $shop->shop_status",
            $shop->total_products ?? 0
        );
    }

    echo "─────────────────────────────────────────────────────────────────\n";
    echo "\n";

    // ============================================================================
    // Step 2: Hiển thị config hiện tại
    // ============================================================================
    echo "⚙️  Cấu hình hiện tại:\n";
    echo "─────────────────────────────────────────────────────────────────\n";

    $currentConfig = config('api.default_shop_id');
    $envFile = base_path('.env');
    $envExists = file_exists($envFile);

    echo "Config value: " . ($currentConfig ?: 'NOT SET') . "\n";
    echo ".env file: " . ($envExists ? '✓ Exists' : '✗ Not Found') . "\n";

    if ($envExists) {
        $envContent = file_get_contents($envFile);
        if (strpos($envContent, 'API_DEFAULT_SHOP_ID') !== false) {
            preg_match('/API_DEFAULT_SHOP_ID=(\d+)/', $envContent, $matches);
            $envValue = $matches[1] ?? 'NOT SET';
            echo ".env API_DEFAULT_SHOP_ID: $envValue\n";
        } else {
            echo ".env API_DEFAULT_SHOP_ID: ✗ Not configured\n";
        }
    }

    echo "─────────────────────────────────────────────────────────────────\n";
    echo "\n";

    // ============================================================================
    // Step 3: Hướng dẫn setup
    // ============================================================================
    echo "📝 Hướng dẫn cấu hình:\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "\n";
    echo "1️⃣  Chọn Shop ID bạn muốn làm mặc định (từ bảng trên)\n";
    echo "\n";
    echo "2️⃣  Thêm dòng sau vào file .env của bạn:\n";
    echo "\n";
    echo "    API_DEFAULT_SHOP_ID=5  # Thay 5 bằng Shop ID bạn chọn\n";
    echo "\n";
    echo "3️⃣  Clear cache Laravel:\n";
    echo "\n";
    echo "    php artisan config:cache\n";
    echo "\n";
    echo "4️⃣  Verify cấu hình:\n";
    echo "\n";
    echo "    php artisan tinker\n";
    echo "    >>> config('api.default_shop_id')\n";
    echo "\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "\n";

    // ============================================================================
    // Step 4: Quick Setup (Optional)
    // ============================================================================
    if ($envExists) {
        echo "⚡ Quick Setup:\n";
        echo "─────────────────────────────────────────────────────────────────\n";
        echo "\n";

        // Tìm shop active đầu tiên
        $firstActiveShop = $shops->where('shop_status', 'active')->first();

        if ($firstActiveShop) {
            echo "Nhập Shop ID bạn muốn set làm mặc định (nhấn Enter để dùng #{$firstActiveShop->id}):\n";
            echo "Shop ID: ";

            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            $shopId = trim($line);
            fclose($handle);

            if (empty($shopId)) {
                $shopId = $firstActiveShop->id;
            }

            $shopId = (int)$shopId;

            // Verify shop exists
            $selectedShop = $shops->firstWhere('id', $shopId);

            if (!$selectedShop) {
                echo "\n❌ Shop ID $shopId không tồn tại!\n\n";
                exit(1);
            }

            echo "\n✓ Đã chọn: #{$selectedShop->id} - {$selectedShop->shop_name}\n";
            echo "\nĐang cập nhật file .env...\n";

            // Update .env file
            $envContent = file_get_contents($envFile);

            if (strpos($envContent, 'API_DEFAULT_SHOP_ID') !== false) {
                // Update existing
                $envContent = preg_replace(
                    '/API_DEFAULT_SHOP_ID=\d+/',
                    "API_DEFAULT_SHOP_ID=$shopId",
                    $envContent
                );
                echo "✓ Đã cập nhật API_DEFAULT_SHOP_ID=$shopId\n";
            } else {
                // Add new
                $envContent .= "\n# API Configuration\nAPI_DEFAULT_SHOP_ID=$shopId\n";
                echo "✓ Đã thêm API_DEFAULT_SHOP_ID=$shopId\n";
            }

            file_put_contents($envFile, $envContent);

            echo "\n✅ Hoàn tất! Bây giờ chạy:\n\n";
            echo "    php artisan config:cache\n\n";
            echo "Để áp dụng thay đổi.\n\n";
        } else {
            echo "⚠️  Không có shop active nào. Vui lòng cấu hình thủ công.\n\n";
        }
    } else {
        echo "⚠️  File .env không tồn tại. Vui lòng tạo file .env từ .env.example\n\n";
    }

    // ============================================================================
    // Step 5: Test command
    // ============================================================================
    echo "🧪 Test API sau khi cấu hình:\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "\n";
    echo "curl -X POST http://localhost/api/products/create \\\n";
    echo "  -H \"X-API-Token: YOUR_TOKEN\" \\\n";
    echo "  -F \"name=Test Product\" \\\n";
    echo "  -F \"template_id=1\" \\\n";
    echo "  -F \"images=@image.jpg\"\n";
    echo "\n";
    echo "Kiểm tra 'shop_id' trong response phải bằng với Shop ID bạn đã set!\n";
    echo "\n";
    echo "─────────────────────────────────────────────────────────────────\n";
    echo "\n";

    // ============================================================================
    // Statistics
    // ============================================================================
    echo "📊 Thống kê API Products:\n";
    echo "─────────────────────────────────────────────────────────────────\n";

    $apiProducts = DB::table('products')
        ->select('shop_id', DB::raw('COUNT(*) as count'))
        ->where('created_by', 'api')
        ->groupBy('shop_id')
        ->get();

    if ($apiProducts->isEmpty()) {
        echo "Chưa có products nào được tạo qua API.\n";
    } else {
        printf("%-10s %-30s %-10s\n", 'Shop ID', 'Shop Name', 'Products');
        echo "─────────────────────────────────────────────────────────────────\n";

        foreach ($apiProducts as $stat) {
            $shop = $shops->firstWhere('id', $stat->shop_id);
            printf(
                "%-10s %-30s %-10s\n",
                $stat->shop_id ?? 'NULL',
                $shop ? substr($shop->shop_name, 0, 28) : 'N/A',
                $stat->count
            );
        }
    }

    echo "─────────────────────────────────────────────────────────────────\n";
    echo "\n";

    // ============================================================================
    // Documentation links
    // ============================================================================
    echo "📚 Tài liệu chi tiết:\n";
    echo "  • SETUP_DEFAULT_SHOP_CONFIG.md\n";
    echo "  • API_DEFAULT_SHOP_CONFIGURATION.md\n";
    echo "  • QUICK_START_API_SHOP.md\n";
    echo "\n";
    echo "✨ Done!\n\n";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Vui lòng kiểm tra:\n";
    echo "  1. Database connection trong file .env\n";
    echo "  2. Bảng 'shops' có tồn tại không\n";
    echo "  3. Chạy: php artisan migrate\n";
    echo "\n";
    exit(1);
}
