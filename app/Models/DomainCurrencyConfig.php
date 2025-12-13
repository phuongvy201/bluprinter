<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainCurrencyConfig extends Model
{
    protected $fillable = [
        'domain',
        'currency',
        'currency_rate',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'currency_rate' => 'decimal:6',
    ];

    /**
     * Lấy currency config cho domain
     */
    public static function getForDomain(?string $domain): ?self
    {
        if (!$domain) {
            return null;
        }

        return self::where('domain', $domain)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Lấy currency cho domain
     */
    public static function getCurrencyForDomain(?string $domain): ?string
    {
        $config = self::getForDomain($domain);
        return $config?->currency;
    }

    /**
     * Lấy currency rate cho domain
     */
    public static function getCurrencyRateForDomain(?string $domain): ?float
    {
        $config = self::getForDomain($domain);

        if ($config) {
            if ($config->currency_rate) {
                return (float) $config->currency_rate;
            }
            if ($config->currency === 'USD') {
                return 1.0;
            }
        }

        return null;
    }

    /**
     * Lấy default shipping rate cho domain
     * Sử dụng domain từ config để lấy default shipping rate
     * 
     * @param string|null $domain Domain name
     * @param int|null $zoneId Optional zone ID to filter by
     * @param int|null $categoryId Optional category ID to filter by
     * @return \App\Models\ShippingRate|null Default shipping rate for the domain
     */
    public static function getDefaultShippingRateForDomain(?string $domain, ?int $zoneId = null, ?int $categoryId = null): ?\App\Models\ShippingRate
    {
        if (!$domain) {
            return null;
        }

        // Verify domain exists in config
        $config = self::getForDomain($domain);
        if (!$config) {
            return null;
        }

        // Get default shipping rate for this domain
        return \App\Models\ShippingRate::getDefaultRateForDomain($domain, $zoneId, $categoryId);
    }
}
