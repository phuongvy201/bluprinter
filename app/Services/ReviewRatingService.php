<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Shop;

class ReviewRatingService
{
    public function syncShopRating(Shop $shop): void
    {
        $stats = Review::query()
            ->approved()
            ->whereHas('product', function ($query) use ($shop) {
                $query->where('shop_id', $shop->id);
            })
            ->selectRaw('COUNT(*) as total_count, AVG(rating) as average_rating')
            ->first();

        $total = (int) ($stats?->total_count ?? 0);
        $average = $total > 0 ? round((float) $stats->average_rating, 2) : 0;

        $shop->update([
            'total_ratings' => $total,
            'rating' => $average,
        ]);
    }

    public function syncShopForProduct(int $productId): void
    {
        $shopId = \App\Models\Product::query()->whereKey($productId)->value('shop_id');
        if (!$shopId) {
            return;
        }

        $shop = Shop::query()->find($shopId);
        if ($shop) {
            $this->syncShopRating($shop);
        }
    }
}
