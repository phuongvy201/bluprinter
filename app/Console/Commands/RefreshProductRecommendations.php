<?php

namespace App\Console\Commands;

use App\Models\ProductRecommendation;
use App\Services\FrequentlyBoughtTogetherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshProductRecommendations extends Command
{
    protected $signature = 'fbt:refresh {--limit=32 : Max related products per source product}';

    protected $description = 'Rebuild co-occurrence recommendations from completed order history (market basket cache)';

    public function handle(FrequentlyBoughtTogetherService $service): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $this->info('Refreshing frequently-bought-together co-occurrence cache...');

        ProductRecommendation::query()
            ->where('source', ProductRecommendation::SOURCE_CO_OCCURRENCE)
            ->delete();

        $pairs = $service->bulkCoOccurrencePairs();

        if ($pairs->isEmpty()) {
            $this->warn('No co-occurrence pairs found in order history.');

            return self::SUCCESS;
        }

        $grouped = $pairs->groupBy('product_id');
        $now = now();
        $rows = [];

        foreach ($grouped as $productId => $items) {
            $rank = 0;
            foreach ($items->sortByDesc('times_bought_together')->take($limit) as $item) {
                $rows[] = [
                    'product_id' => (int) $productId,
                    'related_product_id' => (int) $item->related_product_id,
                    'score' => (int) $item->times_bought_together,
                    'source' => ProductRecommendation::SOURCE_CO_OCCURRENCE,
                    'rank' => $rank++,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('product_recommendations')->insert($chunk);
        }

        $this->info('Cached ' . count($rows) . ' co-occurrence recommendations for ' . $grouped->count() . ' products.');

        return self::SUCCESS;
    }
}
