<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'selected_variant',
        'customizations'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'selected_variant' => 'array',
        'customizations' => 'array'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // Helper methods
    public function getTotalPrice(): float
    {
        $total = $this->price * $this->quantity;

        // Add customization prices
        if ($this->customizations) {
            foreach ($this->customizations as $customization) {
                $total += ($customization['price'] ?? 0) * $this->quantity;
            }
        }

        return $total;
    }

    public function getDisplayName(): string
    {
        $name = $this->product->name;

        if ($this->selected_variant) {
            $attributes = [];
            foreach ($this->selected_variant as $key => $value) {
                $attributes[] = $value;
            }
            if (!empty($attributes)) {
                $name .= ' (' . implode(', ', $attributes) . ')';
            }
        }

        return $name;
    }
}
