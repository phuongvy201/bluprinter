<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Services\ShippingCalculator;
use App\Services\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();
        $userId = Auth::id();

        // Get cart items from database
        $cartItems = Cart::with(['product.shop', 'product.template', 'product.variants', 'variant'])
            ->where(function ($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->get();

        // Transform cart items to include media using getEffectiveMedia()
        $cartItems->each(function ($item) {
            if ($item->product) {
                $item->product->media = $item->product->getEffectiveMedia();
            }
        });

        // Calculate totals (without tax) including customizations
        $subtotal = $cartItems->sum(function ($item) {
            return $item->getTotalPriceWithCustomizations();
        });

        // Get current domain (needed for shipping zone filtering even if cart is empty)
        $currentDomain = CurrencyService::getCurrentDomain();
        
        // Calculate shipping using ShippingCalculator
        $shipping = 0;
        $shippingDetails = null;

        if (!$cartItems->isEmpty()) {
            // Get currency and rate for conversion
            $currency = CurrencyService::getCurrencyForDomain() ?? 'USD';
            $currencyRate = CurrencyService::getCurrencyRateForDomain();

            // If no rate from domain, use default rates
            if (!$currencyRate || $currencyRate == 1.0) {
                $defaultRates = [
                    'USD' => 1.0,
                    'GBP' => 0.79,
                    'EUR' => 0.92,
                    'CAD' => 1.35,
                    'AUD' => 1.52,
                    'JPY' => 150.0,
                    'CNY' => 7.2,
                    'HKD' => 7.8,
                    'SGD' => 1.34,
                ];
                $currencyRate = $defaultRates[$currency] ?? 1.0;
            }

            // Prepare cart items for shipping calculation
            // Shipping calculator expects USD prices, so we need to convert back to USD
            $items = $cartItems->map(function ($item) use ($currency, $currencyRate) {
                // Convert price back to USD for shipping calculation
                $priceInUSD = $currency !== 'USD' ? $item->price / $currencyRate : $item->price;
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $priceInUSD,
                ];
            });
            
            // Determine country from currency (same logic as products/show.blade.php)
            // Priority: currency -> domain name -> default to US
            $currencyToCountry = [
                'USD' => 'US',
                'GBP' => 'GB',
                'CAD' => 'CA',
                'MXN' => 'MX',
                'VND' => 'VN',
                'EUR' => 'DE'
            ];
            
            $shippingCountry = $currencyToCountry[$currency] ?? 'US';
            
            // If domain is available, try to get country from domain name
            if ($currentDomain) {
                $domainToCountry = [
                    'mx' => 'MX',
                    'mexico' => 'MX',
                    'us' => 'US',
                    'usa' => 'US',
                    'united-states' => 'US',
                    'gb' => 'GB',
                    'uk' => 'GB',
                    'united-kingdom' => 'GB',
                    'ca' => 'CA',
                    'canada' => 'CA',
                    'vn' => 'VN',
                    'vietnam' => 'VN',
                    'de' => 'DE',
                    'germany' => 'DE',
                    'eu' => 'DE',
                    'europe' => 'DE'
                ];
                
                $domainLower = strtolower($currentDomain);
                foreach ($domainToCountry as $domainKey => $countryCode) {
                    if (strpos($domainLower, $domainKey) !== false) {
                        $shippingCountry = $countryCode;
                        break;
                    }
                }
            }
            
            // Calculate shipping with domain parameter to prioritize default rate
            $calculator = new ShippingCalculator();
            $shippingResult = $calculator->calculateShipping($items, $shippingCountry, $currentDomain);

            if ($shippingResult['success']) {
                $shippingUSD = $shippingResult['total_shipping'];
                $shippingDetails = $shippingResult;

                // Convert shipping from USD to current currency
                $shipping = $currency !== 'USD'
                    ? CurrencyService::convertFromUSDWithRate($shippingUSD, $currency, $currencyRate)
                    : $shippingUSD;
            }
        }

        $total = $subtotal + $shipping;

        // Get current domain for shipping zone filtering (already retrieved above)
        // $currentDomain is already set from shipping calculation above

        // Get all active shipping zones for the delivery modal
        $shippingZones = ShippingZone::active()
            ->ordered()
            ->with(['activeShippingRates' => function ($query) {
                $query->ordered();
            }])
            ->get();

        // Filter zones by domain and get default zone
        $availableZones = $shippingZones;
        $defaultZone = null;

        if ($currentDomain && $availableZones->isNotEmpty()) {
            // Sort zones: domain matching zones first
            $availableZones = $availableZones->sortBy(function ($zone) use ($currentDomain) {
                return $zone->domain === $currentDomain ? 0 : 1;
            });

            // Find default zone for current domain
            $defaultZone = $availableZones->first(function ($zone) use ($currentDomain) {
                return $zone->domain === $currentDomain;
            });
        }

        // If no default zone found, use first available zone
        if (!$defaultZone && $availableZones->isNotEmpty()) {
            $defaultZone = $availableZones->first();
        }

        return view('cart.index', compact(
            'cartItems', 
            'subtotal', 
            'shipping', 
            'total', 
            'shippingDetails', 
            'shippingZones',
            'availableZones',
            'currentDomain',
            'defaultZone'
        ));
    }
}
