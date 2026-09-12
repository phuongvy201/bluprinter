<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\CollectionAiMatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MatchProductToCollectionsWithAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public int $productId) {}

    public function handle(CollectionAiMatchService $matcher): void
    {
        if (! $matcher->enabled()) {
            return;
        }

        $product = Product::find($this->productId);
        if (! $product) {
            return;
        }

        try {
            $matcher->matchProduct($product);
        } catch (\Throwable $e) {
            Log::warning('AI collection match failed for product', [
                'product_id' => $this->productId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
