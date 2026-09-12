<?php

namespace App\Services\Concerns;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

trait ResolvesRelatedProductIds
{
    protected function tieredCatalogFallbackIds(Product $product, int $limit, array $excludeIds = []): Collection
    {
        if ($limit <= 0) {
            return collect();
        }

        $excludeIds[] = $product->id;
        $categoryId = $product->resolvedCategoryId();
        $shopId = $product->shop_id;
        $ids = collect();

        $tiers = [];

        if ($shopId && $categoryId) {
            $tiers[] = function (array $exclude) use ($shopId, $categoryId): Builder {
                return $this->catalogFallbackQuery($exclude)
                    ->where('shop_id', $shopId)
                    ->inCategoryIds([$categoryId]);
            };
        }

        if ($categoryId) {
            $tiers[] = function (array $exclude) use ($categoryId): Builder {
                return $this->catalogFallbackQuery($exclude)
                    ->inCategoryIds([$categoryId]);
            };
        }

        if ($shopId) {
            $tiers[] = function (array $exclude) use ($shopId): Builder {
                return $this->catalogFallbackQuery($exclude)->where('shop_id', $shopId);
            };
        }

        $tiers[] = fn (array $exclude) => $this->catalogFallbackQuery($exclude);

        foreach ($tiers as $tier) {
            if ($ids->count() >= $limit) {
                break;
            }

            $need = $limit - $ids->count();
            $exclude = array_values(array_unique(array_merge($excludeIds, $ids->all())));

            $found = $tier($exclude)
                ->limit($need)
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            $ids = $ids->merge($found)->unique()->values();
            $excludeIds = array_merge($excludeIds, $found->all());
        }

        return $ids->take($limit)->values();
    }

    protected function catalogFallbackQuery(array $excludeIds): Builder
    {
        return Product::query()
            ->availableForDisplay()
            ->whereNotIn('id', $excludeIds)
            ->withSum('orderItems as order_items_sum_quantity', 'quantity')
            ->orderByDesc('order_items_sum_quantity')
            ->orderByDesc('created_at');
    }

    protected function rememberRelatedProductIds(string $cacheKey, \DateTimeInterface $ttl, callable $resolver): Collection
    {
        $cached = cache()->get($cacheKey);
        if (is_array($cached) && count($cached) > 0) {
            return collect($cached)->map(fn ($id) => (int) $id)->values();
        }

        $ids = $resolver();

        if ($ids->isNotEmpty()) {
            cache()->put($cacheKey, $ids->all(), $ttl);
        } else {
            cache()->forget($cacheKey);
        }

        return $ids;
    }

    protected function loadProductsByIds(Collection $ids): Collection
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        $products = Product::query()
            ->whereIn('id', $ids)
            ->availableForDisplay()
            ->with(['shop', 'template'])
            ->withCount('variants')
            ->get();

        return $ids
            ->map(fn ($id) => $products->firstWhere('id', $id))
            ->filter()
            ->values();
    }
}
