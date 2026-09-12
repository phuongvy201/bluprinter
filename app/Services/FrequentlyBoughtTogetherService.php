<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductRecommendation;
use App\Services\Concerns\ResolvesRelatedProductIds;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FrequentlyBoughtTogetherService
{
    use ResolvesRelatedProductIds;

    public function getProducts(Product $product, int $limit = 32): Collection
    {
        $cacheKey = 'fbt_products:' . $product->id . ':' . $limit;

        $ids = $this->rememberRelatedProductIds($cacheKey, now()->addHours(12), function () use ($product, $limit) {
            return $this->resolveProductIds($product, $limit);
        });

        return $this->loadProductsByIds($ids);
    }

    /**
     * Record sequential product views within the same browsing session (view-together signal).
     */
    public function recordCoView(int $previousProductId, int $currentProductId): void
    {
        if ($previousProductId <= 0 || $currentProductId <= 0 || $previousProductId === $currentProductId) {
            return;
        }

        if (! $this->coViewsTableExists()) {
            return;
        }

        $now = now();

        DB::table('product_co_views')->upsert(
            [
                [
                    'product_id' => $previousProductId,
                    'related_product_id' => $currentProductId,
                    'view_count' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'product_id' => $currentProductId,
                    'related_product_id' => $previousProductId,
                    'view_count' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['product_id', 'related_product_id'],
            [
                'view_count' => DB::raw('view_count + 1'),
                'updated_at' => $now,
            ]
        );
    }

    /**
     * All product pairs bought together in completed orders (for nightly cron).
     */
    public function bulkCoOccurrencePairs(): Collection
    {
        return DB::table('order_items as oi1')
            ->join('order_items as oi2', function ($join) {
                $join->on('oi1.order_id', '=', 'oi2.order_id')
                    ->whereColumn('oi1.product_id', '!=', 'oi2.product_id');
            })
            ->join('orders', 'orders.id', '=', 'oi1.order_id')
            ->join('products', 'products.id', '=', 'oi2.product_id')
            ->where('products.status', 'active')
            ->where('orders.payment_status', 'paid')
            ->whereNotIn('orders.status', ['cancelled', 'pending'])
            ->groupBy('oi1.product_id', 'oi2.product_id')
            ->selectRaw('oi1.product_id as product_id, oi2.product_id as related_product_id, COUNT(DISTINCT oi1.order_id) as times_bought_together')
            ->get();
    }

    protected function resolveProductIds(Product $product, int $limit): Collection
    {
        $exclude = [$product->id];
        $ids = $this->coOccurrenceProductIds($product->id, $limit);

        if ($ids->count() < $limit) {
            $ids = $ids->merge(
                $this->tieredCatalogFallbackIds($product, $limit - $ids->count(), array_merge($exclude, $ids->all()))
            )->unique()->values();
        }

        if ($ids->count() < $limit) {
            $ids = $ids->merge(
                $this->coViewFallbackIds($product->id, $limit - $ids->count(), array_merge($exclude, $ids->all()))
            )->unique()->values();
        }

        return $ids->take($limit)->values();
    }

    protected function coOccurrenceProductIds(int $productId, int $limit): Collection
    {
        $cached = ProductRecommendation::query()
            ->where('product_id', $productId)
            ->where('source', ProductRecommendation::SOURCE_CO_OCCURRENCE)
            ->orderBy('rank')
            ->limit($limit)
            ->pluck('related_product_id');

        if ($cached->isNotEmpty()) {
            return $cached->map(fn ($id) => (int) $id)->values();
        }

        return $this->liveCoOccurrenceProductIds($productId, $limit);
    }

    protected function liveCoOccurrenceProductIds(int $productId, int $limit): Collection
    {
        return DB::table('order_items as oi1')
            ->join('order_items as oi2', function ($join) use ($productId) {
                $join->on('oi1.order_id', '=', 'oi2.order_id')
                    ->where('oi2.product_id', '!=', $productId);
            })
            ->join('orders', 'orders.id', '=', 'oi1.order_id')
            ->join('products', 'products.id', '=', 'oi2.product_id')
            ->where('oi1.product_id', $productId)
            ->where('products.status', 'active')
            ->where('orders.payment_status', 'paid')
            ->whereNotIn('orders.status', ['cancelled', 'pending'])
            ->groupBy('oi2.product_id')
            ->orderByDesc('times_bought_together')
            ->limit($limit)
            ->selectRaw('oi2.product_id, COUNT(DISTINCT oi1.order_id) as times_bought_together')
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    protected function coViewFallbackIds(int $productId, int $limit, array $excludeIds = []): Collection
    {
        if ($limit <= 0 || ! $this->coViewsTableExists()) {
            return collect();
        }

        $excludeIds[] = $productId;

        return DB::table('product_co_views')
            ->join('products', 'products.id', '=', 'product_co_views.related_product_id')
            ->where('product_co_views.product_id', $productId)
            ->where('products.status', 'active')
            ->whereNotIn('product_co_views.related_product_id', $excludeIds)
            ->orderByDesc('product_co_views.view_count')
            ->limit($limit)
            ->pluck('product_co_views.related_product_id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    protected function coViewsTableExists(): bool
    {
        static $exists = null;

        if ($exists === null) {
            $exists = DB::getSchemaBuilder()->hasTable('product_co_views');
        }

        return $exists;
    }
}
