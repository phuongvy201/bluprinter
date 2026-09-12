<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\Product;
use App\Services\Concerns\ResolvesRelatedProductIds;
use Illuminate\Support\Collection as SupportCollection;

class CollectionRelatedProductsService
{
    use ResolvesRelatedProductIds;

    public function getProducts(Product $product, int $limit = 32): SupportCollection
    {
        $cacheKey = 'collection_related:' . $product->id . ':' . $limit;

        $ids = $this->rememberRelatedProductIds($cacheKey, now()->addHours(6), function () use ($product, $limit) {
            return $this->resolveProductIds($product, $limit);
        });

        return $this->loadProductsByIds($ids);
    }

    protected function resolveProductIds(Product $product, int $limit): SupportCollection
    {
        $collectionIds = Collection::query()
            ->active()
            ->approved()
            ->whereHas('products', fn ($q) => $q->where('products.id', $product->id))
            ->pluck('id');

        $ids = collect();

        if ($collectionIds->isNotEmpty()) {
            $ids = Product::query()
                ->availableForDisplay()
                ->where('products.id', '!=', $product->id)
                ->whereHas('collections', function ($query) use ($collectionIds) {
                    $query->whereIn('collections.id', $collectionIds)
                        ->where('collections.status', 'active');
                })
                ->withSum('orderItems as order_items_sum_quantity', 'quantity')
                ->orderByDesc('order_items_sum_quantity')
                ->orderByDesc('products.created_at')
                ->limit($limit)
                ->pluck('products.id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        if ($ids->count() < $limit) {
            $ids = $ids->merge(
                $this->tieredCatalogFallbackIds(
                    $product,
                    $limit - $ids->count(),
                    array_merge([$product->id], $ids->all())
                )
            )->unique()->values();
        }

        return $ids->take($limit)->values();
    }
}
