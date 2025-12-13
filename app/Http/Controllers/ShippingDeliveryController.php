<?php

namespace App\Http\Controllers;

use App\Models\DomainShippingCost;
use App\Models\ShippingRate;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class ShippingDeliveryController extends Controller
{
    /**
     * Display the shipping & delivery page
     */
    public function index()
    {
        $domain = CurrencyService::getCurrentDomain();
        $currency = CurrencyService::getCurrencyForDomain($domain);
        $currencyRate = CurrencyService::getCurrencyRateForDomain($domain) ?? 1.0;

        // Get region from domain
        $region = DomainShippingCost::getRegionFromDomain($domain);

        // Get shipping costs for this domain and region
        $shippingCosts = DomainShippingCost::where('domain', $domain)
            ->where('region', $region)
            ->where('is_active', true)
            ->get();

        // If no costs found for this domain, try to get by region only
        if ($shippingCosts->isEmpty()) {
            $shippingCosts = DomainShippingCost::where('region', $region)
                ->where('is_active', true)
                ->get();
        }

        // If still no costs found, fallback to shipping_rates
        if ($shippingCosts->isEmpty()) {
            $shippingRates = ShippingRate::where(function ($query) use ($domain) {
                $query->where('domain', $domain)
                    ->orWhereNull('domain'); // Include general rates
            })
                ->where('is_active', true)
                ->whereNotNull('first_item_cost')
                ->whereNotNull('additional_item_cost')
                ->with(['category', 'shippingZone'])
                ->orderBy('sort_order')
                ->get();

            // Determine region from shipping zones if available
            if ($shippingRates->isNotEmpty()) {
                $firstRate = $shippingRates->first();
                if ($firstRate->shippingZone) {
                    $zone = $firstRate->shippingZone;
                    $countries = $zone->countries ?? [];
                    $zoneName = strtolower($zone->name ?? '');

                    // First try to get region from countries
                    $detectedRegion = $this->getRegionFromCountries($countries, null);

                    // If no region from countries, try to get from zone name
                    if (!$detectedRegion) {
                        $detectedRegion = $this->getRegionFromZoneName($zoneName, null);
                    }

                    // Use detected region if found, otherwise keep the default
                    if ($detectedRegion) {
                        $region = $detectedRegion;
                    }
                }
            }

            // Convert shipping rates to domain shipping costs format
            $shippingCosts = $shippingRates->map(function ($rate) {
                $productType = 'general';
                if ($rate->category) {
                    $productType = strtolower(str_replace(' ', '_', $rate->category->name));
                } elseif ($rate->name) {
                    $productType = strtolower(str_replace(' ', '_', $rate->name));
                }

                return (object) [
                    'product_type' => $productType,
                    'first_item_cost' => (float) $rate->first_item_cost,
                    'additional_item_cost' => (float) $rate->additional_item_cost,
                    'is_active' => $rate->is_active,
                ];
            });
        }

        // Format shipping costs with currency conversion
        $formattedCosts = $shippingCosts->map(function ($cost) use ($currency, $currencyRate, $domain) {
            $firstItemConverted = CurrencyService::convertFromUSDWithRate(
                $cost->first_item_cost,
                $currency,
                $currencyRate
            );
            $additionalItemConverted = CurrencyService::convertFromUSDWithRate(
                $cost->additional_item_cost,
                $currency,
                $currencyRate
            );

            return [
                'product_type' => $cost->product_type,
                'first_item' => CurrencyService::formatPrice($firstItemConverted, $currency, $domain),
                'additional_item' => CurrencyService::formatPrice($additionalItemConverted, $currency, $domain),
                'first_item_raw' => $firstItemConverted,
                'additional_item_raw' => $additionalItemConverted,
            ];
        });

        // Region display names
        $regionNames = [
            'US' => 'United States',
            'UK' => 'United Kingdom',
            'CA' => 'Canada',
            'MX' => 'Mexico',
        ];

        $regionName = $regionNames[$region] ?? $region;

        return view('shipping-delivery.index', compact(
            'domain',
            'currency',
            'region',
            'regionName',
            'formattedCosts',
            'shippingCosts'
        ));
    }

    /**
     * Get region from countries array
     */
    private function getRegionFromCountries(array $countries, ?string $defaultRegion = null): ?string
    {
        $countryToRegion = [
            'US' => 'US',
            'USA' => 'US',
            'GB' => 'UK',
            'GBR' => 'UK',
            'UK' => 'UK',
            'CA' => 'CA',
            'CAN' => 'CA',
            'MX' => 'MX',
            'MEX' => 'MX',
        ];

        foreach ($countries as $country) {
            $countryUpper = strtoupper($country);
            if (isset($countryToRegion[$countryUpper])) {
                return $countryToRegion[$countryUpper];
            }
        }

        return $defaultRegion;
    }

    /**
     * Get region from zone name
     */
    private function getRegionFromZoneName(string $zoneName, ?string $defaultRegion = null): ?string
    {
        $zoneNameLower = strtolower($zoneName);

        // Check for UK
        if (
            strpos($zoneNameLower, 'united kingdom') !== false ||
            strpos($zoneNameLower, 'uk') !== false ||
            strpos($zoneNameLower, 'britain') !== false
        ) {
            return 'UK';
        }

        // Check for Canada
        if (
            strpos($zoneNameLower, 'canada') !== false ||
            strpos($zoneNameLower, 'ca') !== false
        ) {
            return 'CA';
        }

        // Check for Mexico
        if (
            strpos($zoneNameLower, 'mexico') !== false ||
            strpos($zoneNameLower, 'mx') !== false
        ) {
            return 'MX';
        }

        // Check for US (should be last to avoid false positives)
        if (
            strpos($zoneNameLower, 'united states') !== false ||
            strpos($zoneNameLower, 'usa') !== false ||
            strpos($zoneNameLower, 'us') !== false
        ) {
            return 'US';
        }

        return $defaultRegion;
    }
}
