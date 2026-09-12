<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_description',
        'unit_price',
        'quantity',
        'total_price',
        'product_options',
        'shipping_cost',
        'is_first_item',
        'shipping_notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'product_options' => 'array',
        'shipping_cost' => 'decimal:2',
        'is_first_item' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function resolveDisplayImage(): string
    {
        $options = is_array($this->product_options) ? $this->product_options : [];
        $customs = $options['customizations'] ?? [];
        if (! is_array($customs)) {
            $customs = [];
        }
        $studio = $customs['_studio']['image'] ?? $customs['Custom design']['image'] ?? '';
        if (is_string($studio) && $studio !== '') {
            return $studio;
        }

        $media = $this->product?->getEffectiveMedia();
        if (is_array($media) && $media !== []) {
            $first = $media[0];
            if (is_string($first) && $first !== '') {
                return $first;
            }
            if (is_array($first)) {
                return (string) ($first['url'] ?? $first['path'] ?? '');
            }
        }

        return '';
    }

    /**
     * @return array<string, mixed>
     */
    public function visibleCustomizations(): array
    {
        $options = is_array($this->product_options) ? $this->product_options : [];
        $customs = $options['customizations'] ?? [];
        if (! is_array($customs)) {
            return [];
        }

        return collect($customs)
            ->reject(fn ($value, $key) => str_starts_with((string) $key, '_'))
            ->all();
    }

    // Accessor for backward compatibility
    public function getPriceAttribute()
    {
        return $this->unit_price;
    }
}
