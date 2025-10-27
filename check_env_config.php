<?php

echo "=== ENVIRONMENT CONFIGURATION CHECK ===\n";

// Check if .env file exists
$envFile = '.env';
if (file_exists($envFile)) {
    echo "✅ .env file exists\n";
    $envContent = file_get_contents($envFile);

    // Check for LianLian Pay configuration
    $lianlianConfigs = [
        'LIANLIAN_SANDBOX',
        'LIANLIAN_MERCHANT_ID',
        'LIANLIAN_PUBLIC_KEY',
        'LIANLIAN_PRIVATE_KEY'
    ];

    foreach ($lianlianConfigs as $config) {
        if (strpos($envContent, $config) !== false) {
            echo "✅ $config found in .env\n";
        } else {
            echo "❌ $config NOT found in .env\n";
        }
    }
} else {
    echo "❌ .env file does not exist\n";
}

// Check Laravel configuration
echo "\n=== LARAVEL CONFIGURATION ===\n";

// Load Laravel app
try {
    require_once 'vendor/autoload.php';

    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    // Check LianLian configuration
    $lianlianConfig = config('lianlian');

    echo "LianLian Config:\n";
    echo "- Sandbox: " . ($lianlianConfig['sandbox'] ? 'true' : 'false') . "\n";
    echo "- Merchant ID: " . $lianlianConfig['merchant_id'] . "\n";
    echo "- Public Key Length: " . strlen($lianlianConfig['public_key']) . "\n";
    echo "- Private Key Length: " . strlen($lianlianConfig['private_key']) . "\n";
    echo "- Base URL: " . $lianlianConfig['baseUrl'] . "\n";

    // Check if keys are loaded from environment
    $merchantId = env('LIANLIAN_MERCHANT_ID');
    $publicKey = env('LIANLIAN_PUBLIC_KEY');
    $privateKey = env('LIANLIAN_PRIVATE_KEY');

    echo "\nEnvironment Variables:\n";
    echo "- LIANLIAN_MERCHANT_ID: " . ($merchantId ? 'SET' : 'NOT SET') . "\n";
    echo "- LIANLIAN_PUBLIC_KEY: " . ($publicKey ? 'SET' : 'NOT SET') . "\n";
    echo "- LIANLIAN_PRIVATE_KEY: " . ($privateKey ? 'SET' : 'NOT SET') . "\n";

    if ($merchantId && $publicKey && $privateKey) {
        echo "\n✅ All LianLian Pay environment variables are set\n";

        // Test signature with loaded keys
        echo "\n=== TESTING WITH LOADED KEYS ===\n";

        $testData = [
            'merchant_id' => $merchantId,
            'biz_code' => 'EC',
            'country' => 'US'
        ];

        $signTool = new \lianlianpay\v3sdk\utils\LianLianSign();

        try {
            $signature = $signTool->sign($testData, $privateKey);
            $isValid = $signTool->verify($testData, $signature, $publicKey);

            echo "Signature: " . substr($signature, 0, 50) . "...\n";
            echo "Verification: " . ($isValid ? 'VALID' : 'INVALID') . "\n";

            if ($isValid) {
                echo "✅ Keys are working correctly!\n";
            } else {
                echo "❌ Keys are not working - signature verification failed\n";
            }
        } catch (Exception $e) {
            echo "❌ Error testing keys: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\n❌ Some LianLian Pay environment variables are missing\n";
    }
} catch (Exception $e) {
    echo "❌ Error loading Laravel: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETE ===\n";
