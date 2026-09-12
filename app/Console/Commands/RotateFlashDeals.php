<?php

namespace App\Console\Commands;

use App\Services\FlashDealRotationService;
use Illuminate\Console\Command;

class RotateFlashDeals extends Command
{
    protected $signature = 'flash-deals:rotate';

    protected $description = 'Generate flash deals from rules, templates, and auto-enrollment';

    public function handle(FlashDealRotationService $rotation): int
    {
        $result = $rotation->rotate();

        $this->info('Flash deals rotated. Created: ' . ($result['created'] ?? 0));

        return self::SUCCESS;
    }
}
