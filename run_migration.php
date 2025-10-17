<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run the migration
Artisan::call('migrate', ['--force' => true]);

echo "Migration completed successfully!\n";
echo Artisan::output();
