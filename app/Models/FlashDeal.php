<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FlashDeal extends Model
{
    protected $fillable = [
        'product_id',
        'shop_id',
        'sale_price',
        'original_price',
        'starts_at',
        'ends_at',
        'source',
        'source_id',
        'sort_order',
        'is_active',
        'expired_at',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'expired_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());
    }

    public function getDiscountPercentAttribute(): int
    {
        $original = (float) $this->original_price;
        $sale = (float) $this->sale_price;

        if ($original <= 0 || $sale >= $original) {
            return 0;
        }

        return (int) round((($original - $sale) / $original) * 100);
    }
}
