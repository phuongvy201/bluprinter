<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'template_id',
        'product_id',
        'variant_name',
        'attributes',
        'sku',
        'price',
        'list_price',
        'quantity',
        'media'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'list_price' => 'decimal:2',
        'quantity' => 'integer',
        'media' => 'array',
        'attributes' => 'array',
    ];

    // Relationships
    public function template(): BelongsTo
    {
        return $this->belongsTo(ProductTemplate::class, 'template_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Helper methods
    public function getFinalPrice(): float
    {
        // Variant has its own price: apply active flash % on top of that pre-deal price.
        if ($this->price !== null) {
            $base = (float) $this->price;
            $multiplier = $this->product?->flashPriceMultiplier();

            if ($multiplier !== null) {
                return round($base * $multiplier, 2);
            }

            return $base;
        }

        // Fall back to product price (already flash-adjusted when a deal mutates products.price)
        if ($this->product) {
            return (float) $this->product->getEffectivePrice();
        }

        return (float) ($this->template->base_price ?? 0);
    }

    /**
     * Strikethrough / compare-at while flash is on = pre-deal variant (or product) price.
     */
    public function getFlashDisplayOriginalPrice(): float
    {
        if ($this->product?->flashPriceMultiplier() === null) {
            return $this->getCompareAtPrice();
        }

        if ($this->price !== null) {
            return (float) $this->price;
        }

        $dealOriginal = (float) ($this->product->activeFlashDeal?->original_price ?? 0);
        if ($dealOriginal > 0) {
            return $dealOriginal;
        }

        return $this->getCompareAtPrice();
    }

    public function getCompareAtPrice(): float
    {
        $list = (float) ($this->list_price ?? 0);
        if ($list > 0) {
            return $list;
        }

        return (float) ($this->product?->getCompareAtPrice() ?? 0);
    }
}
