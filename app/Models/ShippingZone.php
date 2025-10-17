<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = [
        'name',
        'countries',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'countries' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all shipping rates for this zone
     */
    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Get active shipping rates for this zone
     */
    public function activeShippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class)->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Check if a country is in this zone
     */
    public function hasCountry(string $countryCode): bool
    {
        $countries = $this->countries ?? [];
        return in_array(strtoupper($countryCode), array_map('strtoupper', $countries));
    }

    /**
     * Get shipping zone by country code
     */
    public static function findByCountry(string $countryCode): ?self
    {
        return self::where('is_active', true)
            ->get()
            ->first(function ($zone) use ($countryCode) {
                return $zone->hasCountry($countryCode);
            });
    }

    /**
     * Scope query to only include active zones
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
