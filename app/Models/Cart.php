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
        // Price already includes variant and customization prices from frontend
        // So we just multiply by quantity
        return $this->price * $this->quantity;
    }

    public function getTotalPriceWithCustomizations(): float
    {
        $basePrice = $this->price * $this->quantity;
        $customizationTotal = 0;

        // Add customization prices if they exist
        if ($this->customizations && is_array($this->customizations)) {
            foreach ($this->customizations as $customization) {
                if (isset($customization['price']) && is_numeric($customization['price'])) {
                    $customizationTotal += floatval($customization['price']);
                }
            }
        }

        return $basePrice + ($customizationTotal * $this->quantity);
    }

    public function getUnitPriceWithCustomizations(): float
    {
        $basePrice = $this->price;
        $customizationTotal = 0;

        // Add customization prices if they exist
        if ($this->customizations && is_array($this->customizations)) {
            foreach ($this->customizations as $customization) {
                if (isset($customization['price']) && is_numeric($customization['price'])) {
                    $customizationTotal += floatval($customization['price']);
                }
            }
        }

        return $basePrice + $customizationTotal;
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
