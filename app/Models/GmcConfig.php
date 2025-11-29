<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GmcConfig extends Model
{
    protected $fillable = [
        'domain',
        'target_country',
        'name',
        'merchant_id',
        'data_source_id',
        'credentials_path',
        'currency',
        'currency_rate',
        'content_language',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'currency_rate' => 'decimal:6',
    ];

    // Helper Methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get currency mapping for common countries
     */
    public static function getCurrencyForCountry(string $countryCode): string
    {
        $mapping = [
            'US' => 'USD',
            'GB' => 'GBP',
            'VN' => 'VND',
            'CA' => 'CAD',
            'AU' => 'AUD',
            'DE' => 'EUR',
            'FR' => 'EUR',
            'IT' => 'EUR',
            'ES' => 'EUR',
        ];

        return $mapping[strtoupper($countryCode)] ?? 'USD';
    }

    /**
     * Get content language mapping for common countries
     */
    public static function getLanguageForCountry(string $countryCode): string
    {
        $mapping = [
            'US' => 'en',
            'GB' => 'en',
            'VN' => 'vi',
            'CA' => 'en',
            'AU' => 'en',
            'DE' => 'de',
            'FR' => 'fr',
            'IT' => 'it',
            'ES' => 'es',
        ];

        return $mapping[strtoupper($countryCode)] ?? 'en';
    }

    /**
     * Get config for domain and country
     */
    public static function getConfigForDomainAndCountry(string $domain, string $countryCode): ?self
    {
        return self::where('domain', $domain)
            ->where('target_country', strtoupper($countryCode))
            ->where('is_active', true)
            ->first();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }

    public function scopeForCountry($query, string $countryCode)
    {
        return $query->where('target_country', strtoupper($countryCode));
    }

    /**
     * Lấy currency cho domain (lấy config active đầu tiên)
     */
    public static function getCurrencyForDomain(string $domain): ?string
    {
        $config = self::where('domain', $domain)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->first();

        return $config?->currency;
    }

    /**
     * Lấy currency rate cho domain
     */
    public static function getCurrencyRateForDomain(string $domain): ?float
    {
        $config = self::where('domain', $domain)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->first();

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
