<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DomainCurrencyConfig;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_zone_id',
        'domain',
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
        'is_default',
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
        'is_default' => 'boolean',
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
     * Scope query to filter by domain
     */
    public function scopeForDomain($query, ?string $domain)
    {
        if (!$domain) {
            return $query;
        }
        return $query->where('domain', $domain);
    }

    /**
     * Scope query to order by priority
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('is_default', 'desc')->orderBy('sort_order')->orderBy('first_item_cost');
    }

    /**
     * Scope query to only include default rates
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get default shipping rate for a domain
     * 
     * @param string|null $domain Domain name
     * @param int|null $zoneId Optional zone ID to filter by
     * @param int|null $categoryId Optional category ID to filter by
     * @return self|null Default rate for the domain, or null if not found
     */
    public static function getDefaultRateForDomain(?string $domain, ?int $zoneId = null, ?int $categoryId = null): ?self
    {
        if (!$domain) {
            return null;
        }

        $query = self::where('is_active', true)
            ->where('domain', $domain);

        if ($zoneId) {
            $query->where('shipping_zone_id', $zoneId);
        }

        if ($categoryId !== null) {
            $query->forCategory($categoryId);
        }

        // First, try to get the rate marked as default for this domain
        $defaultRate = (clone $query)
            ->where('is_default', true)
            ->ordered()
            ->first();

        if ($defaultRate) {
            return $defaultRate;
        }

        // If no default rate found, return the first active rate for this domain
        return $query->ordered()->first();
    }

    /**
     * Get default shipping rate for a domain using DomainCurrencyConfig
     * This method uses DomainCurrencyConfig to get the domain and then finds the default rate
     * 
     * @param string|null $domain Domain name (will be used to get currency config)
     * @param int|null $zoneId Optional zone ID to filter by
     * @param int|null $categoryId Optional category ID to filter by
     * @return self|null Default rate for the domain
     */
    public static function getDefaultRateForDomainFromConfig(?string $domain, ?int $zoneId = null, ?int $categoryId = null): ?self
    {
        if (!$domain) {
            return null;
        }

        // Get currency config for domain to verify domain exists
        $currencyConfig = DomainCurrencyConfig::getForDomain($domain);

        if (!$currencyConfig) {
            return null;
        }

        // Use the domain from config to get default rate
        return self::getDefaultRateForDomain($domain, $zoneId, $categoryId);
    }

    /**
     * Get all shipping rates for a domain, with default rates first
     * 
     * @param string|null $domain Domain name
     * @param int|null $zoneId Optional zone ID to filter by
     * @param int|null $categoryId Optional category ID to filter by
     * @return \Illuminate\Support\Collection Collection of ShippingRate models
     */
    public static function getRatesForDomain(?string $domain, ?int $zoneId = null, ?int $categoryId = null): \Illuminate\Support\Collection
    {
        if (!$domain) {
            return collect();
        }

        $query = self::where('is_active', true)
            ->where('domain', $domain);

        if ($zoneId) {
            $query->where('shipping_zone_id', $zoneId);
        }

        if ($categoryId !== null) {
            $query->forCategory($categoryId);
        }

        $rates = $query->ordered()->get();

        // Sort to put default rates first
        return $rates->sortBy(function ($rate) {
            return $rate->is_default ? 0 : 1;
        })->values();
    }

    /**
     * Set this rate as default for its domain
     * This will unset other default rates for the same domain, zone, and category
     * 
     * @return bool
     */
    public function setAsDefault(): bool
    {
        if (!$this->domain) {
            return false;
        }

        // Unset other default rates for the same domain, zone, and category
        $query = self::where('domain', $this->domain)
            ->where('id', '!=', $this->id);

        if ($this->shipping_zone_id) {
            $query->where('shipping_zone_id', $this->shipping_zone_id);
        }

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        } else {
            $query->whereNull('category_id');
        }

        $query->update(['is_default' => false]);

        // Set this rate as default
        $this->is_default = true;
        return $this->save();
    }

    /**
     * Unset this rate as default
     * 
     * @return bool
     */
    public function unsetAsDefault(): bool
    {
        $this->is_default = false;
        return $this->save();
    }

    /**
     * Get shipping zones that have rates for a specific category
     * 
     * @param int|null $categoryId Category ID (null returns empty collection)
     * @param string|null $domain Optional domain to prioritize zones for this domain
     * @return \Illuminate\Support\Collection Collection of ShippingZone models
     */
    public static function getZonesForCategory(?int $categoryId, ?string $domain = null): \Illuminate\Support\Collection
    {
        // If no category ID provided, return empty collection
        if ($categoryId === null) {
            return collect();
        }

        // Get distinct zone IDs that have active rates for this specific category
        // PRIORITY: If domain is provided, prioritize rates matching that domain
        $query = self::active()->where('category_id', $categoryId);

        if ($domain) {
            // First, try to get zones with rates matching the domain
            $zoneIdsWithDomain = (clone $query)
                ->forDomain($domain)
                ->distinct()
                ->pluck('shipping_zone_id');

            if ($zoneIdsWithDomain->isNotEmpty()) {
                $zoneIds = $zoneIdsWithDomain;
            } else {
                // Fallback to all zones for this category
                $zoneIds = $query->distinct()->pluck('shipping_zone_id');
            }
        } else {
            $zoneIds = $query->distinct()->pluck('shipping_zone_id');
        }

        // If no zones found for this category, return empty collection
        if ($zoneIds->isEmpty()) {
            return collect();
        }

        // Get the zones that are active
        $zones = \App\Models\ShippingZone::whereIn('id', $zoneIds)
            ->active()
            ->ordered()
            ->get();

        // PRIORITY: Sort zones to put domain's zones first if domain is provided
        if ($domain && $zones->isNotEmpty()) {
            $zones = $zones->sortBy(function ($zone) use ($domain) {
                return $zone->domain === $domain ? 0 : 1;
            })->values();
        }

        return $zones;
    }
}
