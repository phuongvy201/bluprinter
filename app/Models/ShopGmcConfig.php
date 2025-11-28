<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopGmcConfig extends Model
{
    protected $fillable = [
        'shop_id',
        'name',
        'target_country',
        'merchant_id',
        'data_source_id',
        'credentials_path',
        'currency',
        'content_language',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCountry($query, string $countryCode)
    {
        return $query->where('target_country', strtoupper($countryCode));
    }
}
