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
}
