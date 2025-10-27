<?php

require_once 'vendor/autoload.php';

echo "=== ENVIRONMENT DIFFERENCES CHECK ===\n";

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Get current configuration
$merchantId = $_ENV['LIANLIAN_MERCHANT_ID'] ?? '';
$sandbox = $_ENV['LIANLIAN_SANDBOX'] ?? 'true';
$publicKey = $_ENV['LIANLIAN_PUBLIC_KEY'] ?? '';
$privateKey = $_ENV['LIANLIAN_PRIVATE_KEY'] ?? '';

echo "Current Configuration:\n";
echo "- Merchant ID: $merchantId\n";
echo "- Sandbox Mode: " . ($sandbox === 'true' ? 'YES' : 'NO') . "\n";
echo "- Public Key Length: " . strlen($publicKey) . "\n";
echo "- Private Key Length: " . strlen($privateKey) . "\n\n";

// Check if we're using production vs sandbox
if ($sandbox === 'true') {
    echo "🔍 SANDBOX MODE DETECTED\n";
    echo "This explains why it works on localhost but not on AWS EC2\n\n";

    echo "Sandbox vs Production differences:\n";
    echo "1. Different API endpoints\n";
    echo "2. Different merchant IDs\n";
    echo "3. Different key pairs\n";
    echo "4. Different validation rules\n\n";

    echo "To fix this on AWS EC2, you need to:\n";
    echo "1. Set LIANLIAN_SANDBOX=false in .env\n";
    echo "2. Use production merchant ID\n";
    echo "3. Use production keys\n";
    echo "4. Ensure production API endpoints\n\n";
} else {
    echo "🔍 PRODUCTION MODE DETECTED\n";
    echo "This should work on AWS EC2\n\n";
}

// Test signature with current configuration
echo "=== TESTING CURRENT CONFIGURATION ===\n";

$testData = [
    'merchant_id' => $merchantId,
    'biz_code' => 'EC',
    'country' => 'US'
];

$signTool = new \lianlianpay\v3sdk\utils\LianLianSign();

try {
    $signature = $signTool->sign($testData, $privateKey);
    $isValid = $signTool->verify($testData, $signature, $publicKey);

    echo "Signature Test: " . ($isValid ? 'VALID' : 'INVALID') . "\n";

    if ($isValid) {
        echo "✅ Current configuration is working!\n";
    } else {
        echo "❌ Current configuration has issues\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Check Laravel configuration
echo "\n=== LARAVEL CONFIGURATION ===\n";

try {
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    $lianlianConfig = config('lianlian');

    echo "Laravel Config:\n";
    echo "- Sandbox: " . ($lianlianConfig['sandbox'] ? 'true' : 'false') . "\n";
    echo "- Merchant ID: " . $lianlianConfig['merchant_id'] . "\n";
    echo "- Base URL: " . ($lianlianConfig['sandbox'] ? $lianlianConfig['sandbox_url'] : $lianlianConfig['production_url']) . "\n";

    // Check if config matches environment
    $configMatches = (
        $lianlianConfig['sandbox'] == ($sandbox === 'true') &&
        $lianlianConfig['merchant_id'] == $merchantId
    );

    echo "Config matches environment: " . ($configMatches ? 'YES' : 'NO') . "\n";

    if (!$configMatches) {
        echo "⚠️  Configuration mismatch detected!\n";
        echo "Run: php artisan config:clear\n";
        echo "Then: php artisan config:cache\n";
    }
} catch (Exception $e) {
    echo "❌ Error loading Laravel: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";

if ($sandbox === 'true') {
    echo "1. For AWS EC2 (Production):\n";
    echo "   - Set LIANLIAN_SANDBOX=false\n";
    echo "   - Use production merchant ID\n";
    echo "   - Use production keys\n";
    echo "   - Clear config cache\n\n";

    echo "2. For Localhost (Development):\n";
    echo "   - Keep LIANLIAN_SANDBOX=true\n";
    echo "   - Use sandbox merchant ID\n";
    echo "   - Use sandbox keys\n\n";
} else {
    echo "1. Ensure you're using production keys\n";
    echo "2. Verify merchant ID is correct\n";
    echo "3. Check API endpoints are production\n";
    echo "4. Clear config cache if needed\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
