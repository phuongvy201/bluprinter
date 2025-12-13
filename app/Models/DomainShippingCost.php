<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainShippingCost extends Model
{
    protected $fillable = [
        'domain',
        'region',
        'product_type',
        'first_item_cost',
        'additional_item_cost',
        'is_active',
    ];

    protected $casts = [
        'first_item_cost' => 'decimal:2',
        'additional_item_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get region from domain name
     * Maps domain to region code (US, UK, CA, MX)
     * 
     * @param string|null $domain Domain name
     * @return string Region code (US, UK, CA, MX) or 'US' as default
     */
    public static function getRegionFromDomain(?string $domain): string
    {
        if (!$domain) {
            return 'US';
        }

        $domainLower = strtolower($domain);

        // Map domain to region
        $domainToRegion = [
            'mx' => 'MX',
            'mexico' => 'MX',
            'us' => 'US',
            'usa' => 'US',
            'united-states' => 'US',
            'gb' => 'UK',
            'uk' => 'UK',
            'united-kingdom' => 'UK',
            'ca' => 'CA',
            'canada' => 'CA',
            'vn' => 'US', // Vietnam defaults to US region
            'vietnam' => 'US',
            'de' => 'UK', // Germany defaults to UK region
            'germany' => 'UK',
            'eu' => 'UK',
            'europe' => 'UK',
        ];

        // Check if domain matches any key
        foreach ($domainToRegion as $key => $region) {
            if (strpos($domainLower, $key) !== false) {
                return $region;
            }
        }

        // Default to US if no match
        return 'US';
    }
}
