<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_zone_id',
        'category_id',
        'name',
        'description',
        'first_item_cost',
        'additional_item_cost',
        'min_items',
        'max_items',
        'min_order_value',
        'max_order_value',
        'max_weight',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'first_item_cost' => 'decimal:2',
        'additional_item_cost' => 'decimal:2',
        'min_items' => 'integer',
        'max_items' => 'integer',
        'min_order_value' => 'decimal:2',
        'max_order_value' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the shipping zone for this rate
     */
    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * Get the category for this rate (if applicable)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculate shipping cost for given number of items
     * 
     * @param int $itemCount Number of items in order
     * @return float Total shipping cost
     */
    public function calculateCost(int $itemCount): float
    {
        if ($itemCount <= 0) {
            return 0;
        }

        // First item cost already includes all fees (shipping + label)
        $cost = $this->first_item_cost;

        // Additional items only pay additional shipping cost
        if ($itemCount > 1) {
            $cost += ($itemCount - 1) * $this->additional_item_cost;
        }

        return $cost;
    }

    /**
     * Check if this rate is applicable for the given conditions
     * 
     * @param int $itemCount
     * @param float $orderValue
     * @return bool
     */
    public function isApplicable(int $itemCount, float $orderValue = 0): bool
    {
        // Check if active
        if (!$this->is_active) {
            return false;
        }

        // Check item count constraints
        if ($this->min_items !== null && $itemCount < $this->min_items) {
            return false;
        }

        if ($this->max_items !== null && $itemCount > $this->max_items) {
            return false;
        }

        // Check order value constraints
        if ($this->min_order_value !== null && $orderValue < $this->min_order_value) {
            return false;
        }

        if ($this->max_order_value !== null && $orderValue > $this->max_order_value) {
            return false;
        }

        return true;
    }

    /**
     * Scope query to only include active rates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to filter by shipping zone
     */
    public function scopeForZone($query, int $zoneId)
    {
        return $query->where('shipping_zone_id', $zoneId);
    }

    /**
     * Scope query to filter by category
     */
    public function scopeForCategory($query, ?int $categoryId)
    {
        return $query->where(function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
                ->orWhereNull('category_id'); // Include general rates
        });
    }

    /**
     * Scope query to order by priority
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('first_item_cost');
    }
}
