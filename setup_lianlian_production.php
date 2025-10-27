<?php

/**
 * LianLian Pay Production Setup & Test Script
 * 
 * Script này kiểm tra cấu hình LianLianPay production và test các chức năng cơ bản
 */

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\LianLianPayServiceV2;
use Illuminate\Support\Facades\Log;

echo "🚀 LianLian Pay Production Setup & Test\n";
echo "=====================================\n\n";

try {
    // 1. Kiểm tra cấu hình
    echo "1. 📋 Checking Configuration...\n";

    $sandbox = config('lianlian.sandbox');
    $merchantId = config('lianlian.merchant_id');
    $subMerchantId = config('lianlian.sub_merchant_id');
    $publicKey = config('lianlian.public_key');
    $privateKey = config('lianlian.private_key');
    $baseUrl = $sandbox ? config('lianlian.sandbox_url') : config('lianlian.production_url');

    echo "   - Sandbox Mode: " . ($sandbox ? 'YES' : 'NO') . "\n";
    echo "   - Merchant ID: " . ($merchantId ? 'Configured' : 'NOT SET') . "\n";
    echo "   - Sub Merchant ID: " . ($subMerchantId ? 'Configured' : 'NOT SET') . "\n";
    echo "   - Public Key: " . ($publicKey ? 'Configured' : 'NOT SET') . "\n";
    echo "   - Private Key: " . ($privateKey ? 'Configured' : 'NOT SET') . "\n";
    echo "   - Base URL: $baseUrl\n";
    echo "   - Environment: " . (app()->environment()) . "\n\n";

    // 2. Test LianLianPay Service
    echo "2. 🔧 Testing LianLianPay Service...\n";

    $lianLianService = new LianLianPayServiceV2();

    // Test get token
    echo "   - Testing get token...\n";
    try {
        $tokenResponse = $lianLianService->getPaymentToken();

        if (isset($tokenResponse['return_code']) && $tokenResponse['return_code'] === 'SUCCESS') {
            echo "   ✅ Token generation: SUCCESS\n";
            echo "   - Token: " . (isset($tokenResponse['order']) ? substr($tokenResponse['order'], 0, 20) . '...' : 'N/A') . "\n";
        } else {
            echo "   ❌ Token generation: FAILED\n";
            echo "   - Error: " . ($tokenResponse['return_message'] ?? 'Unknown error') . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Token generation: ERROR\n";
        echo "   - Error: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // 3. Kiểm tra URLs
    echo "3. 🌐 Checking URLs...\n";

    $redirectUrl = url('/payment/lianlian/return');
    $webhookUrl = url('/payment/lianlian/webhook-v2');
    $cancelUrl = url('/payment/lianlian/cancel');

    echo "   - Redirect URL: $redirectUrl\n";
    echo "   - Webhook URL: $webhookUrl\n";
    echo "   - Cancel URL: $cancelUrl\n";

    // Test URL accessibility
    echo "   - Testing URL accessibility...\n";

    $testUrls = [
        'Redirect' => $redirectUrl,
        'Webhook' => $webhookUrl,
        'Cancel' => $cancelUrl
    ];

    foreach ($testUrls as $name => $url) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        $httpCode = 0;

        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (strpos($header, 'HTTP/') === 0) {
                    preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches);
                    $httpCode = isset($matches[1]) ? (int)$matches[1] : 0;
                    break;
                }
            }
        }

        if ($httpCode >= 200 && $httpCode < 400) {
            echo "   ✅ $name URL: Accessible (HTTP $httpCode)\n";
        } else {
            echo "   ⚠️  $name URL: Not accessible (HTTP $httpCode)\n";
        }
    }

    echo "\n";

    // 4. Kiểm tra logs
    echo "4. 📝 Checking Logs...\n";

    $logFiles = [
        'LianLian' => storage_path('logs/lianlian.log'),
        'Laravel' => storage_path('logs/laravel.log')
    ];

    foreach ($logFiles as $name => $logFile) {
        if (file_exists($logFile)) {
            $size = filesize($logFile);
            $lastModified = date('Y-m-d H:i:s', filemtime($logFile));
            echo "   ✅ $name Log: Exists (Size: " . number_format($size) . " bytes, Modified: $lastModified)\n";
        } else {
            echo "   ⚠️  $name Log: Not found\n";
        }
    }

    echo "\n";

    // 5. Production URLs
    echo "5. 🔗 Production URLs:\n";
    echo "   - API Base URL: https://gpapi.lianlianpay.com/v3\n";
    echo "   - SDK URL: https://gacashier.lianlianpay-inc.com/llpay.min.js\n";
    echo "   - Iframe URL: https://gpapi.lianlianpay.com/v3/merchants/$merchantId/payments\n";

    echo "\n";

    // 6. Recommendations
    echo "6. 💡 Recommendations:\n";

    if ($sandbox) {
        echo "   ⚠️  WARNING: Currently in SANDBOX mode\n";
        echo "   - Set LIANLIAN_SANDBOX=false in .env for production\n";
    }

    if (!$merchantId || !$publicKey || !$privateKey) {
        echo "   ⚠️  WARNING: Missing required configuration\n";
        echo "   - Check LIANLIAN_MERCHANT_ID, LIANLIAN_PUBLIC_KEY, LIANLIAN_PRIVATE_KEY\n";
    }

    if (app()->environment() !== 'production') {
        echo "   ℹ️  INFO: Not in production environment\n";
        echo "   - Current environment: " . app()->environment() . "\n";
    }

    echo "\n";
    echo "✅ Setup check completed!\n";
    echo "📖 For detailed testing, visit: " . url('/payment/lianlian/test-config') . "\n";
} catch (Exception $e) {
    echo "❌ Error during setup check: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
