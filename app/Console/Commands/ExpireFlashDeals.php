<?php

namespace App\Console\Commands;

use App\Services\FlashDealRotationService;
use Illuminate\Console\Command;

class ExpireFlashDeals extends Command
{
    protected $signature = 'flash-deals:expire';

    protected $description = 'Expire ended flash deals and restore product prices';

    public function handle(FlashDealRotationService $rotation): int
    {
        $count = $rotation->expireEndedDeals();

        $this->info('Expired flash deals: ' . $count);

        return self::SUCCESS;
    }
}
