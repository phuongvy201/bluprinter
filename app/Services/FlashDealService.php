<?php

namespace App\Services;

use App\Models\FlashDeal;
use App\Models\Product;
use App\Support\Settings;
use Illuminate\Support\Collection;

class FlashDealService
{
    public function isEnabled(): bool
    {
        return (bool) Settings::get('flash_deal.enabled', config('flash_deal.enabled', true));
    }

    public function productLimit(): int
    {
        return (int) Settings::get('flash_deal.product_limit', config('flash_deal.product_limit', 8));
    }

    public function minDiscountPercent(): int
    {
        return (int) Settings::get('flash_deal.min_discount_percent', config('flash_deal.min_discount_percent', 5));
    }

    /**
     * Active flash deals for homepage carousel.
     */
    public function getHomepageDeals(): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        $limit = $this->productLimit();
        $minDiscount = $this->minDiscountPercent();

        $deals = FlashDeal::query()
            ->with(['product.shop', 'product.template'])
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->filter(function (FlashDeal $deal) {
                $product = $deal->product;

                return $product && $product->isAvailableForDisplay();
            })
            ->filter(fn (FlashDeal $deal) => $deal->discount_percent >= $minDiscount)
            ->take($limit);

        if ($deals->isNotEmpty()) {
            return $deals;
        }

        return $this->fallbackHeuristicDeals($limit, $minDiscount);
    }

    /**
     * Fallback when no scheduled deals exist yet.
     */
    public function fallbackHeuristicDeals(int $limit, int $minDiscount): Collection
    {
        $products = Product::with(['shop', 'template'])
            ->availableForDisplay()
            ->join('product_templates', 'products.template_id', '=', 'product_templates.id')
            ->whereColumn('product_templates.base_price', '>', 'products.price')
            ->select('products.*')
            ->orderByRaw('(product_templates.base_price - products.price) / NULLIF(product_templates.base_price, 0) DESC')
            ->limit($limit)
            ->get();

        return $products->map(function (Product $product) use ($minDiscount) {
            $original = $this->referencePrice($product);
            $sale = (float) $product->getEffectivePrice();
            $pct = $original > 0 && $sale < $original
                ? (int) round((($original - $sale) / $original) * 100)
                : 0;

            if ($pct < $minDiscount) {
                return null;
            }

            $deal = new FlashDeal([
                'sale_price' => $sale,
                'original_price' => $original,
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->endOfDay(),
                'source' => 'heuristic',
                'is_active' => true,
            ]);
            $deal->setRelation('product', $product);

            return $deal;
        })->filter()->values();
    }

    public function referencePrice(Product $product): float
    {
        $templateBase = (float) ($product->getCompareAtPrice());
        $current = (float) $product->getEffectivePrice();

        return max($templateBase, $current);
    }

    public function maxDiscountFromDeals(Collection $deals): int
    {
        return $deals->reduce(fn (int $max, FlashDeal $deal) => max($max, $deal->discount_percent), 0);
    }

    public function earliestEndsAt(Collection $deals): \Carbon\Carbon
    {
        $ends = $deals->min(fn (FlashDeal $deal) => $deal->ends_at);

        return $ends ?? now()->endOfDay();
    }

    public function expireDeal(FlashDeal $deal): void
    {
        if (! $deal->is_active) {
            return;
        }

        $product = $deal->product;

        if ($product) {
            $product->update(['price' => $deal->original_price]);
        }

        $deal->update([
            'is_active' => false,
            'expired_at' => now(),
        ]);
    }

    public function activateDeal(
        Product $product,
        float $salePrice,
        \Carbon\Carbon $startsAt,
        \Carbon\Carbon $endsAt,
        string $source,
        ?int $sourceId = null,
        int $sortOrder = 0,
    ): ?FlashDeal {
        $originalPrice = (float) $product->getEffectivePrice();

        if ($originalPrice <= 0) {
            $originalPrice = (float) ($product->template->base_price ?? 0);
        }

        if ($originalPrice <= 0) {
            return null;
        }

        $salePrice = $this->applySellerConstraints($product, $originalPrice, $salePrice);

        if ($salePrice >= $originalPrice) {
            return null;
        }

        $minDiscount = $this->minDiscountPercent();
        $discountPct = (($originalPrice - $salePrice) / $originalPrice) * 100;

        if ($discountPct < $minDiscount) {
            return null;
        }

        FlashDeal::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->each(fn (FlashDeal $existing) => $this->expireDeal($existing));

        $deal = FlashDeal::create([
            'product_id' => $product->id,
            'shop_id' => $product->shop_id,
            'sale_price' => $salePrice,
            'original_price' => $originalPrice,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'source' => $source,
            'source_id' => $sourceId,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);

        $product->update(['price' => $salePrice]);

        return $deal;
    }

    public function applySellerConstraints(Product $product, float $originalPrice, float $salePrice): float
    {
        if ($product->flash_deal_min_price !== null) {
            $salePrice = max($salePrice, (float) $product->flash_deal_min_price);
        }

        $shop = $product->shop;

        if ($shop && $shop->flash_deal_max_discount_percent !== null) {
            $maxPct = (int) $shop->flash_deal_max_discount_percent;
            $floor = $originalPrice * (1 - $maxPct / 100);
            $salePrice = max($salePrice, $floor);
        }

        return round($salePrice, 2);
    }

    public function salePriceFromDiscount(float $originalPrice, int $discountPercent): float
    {
        return round($originalPrice * (1 - $discountPercent / 100), 2);
    }
}
