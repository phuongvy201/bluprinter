@extends('layouts.app')

@section('title', $product->name)

@section('content')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

@php
    $displayCategory = $product->resolveDisplayCategory();
    $primaryCategory = $displayCategory['name'] ?? null;
    $displayTitle = $product->getDisplayTitle();
    $activeFlashDeal = $product->activeFlashDeal;
    $isFlashSale = $activeFlashDeal !== null;
    $flashDiscountPct = $isFlashSale ? (int) $product->flashDiscountPercent() : 0;
    $flashOriginalPriceUsd = $isFlashSale ? (float) $activeFlashDeal->original_price : null;
    $flashEndsAt = $isFlashSale ? $activeFlashDeal->ends_at : null;
    $currentCurrency = currency();
    $currencySymbol = currency_symbol();
    $currentCurrencyRate = currency_rate() ?? 1.0;
    $productPriceUSD = (float) $product->getEffectivePrice();

    // PDP luôn chọn variant đầu → giá flash phải theo variant (không lấy products.price đã giảm một mình)
    if ($isFlashSale && $product->relationLoaded('variants') && $product->variants->isNotEmpty()) {
        $firstVariant = $product->variants->first();
        $productPriceUSD = (float) $firstVariant->getFinalPrice();
        $flashOriginalPriceUsd = (float) $firstVariant->getFlashDisplayOriginalPrice();
    } elseif ($isFlashSale && ! $product->relationLoaded('variants')) {
        $firstVariant = $product->variants()->first();
        if ($firstVariant) {
            $firstVariant->setRelation('product', $product);
            $productPriceUSD = (float) $firstVariant->getFinalPrice();
            $flashOriginalPriceUsd = (float) $firstVariant->getFlashDisplayOriginalPrice();
        }
    }

    $productPriceConverted = convert_currency($productPriceUSD);
    $productListPriceUSD = (float) $product->getCompareAtPrice();
    $productListPriceConverted = convert_currency($productListPriceUSD);
    $hasListDiscount = ! $isFlashSale && $productListPriceUSD > $productPriceUSD;
    $listDiscountPct = $hasListDiscount
        ? (int) round((($productListPriceUSD - $productPriceUSD) / $productListPriceUSD) * 100)
        : 0;
    $tryOnMedia = $product->tryOnImages();
    $flashSavingsUsd = ($isFlashSale && $flashOriginalPriceUsd && $flashOriginalPriceUsd > $productPriceUSD)
        ? $flashOriginalPriceUsd - $productPriceUSD
        : null;
    
    // Get current domain
    $currentDomain = \App\Services\CurrencyService::getCurrentDomain();
    
    // Get all shipping rates for all domains (apply to all domains)
    $shippingRates = \App\Models\ShippingRate::where('is_active', true)
        ->with('shippingZone')
        ->orderByRaw("CASE WHEN domain = ? THEN 0 ELSE 1 END", [$currentDomain]) // Prioritize current domain rates
        ->orderBy('is_default', 'desc')
        ->orderBy('sort_order')
        ->get();
    
    // Get default shipping rate (the one with is_default = true)
    $defaultShippingRate = $shippingRates->where('is_default', true)->first();
    
    // If no default rate, use the first active rate
    if (!$defaultShippingRate && $shippingRates->count() > 0) {
        $defaultShippingRate = $shippingRates->first();
    }
    
    // Prepare shipping rates data for JavaScript (grouped by zone)
    $shippingRatesByZone = [];
    $shippingRatesData = [];
    
    foreach ($shippingRates as $rate) {
        // Determine zone name: use shippingZone name if exists, otherwise try to extract from rate name or use 'General'
        $zoneName = 'General';
        if ($rate->shippingZone) {
            $zoneName = $rate->shippingZone->name;
        } elseif ($rate->shipping_zone_id === null) {
            // For general domain rates, try to extract zone name from rate name
            // Check if rate name contains common zone names
            $rateName = strtolower($rate->name ?? '');
            if (stripos($rateName, 'euro') !== false || stripos($rateName, 'europe') !== false) {
                $zoneName = 'Euro';
            } elseif (stripos($rateName, 'asia') !== false) {
                $zoneName = 'Asia';
            } elseif (stripos($rateName, 'america') !== false || stripos($rateName, 'us') !== false) {
                $zoneName = 'America';
            }
        }
        
        $rateData = [
            'id' => $rate->id,
            'zone_id' => $rate->shipping_zone_id,
            'zone_name' => $zoneName,
            'category_id' => $rate->category_id,
            'name' => $rate->name,
            'domain' => $rate->domain, // Add domain info to distinguish domain-specific vs general domain rates
            'first_item_cost' => (float) $rate->first_item_cost,
            'additional_item_cost' => (float) $rate->additional_item_cost,
            'is_default' => (bool) $rate->is_default,
            'min_items' => $rate->min_items,
            'max_items' => $rate->max_items,
            'min_order_value' => $rate->min_order_value ? (float) $rate->min_order_value : null,
            'max_order_value' => $rate->max_order_value ? (float) $rate->max_order_value : null,
        ];
        
        $shippingRatesData[] = $rateData;
        
        // Group by zone
        $zoneId = $rate->shipping_zone_id ?? 'none';
        if (!isset($shippingRatesByZone[$zoneId])) {
            $shippingRatesByZone[$zoneId] = [
                'zone_id' => $rate->shipping_zone_id,
                'zone_name' => $zoneName,
                'rates' => []
            ];
        }
        $shippingRatesByZone[$zoneId]['rates'][] = $rateData;
    }
    
    // Get unique shipping zones from rates (with shipping_zone_id)
    $shippingZones = $shippingRates->pluck('shippingZone')
        ->filter()
        ->unique('id')
        ->sortBy('sort_order')
        ->values();
    
    // Mapping country codes to country names
    $countryNamesMap = [
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'UK' => 'United Kingdom',
        'CA' => 'Canada',
        'AU' => 'Australia',
        'MX' => 'Mexico',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'NL' => 'Netherlands',
        'BE' => 'Belgium',
        'CH' => 'Switzerland',
        'AT' => 'Austria',
        'SE' => 'Sweden',
        'NO' => 'Norway',
        'DK' => 'Denmark',
        'FI' => 'Finland',
        'IE' => 'Ireland',
        'PT' => 'Portugal',
        'GR' => 'Greece',
        'PL' => 'Poland',
        'CZ' => 'Czech Republic',
        'HU' => 'Hungary',
        'RO' => 'Romania',
        'BG' => 'Bulgaria',
        'HR' => 'Croatia',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'EE' => 'Estonia',
        'LV' => 'Latvia',
        'LT' => 'Lithuania',
        'JP' => 'Japan',
        'CN' => 'China',
        'KR' => 'South Korea',
        'SG' => 'Singapore',
        'MY' => 'Malaysia',
        'TH' => 'Thailand',
        'ID' => 'Indonesia',
        'PH' => 'Philippines',
        'VN' => 'Vietnam',
        'IN' => 'India',
        'NZ' => 'New Zealand',
        'BR' => 'Brazil',
        'AR' => 'Argentina',
        'CL' => 'Chile',
        'CO' => 'Colombia',
        'PE' => 'Peru',
        'ZA' => 'South Africa',
        'EG' => 'Egypt',
        'AE' => 'United Arab Emirates',
        'SA' => 'Saudi Arabia',
        'IL' => 'Israel',
        'TR' => 'Turkey',
        'RU' => 'Russia',
        'UA' => 'Ukraine',
    ];
    
    // Helper function to convert country codes to names
    $convertCountryCodesToNames = function($countryCodes) use ($countryNamesMap) {
        if (empty($countryCodes) || !is_array($countryCodes)) {
            return [];
        }
        return array_map(function($code) use ($countryNamesMap) {
            $codeUpper = strtoupper($code);
            return $countryNamesMap[$codeUpper] ?? $codeUpper;
        }, $countryCodes);
    };
    
    // Prepare zones data for dropdown
    // Create separate options for each country in each zone
    $zonesData = [];
    $zonesWithCountries = [];
    
    // Include zones with shipping_zone_id (from ShippingZone model)
    foreach ($shippingZones as $zone) {
        // Get country codes from zone
        $countries = $zone->countries ?? [];
        $countryCodes = is_array($countries) ? $countries : [];
        
        // Convert country codes to country names
        $countryNames = $convertCountryCodesToNames($countryCodes);
        
        // If zone has countries, create separate options for each country
        if (!empty($countryCodes)) {
            $zoneData = [
                'id' => $zone->id,
                'name' => $zone->name,
                'description' => $zone->description,
                'countries' => $countryCodes,
                'country_options' => []
            ];
            
            // Create an option for each country
            foreach ($countryCodes as $index => $countryCode) {
                $countryName = $countryNames[$index] ?? strtoupper($countryCode);
                $zoneData['country_options'][] = [
                    'value' => $zone->id . ':' . strtoupper($countryCode),
                    'label' => $countryName,
                    'zone_id' => $zone->id,
                    'country_code' => strtoupper($countryCode)
                ];
            }
            
            $zonesWithCountries[] = $zoneData;
        } else {
            // Zone without countries - keep as single option
            $zonesData[] = [
                'id' => $zone->id,
                'name' => $zone->name,
                'description' => $zone->description,
                'countries' => [],
                'display_name' => $zone->name,
            ];
        }
    }
    
    // Mapping for common general domain zones to country codes
    // This can be customized based on your actual country assignments
    $generalZoneCountries = [
        'Euro' => ['AT', 'BE', 'DE', 'FR', 'IT', 'NL', 'ES', 'CH', 'UK'], // 9 European countries
        'Europe' => ['AT', 'BE', 'DE', 'FR', 'IT', 'NL', 'ES', 'CH', 'UK'],
        'Asia' => ['CN', 'JP', 'KR', 'SG', 'MY', 'TH', 'ID', 'PH', 'VN'],
        'America' => ['US', 'CA', 'MX'],
        'US' => ['US'],
    ];
    
    // Also include zones with null shipping_zone_id (general domain zones)
    // These are zones that don't have a ShippingZone record but have rates
    // Use zone name as identifier for these zones (prefixed with 'general_')
    foreach ($shippingRatesByZone as $zoneId => $zoneData) {
        if ($zoneId === 'none' || $zoneData['zone_id'] === null) {
            // Check if this zone name already exists in zonesData
            $exists = collect($zonesData)->contains(function($zone) use ($zoneData) {
                return $zone['name'] === $zoneData['zone_name'];
            });
            
            if (!$exists && !empty($zoneData['zone_name'])) {
                // Get countries for this general domain zone from mapping
                $zoneName = $zoneData['zone_name'];
                $countries = $generalZoneCountries[$zoneName] ?? [];
                
                // Convert country codes to country names
                $countryNames = $convertCountryCodesToNames($countries);
                
                // If zone has countries, create separate options for each country
                if (!empty($countries)) {
                    $zoneId = 'general_' . strtolower(str_replace(' ', '_', $zoneName));
                    $zoneDataItem = [
                        'id' => $zoneId,
                        'name' => $zoneName,
                        'description' => null,
                        'countries' => $countries,
                        'country_options' => []
                    ];
                    
                    // Create an option for each country
                    foreach ($countries as $index => $countryCode) {
                        $countryName = $countryNames[$index] ?? strtoupper($countryCode);
                        $zoneDataItem['country_options'][] = [
                            'value' => $zoneId . ':' . strtoupper($countryCode),
                            'label' => $countryName,
                            'zone_id' => $zoneId,
                            'country_code' => strtoupper($countryCode)
                        ];
                    }
                    
                    $zonesWithCountries[] = $zoneDataItem;
                } else {
                    // Zone without countries - keep as single option
                    $zonesData[] = [
                        'id' => 'general_' . strtolower(str_replace(' ', '_', $zoneName)),
                        'name' => $zoneName,
                        'description' => null,
                        'countries' => [],
                        'display_name' => $zoneName,
                    ];
                }
            }
        }
    }
    
    // Prepare default shipping rate data for JavaScript
    $defaultShippingRateData = null;
    if ($defaultShippingRate) {
        $defaultShippingRateData = [
            'id' => $defaultShippingRate->id,
            'category_id' => $defaultShippingRate->category_id,
            'name' => $defaultShippingRate->name,
            'description' => $defaultShippingRate->description,
            'first_item_cost' => (float) $defaultShippingRate->first_item_cost,
            'additional_item_cost' => (float) $defaultShippingRate->additional_item_cost,
            'is_default' => true,
            'min_items' => $defaultShippingRate->min_items,
            'max_items' => $defaultShippingRate->max_items,
            'min_order_value' => $defaultShippingRate->min_order_value ? (float) $defaultShippingRate->min_order_value : null,
            'max_order_value' => $defaultShippingRate->max_order_value ? (float) $defaultShippingRate->max_order_value : null,
            'zone_id' => $defaultShippingRate->shipping_zone_id,
            'zone_name' => $defaultShippingRate->shippingZone ? $defaultShippingRate->shippingZone->name : null,
        ];
    }
    
    @endphp

<script>
const TIKTOK_PRODUCT_ID = {!! json_encode((string) $product->id) !!};
const TIKTOK_PRODUCT_NAME = {!! json_encode($product->name) !!};
const TIKTOK_PRIMARY_CATEGORY = @json($primaryCategory);
const TIKTOK_PRODUCT_PRICE = {{ $productPriceConverted }};
const CURRENT_CURRENCY = @json($currentCurrency);
const CURRENCY_SYMBOL = @json($currencySymbol ?? '$');
const CURRENT_CURRENCY_RATE = {{ $currentCurrencyRate }};
const AUTH_USER = {!! json_encode(auth()->check() ? [
    'email' => auth()->user()->email,
    'name' => auth()->user()->name,
    'country' => auth()->user()->country ?? null,
] : null) !!};
const CART_PROMO_CLAIMED_KEY = 'cart_promo_claimed';
const FREE_SHIPPING_THRESHOLD_USD = {{ (float) (($productShowSettings ?? \App\Support\CatalogPageSettings::productShow())['free_shipping_threshold_usd'] ?? \App\Support\CatalogPageSettings::freeShippingThresholdUsd()) }};
const SHIPPING_RATES = @json($shippingRatesData);
const SHIPPING_RATES_BY_ZONE = @json($shippingRatesByZone);
const SHIPPING_ZONES = @json($zonesData);
const SHIPPING_ZONES_WITH_COUNTRIES = @json($zonesWithCountries);
const DEFAULT_SHIPPING_RATE = @json($defaultShippingRateData);
const DEFAULT_SHIPPING_ZONE_ID = @json($defaultShippingRate ? $defaultShippingRate->shipping_zone_id : null);
const CURRENT_DOMAIN = @json($currentDomain ?? null);

// Get countries from shipping zones for current domain
@php
    $domainCountries = $shippingZones->flatMap(function($zone) {
        return collect($zone->countries ?? [])->map(function($country) use ($zone) {
            return [
                'code' => strtoupper($country),
                'zone_name' => $zone->name
            ];
        });
    })->unique('code')->values()->toArray();
@endphp
const DOMAIN_COUNTRIES = @json($domainCountries);

// Track Facebook Pixel ViewContent for product detail page
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: '{{ addslashes($product->name) }}',
            content_ids: ['{{ $product->id }}'],
            content_type: 'product',
            value: {{ $productPriceConverted }},
            currency: CURRENT_CURRENCY
        });
    }

    // Event tracking Ä‘Æ°á»£c xá»­ lÃ½ bá»Ÿi GTM thÃ´ng qua dataLayer
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': 'view_item',
            'currency': CURRENT_CURRENCY,
            'value': {{ $productPriceConverted }},
            'items': [{
                item_id: '{{ $product->sku ?? $product->id }}',
                item_name: '{{ addslashes($product->name) }}',
                item_category: @json($primaryCategory),
                price: {{ $productPriceConverted }},
                quantity: 1
            }]
        });
    }

    if (typeof window !== 'undefined' && window.ttq) {
        const tiktokViewContent = {
            contents: [{
                content_id: TIKTOK_PRODUCT_ID,
                content_type: 'product',
                content_name: TIKTOK_PRODUCT_NAME,
                quantity: 1,
                price: TIKTOK_PRODUCT_PRICE
            }],
            value: TIKTOK_PRODUCT_PRICE,
            currency: CURRENT_CURRENCY
        };

        if (TIKTOK_PRIMARY_CATEGORY) {
            tiktokViewContent.contents[0].content_category = TIKTOK_PRIMARY_CATEGORY;
        }

        window.ttq.track('ViewContent', tiktokViewContent);
    }
});
</script>
<!-- Product page -->
<section class="product-show-page catalog-page" aria-labelledby="product-show-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 product-show-page__body">
        <nav class="product-show-breadcrumb" aria-label="Breadcrumb">
            @foreach ($breadcrumbs as $index => $breadcrumb)
                @if ($index > 0)
                    <svg class="product-show-breadcrumb__chev" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @endif
                @if (!empty($breadcrumb['url']) && $index < count($breadcrumbs) - 1)
                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                @else
                    <span class="product-show-breadcrumb__current">{{ $breadcrumb['name'] }}</span>
                @endif
            @endforeach
        </nav>

        <div class="product-show-layout product-show-layout--sticky-gallery">
            <div class="product-show-col product-show-col--gallery">
                    <div class="product-show-gallery">
                @php
                    $normalizeMediaUrl = static function ($item) {
                        if (is_string($item) && $item !== '') {
                            return $item;
                        }
                        if (is_array($item)) {
                            $url = $item['url'] ?? $item['path'] ?? (reset($item) ?: null);
                            return is_string($url) && $url !== '' ? $url : null;
                        }
                        return null;
                    };

                    $allImages = [];
                    $seenUrls = [];

                    $productMedia = is_array($product->media) ? $product->media : [];
                    foreach ($productMedia as $mediaItem) {
                        $url = $normalizeMediaUrl($mediaItem);
                        if ($url && !isset($seenUrls[$url])) {
                            $allImages[] = $url;
                            $seenUrls[$url] = true;
                        }
                    }

                    $productImageCount = count($allImages);

                    $templateMedia = [];
                    if ($product->template && $product->template->media) {
                        $templateMedia = is_array($product->template->media) ? $product->template->media : [];
                    }
                    foreach ($templateMedia as $mediaItem) {
                        $url = $normalizeMediaUrl($mediaItem);
                        if ($url && !isset($seenUrls[$url])) {
                            $allImages[] = $url;
                            $seenUrls[$url] = true;
                        }
                    }

                    $media = $allImages;
                    $firstMediaUrl = $allImages[0] ?? '';
                @endphp

            <div class="product-show-gallery__sticky-media">
            <div class="product-show-gallery__frame">
            @if(!empty($allImages) && count($allImages) > 1)
                <aside class="product-show-gallery__nav-block" aria-label="Product gallery thumbnails">
                    <div class="product-show-gallery__thumbs-wrap">
                        @if(count($allImages) > 4)
                            <button type="button" onclick="scrollThumbnails('up')"
                                    class="product-show-gallery__thumb-scroll product-show-gallery__thumb-scroll--up"
                                    aria-label="Scroll thumbnails up">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                            </button>
                        @endif
                        <div class="product-show-gallery__thumbs flex overflow-x-auto scrollbar-hide" id="thumbnail-container">
                    @foreach($allImages as $index => $imageUrl)
                                @php
                                    $isThumbVideo = str_contains($imageUrl, '.mp4') || str_contains($imageUrl, '.mov') || str_contains($imageUrl, '.avi') || str_contains($imageUrl, '.webm');
                                    $isTemplateThumb = $index >= $productImageCount;
                                @endphp
                                <button type="button" onclick="changeMainImage('{{ $imageUrl }}', {{ $index }})"
                                        class="product-show-gallery__thumb flex-shrink-0 bg-white rounded-lg shadow-sm overflow-hidden border-2 {{ $index === 0 ? 'border-[#005366]' : 'border-gray-200' }} hover:border-[#005366] transition-colors group relative"
                                        aria-label="View media {{ $index + 1 }}{{ $isTemplateThumb ? ' (template)' : '' }}"
                                        aria-current="{{ $index === 0 ? 'true' : 'false' }}">
                                    @if($isThumbVideo)
                                        <div class="w-full h-full bg-[#005366]/10 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-[#005366]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
                                            </svg>
                                        </div>
                                        <div class="absolute bottom-0 left-0 right-0 bg-[#005366] text-white text-[8px] text-center py-0.5 font-bold">
                                            Video
                                        </div>
                                    @else
                                        <img src="{{ $imageUrl }}"
                                             alt="{{ $product->name }} - Media {{ $index + 1 }}"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    @endif
                        </button>
                    @endforeach
                        </div>
                        @if(count($allImages) > 4)
                            <button type="button" onclick="scrollThumbnails('down')"
                                    class="product-show-gallery__thumb-scroll product-show-gallery__thumb-scroll--down"
                                    aria-label="Scroll thumbnails down">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <button type="button" onclick="scrollThumbnails('left')"
                                    class="product-show-gallery__thumb-scroll product-show-gallery__thumb-scroll--prev"
                                    aria-label="Scroll thumbnails left">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <button type="button" onclick="scrollThumbnails('right')"
                                    class="product-show-gallery__thumb-scroll product-show-gallery__thumb-scroll--next"
                                    aria-label="Scroll thumbnails right">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </aside>
            @endif
            <!-- Main Image/Video -->
            <div class="product-show-gallery__main overflow-hidden relative group" id="image-container">
                @if(!empty($allImages) && count($allImages) > 1)
                    <button type="button" class="product-show-gallery__nav product-show-gallery__nav--prev" onclick="navigateMainGallery('prev')" aria-label="Previous image">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" class="product-show-gallery__nav product-show-gallery__nav--next" onclick="navigateMainGallery('next')" aria-label="Next image">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @endif
                @if(!empty($allImages))
                    @php
                        $isVideo = str_contains($firstMediaUrl, '.mp4') || str_contains($firstMediaUrl, '.mov') || str_contains($firstMediaUrl, '.avi') || str_contains($firstMediaUrl, '.webm');
                    @endphp
                    
                    @if($isVideo)
                        <!-- Video Player -->
                        @php
                            // Get poster image: first image from media array
                            $posterImage = null;
                            foreach($allImages as $mediaItem) {
                                if (!str_contains($mediaItem, '.mp4') && !str_contains($mediaItem, '.mov') && !str_contains($mediaItem, '.avi') && !str_contains($mediaItem, '.webm')) {
                                    $posterImage = $mediaItem;
                                    break;
                                }
                            }
                            // If no image, use template media or generate placeholder
                            if (!$posterImage && $product->template && $product->template->media) {
                                $templateMedia = is_array($product->template->media) ? $product->template->media : json_decode($product->template->media, true);
                                if ($templateMedia && count($templateMedia) > 0) {
                                    foreach($templateMedia as $tmItem) {
                                        // Get URL safely
                                        if (is_string($tmItem)) {
                                            $tmUrl = $tmItem;
                                        } elseif (is_array($tmItem)) {
                                            $tmUrl = $tmItem['url'] ?? $tmItem['path'] ?? reset($tmItem) ?? '';
                                        } else {
                                            $tmUrl = '';
                                        }
                                        if (!str_contains($tmUrl, '.mp4') && !str_contains($tmUrl, '.mov') && !str_contains($tmUrl, '.avi') && !str_contains($tmUrl, '.webm')) {
                                            $posterImage = $tmUrl;
                                            break;
                                        }
                                    }
                                }
                            }
                        @endphp
                        <video id="main-video" 
                               class="w-full h-full object-cover cursor-pointer" 
                               controls 
                               playsinline
                               @if($posterImage)
                               poster="{{ $posterImage }}"
                               @endif>
                            <source src="{{ $firstMediaUrl }}" type="video/mp4">
                            <source src="{{ $firstMediaUrl }}" type="video/webm">
                            Your browser does not support the video tag.
                        </video>
                        
                        <div class="absolute top-3 left-3 bg-[#005366] text-white text-xs px-3 py-1 rounded-full font-medium flex items-center space-x-1 pointer-events-none z-10">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
                            </svg>
                            <span>Video</span>
                        </div>
                        
                        <div id="video-play-overlay" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 transition-opacity cursor-pointer z-20" onclick="playVideoOnClick(event)">
                            <div class="w-20 h-20 bg-white bg-opacity-90 rounded-full flex items-center justify-center shadow-2xl hover:bg-opacity-100 hover:scale-110 transition-all duration-300">
                                <svg class="w-10 h-10 text-[#005366] ml-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                                </svg>
                            </div>
                        </div>
                    @else
                        <!-- Image -->
                        <img src="{{ $firstMediaUrl }}" 
                             alt="{{ $product->name }}" 
                             id="main-image"
                             class="w-full h-full object-cover">
                        
                        <!-- Zoom Overlay (Only for images) -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                                <div class="bg-white bg-opacity-90 rounded-full p-3 shadow-lg zoom-icon">
                                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Media Counter Badge -->
                    @if(!empty($allImages) && count($allImages) > 1)
                        <div class="absolute top-3 right-3 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded-full">
                            <span id="image-counter">1</span> / {{ count($allImages) }}
                        </div>
                    @endif
                    
                    <!-- Loading Spinner -->
                    <div id="image-loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#005366]"></div>
                    </div>
                    
                @else
                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif

                @if($primaryCategory)
                    <span class="product-show-gallery__badge">{{ $primaryCategory }}</span>
                @endif
                @if($isFlashSale)
                    <span class="product-show-gallery__flash-badge product-show-gallery__flash-badge--live">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="product-show-gallery__flash-badge-text">Flash Sale</span>
                        @if($flashDiscountPct > 0)
                            <span class="product-show-gallery__flash-badge-pct">-{{ $flashDiscountPct }}%</span>
                        @endif
                    </span>
                @endif

                <div class="product-show-gallery__wishlist">
                    <x-wishlist-button :product="$product" size="md" :showText="false" />
                </div>
                @if (! empty($tryOnMedia['image']))
                    <button type="button"
                            class="product-show-gallery__tryon"
                            data-tryon-open
                            data-tryon-name="{{ $product->name }}"
                            data-tryon-type="{{ $product->getDisplayCategoryName() }}"
                            data-tryon-url="{{ route('products.show', $product->slug) }}"
                            data-tryon-image="{{ $tryOnMedia['image'] }}"
                            data-tryon-back="{{ $tryOnMedia['back'] }}"
                            aria-label="Start virtual try-on">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        TRY ON
                    </button>
                @endif
                
                <!-- Hover Effects -->
                <div class="absolute inset-0 border-2 border-transparent group-hover:border-[#005366] transition-all duration-300 rounded-xl"></div>
            </div>
            </div>{{-- /.product-show-gallery__frame --}}

            @if(!empty($allImages) && count($allImages) > 1)
                <div class="product-show-gallery__meta">
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ count($allImages) }} {{ count($allImages) === 1 ? 'image' : 'images' }}</span>
                    </div>
                    <button type="button" onclick="openGalleryModal()"
                            class="text-sm text-[#005366] hover:underline flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                        </svg>
                        <span>View All</span>
                    </button>
                </div>
            @endif
            </div>
        </div>
            </div>

            <div class="product-show-col product-show-col--info">
                <div class="product-show-purchase">
            @php
                $averageRating = $product->getAverageRating();
                $totalReviews = $product->getTotalReviews();
                $soldCount = $product->getSoldCount();
                $productShowConfig = $productShowSettings ?? config('catalog.product_show', []);
                $virtualStatsConfig = $productShowConfig['virtual_stats'] ?? [];
                $virtualViews = (int) ($virtualStatsConfig['views_base'] ?? 800)
                    + ($product->id * (int) ($virtualStatsConfig['views_multiplier'] ?? 37) % 1500);
                $virtualInCart = (int) ($virtualStatsConfig['in_cart_base'] ?? 40)
                    + ($product->id * (int) ($virtualStatsConfig['in_cart_multiplier'] ?? 1) % 80);
                $virtualViewsLabel = $virtualViews >= 1000
                    ? rtrim(rtrim(number_format($virtualViews / 1000, 1), '0'), '.') . 'k'
                    : number_format($virtualViews);
                $volumeDiscountTiers = $productShowConfig['volume_discounts'] ?? [];
                $saleEndsDate = $productShowConfig['sale_ends_date'] ?? null;
                if ($saleEndsDate) {
                    try {
                        $saleEndsFormatted = \Carbon\Carbon::parse($saleEndsDate)->format('F d, Y');
                    } catch (\Exception $e) {
                        $saleEndsFormatted = null;
                    }
                } else {
                    $saleEndsFormatted = null;
                }
                $tryOnImage = $tryOnMedia['image'];
                $shopInitials = $product->shop
                    ? strtoupper(substr($product->shop->name ?? $product->shop->shop_name ?? 'B', 0, 2))
                    : 'BP';
                $pdpShop = $product->shop;
                $pdpShopLogo = $pdpShop?->shop_logo;
                $pdpShopRating = null;
                $pdpShopRatingCount = 0;
                if ($pdpShop) {
                    if (($pdpShop->total_ratings ?? 0) > 0 && ($pdpShop->rating ?? 0) > 0) {
                        $pdpShopRating = (float) $pdpShop->getRatingStars();
                        $pdpShopRatingCount = (int) $pdpShop->total_ratings;
                    } elseif (($shopReviewsTotal ?? 0) > 0) {
                        $pdpShopRating = (float) ($shopReviewsAverage ?? 0);
                        $pdpShopRatingCount = (int) ($shopReviewsTotal ?? 0);
                    }
                    $pdpShopProductCount = (int) ($pdpShop->total_products ?? 0);
                    $pdpShopFollowerCount = (int) ($pdpShop->followers_count ?? 0);
                    $pdpShopJoinedLabel = $pdpShop->created_at
                        ? 'Joined ' . $pdpShop->created_at->format('M Y')
                        : null;
                    $pdpShopIsNew = $pdpShop->created_at && $pdpShop->created_at->greaterThan(now()->subDays(60));
                    $pdpShopTrustLine = null;
                    if ($pdpShopProductCount >= 3) {
                        $pdpShopTrustLine = number_format($pdpShopProductCount) . ' ' . \Illuminate\Support\Str::plural('product', $pdpShopProductCount);
                    } elseif ($pdpShopFollowerCount >= 1) {
                        $pdpShopTrustLine = number_format($pdpShopFollowerCount) . ' ' . \Illuminate\Support\Str::plural('follower', $pdpShopFollowerCount);
                    } elseif ($pdpShopJoinedLabel) {
                        $pdpShopTrustLine = $pdpShopJoinedLabel;
                    } elseif ($pdpShopProductCount > 0) {
                        $pdpShopTrustLine = 'Updating catalog';
                    } elseif ($pdpShopIsNew) {
                        $pdpShopTrustLine = 'New shop';
                    }
                }
            @endphp
            <div class="product-show-purchase__head">
                @if($isFlashSale)
                    <div class="product-show-flash-chip" role="status" aria-label="Flash sale active">
                        <span class="product-show-flash-chip__icon" aria-hidden="true">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </span>
                        <span class="product-show-flash-chip__text">Flash Sale</span>
                        @if($flashDiscountPct > 0)
                            <span class="product-show-flash-chip__pct">{{ $flashDiscountPct }}% OFF</span>
                        @endif
                        <span class="product-show-flash-chip__urgency">Limited time</span>
                    </div>
                @endif
                <div class="product-show-purchase__title-row">
                    <h1 id="product-show-heading" class="product-show-purchase__title" title="{{ $product->name }}">{{ $displayTitle }}</h1>
                    <button type="button" onclick="openShareModal()" class="product-show-purchase__share" aria-label="Share this product">
                        <span class="product-show-purchase__share-btn" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                <div class="product-show-purchase__meta-row">
                    <div class="product-show-purchase__meta-item">
                        <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-semibold text-gray-900">{{ number_format($averageRating, 1) }}</span>
                        <span>({{ number_format($totalReviews) }} {{ Str::plural('review', $totalReviews) }})</span>
                    </div>
                    <span class="product-show-purchase__meta-divider" aria-hidden="true"></span>
                    <div class="product-show-purchase__meta-item">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>{{ $virtualViewsLabel }} views</span>
                    </div>
                    <span class="product-show-purchase__meta-divider" aria-hidden="true"></span>
                    <div class="product-show-purchase__meta-item">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>{{ number_format($virtualInCart) }} in cart</span>
                    </div>
                </div>
            </div>

            <div class="product-show-purchase__price-panel @if($isFlashSale) product-show-purchase__price-panel--flash @endif">
                @if($isFlashSale)
                    <div class="product-show-flash-sale"
                         id="pdpFlashSale"
                         data-ends-at="{{ $flashEndsAt->toIso8601String() }}"
                         role="status"
                         aria-live="polite">
                        <div class="product-show-flash-sale__banner">
                            <div class="product-show-flash-sale__intro">
                                <span class="product-show-flash-sale__bolt" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </span>
                                <div class="product-show-flash-sale__copy">
                                    <strong class="product-show-flash-sale__title">Flash Sale Price</strong>
                                    <span class="product-show-flash-sale__subtitle">Grab it before time runs out</span>
                                </div>
                                @if($flashDiscountPct > 0)
                                    <span class="product-show-flash-sale__pct">{{ $flashDiscountPct }}% OFF</span>
                                @endif
                            </div>
                            <div class="product-show-flash-sale__countdown" aria-label="Flash sale ends in">
                                <span class="product-show-flash-sale__countdown-label">Ends in</span>
                                <div class="product-show-flash-sale__timer">
                                    <span class="product-show-flash-sale__time-unit">
                                        <span class="product-show-flash-sale__time-value" id="pdpFlashHours">00</span>
                                        <span class="product-show-flash-sale__time-label">hr</span>
                                    </span>
                                    <span class="product-show-flash-sale__time-sep" aria-hidden="true">:</span>
                                    <span class="product-show-flash-sale__time-unit">
                                        <span class="product-show-flash-sale__time-value" id="pdpFlashMinutes">00</span>
                                        <span class="product-show-flash-sale__time-label">min</span>
                                    </span>
                                    <span class="product-show-flash-sale__time-sep" aria-hidden="true">:</span>
                                    <span class="product-show-flash-sale__time-unit">
                                        <span class="product-show-flash-sale__time-value" id="pdpFlashSeconds">00</span>
                                        <span class="product-show-flash-sale__time-label">sec</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="product-show-purchase__price-row @if($isFlashSale) product-show-purchase__price-row--flash @endif">
                    @if($isFlashSale)
                        <span class="product-show-purchase__price-current product-show-purchase__price-current--flash" id="base-price" data-price="{{ $productPriceUSD }}" data-price-converted="{{ $productPriceConverted }}">{{ format_price_usd($productPriceUSD) }}</span>
                        @if($flashOriginalPriceUsd > $productPriceUSD)
                            <span class="product-show-purchase__price-original" id="flash-original-price" data-price="{{ $flashOriginalPriceUsd }}" data-price-converted="{{ convert_currency($flashOriginalPriceUsd) }}">{{ format_price_usd($flashOriginalPriceUsd) }}</span>
                        @else
                            <span class="product-show-purchase__price-original hidden" id="flash-original-price" data-price="0" data-price-converted="0"></span>
                        @endif
                        @if($flashDiscountPct > 0)
                            <span class="product-show-purchase__save-badge product-show-purchase__save-badge--flash" id="flash-save-badge">{{ $flashDiscountPct }}% OFF</span>
                        @endif
                    @elseif($hasListDiscount)
                        <span class="product-show-purchase__price-current" id="base-price" data-price="{{ $productPriceUSD }}" data-price-converted="{{ $productPriceConverted }}">{{ format_price_usd($productPriceUSD) }}</span>
                        <span class="product-show-purchase__price-original" id="list-price" data-price="{{ $productListPriceUSD }}" data-price-converted="{{ $productListPriceConverted }}">{{ format_price_usd($productListPriceUSD) }}</span>
                        <span class="product-show-purchase__save-badge" id="list-save-badge">Save {{ $listDiscountPct }}%</span>
                        @if($saleEndsFormatted)
                            <span class="product-show-purchase__sale-ends text-sm text-[#e2150c] font-medium">Sale ends {{ $saleEndsFormatted }}</span>
                        @endif
                    @else
                        <span class="product-show-purchase__price-current" id="base-price" data-price="{{ $productPriceUSD }}" data-price-converted="{{ $productPriceConverted }}">{{ format_price_usd($productPriceUSD) }}</span>
                        <span class="product-show-purchase__price-original hidden" id="list-price" data-price="{{ $productListPriceUSD }}" data-price-converted="{{ $productListPriceConverted }}">{{ format_price_usd($productListPriceUSD) }}</span>
                        <span class="product-show-purchase__save-badge hidden" id="list-save-badge">Save 0%</span>
                    @endif
                </div>
                @if($isFlashSale && $flashSavingsUsd)
                    <p class="product-show-flash-sale__savings">You save <strong>{{ format_price_usd($flashSavingsUsd) }}</strong> with this flash deal</p>
                @endif
                <div class="product-show-purchase__stock-row">
                    <span class="product-show-purchase__stock-dot" id="engagement-stock-dot"></span>
                    <span class="product-show-purchase__stock-label" id="engagement-stock-label">In Stock</span>
                    <span class="product-show-purchase__stock-qty" id="engagement-stock-qty"></span>
                </div>
                <p id="volume-discount-hint" class="hidden mt-2 text-sm font-medium text-[#005366]"></p>
                <div id="customization-price-display" class="hidden mt-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Customization:</span>
                        <span class="text-[#005366] font-medium" id="customization-price">+{{ currency_symbol() }}0.00</span>
                    </div>
                    <div class="border-t border-gray-300 mt-2 pt-2 flex items-center justify-between">
                        <span class="text-gray-900 font-semibold">Total:</span>
                        <span class="text-xl font-bold text-[#005366]" id="total-price">{{ format_price($productPriceConverted) }}</span>
                    </div>
                </div>
            </div>

            <hr class="product-show-purchase__divider">

            <div class="product-show-options">
            <div class="product-show-purchase__panel">
                @if($product->variants()->count() > 0)
                    @php
                        $variants = $product->variants;
                        $selectedVariant = $variants->first();

                        $allAttributes = [];
                        foreach ($variants as $variant) {
                            if ($variant->attributes) {
                                foreach ($variant->attributes as $key => $value) {
                                    if (!isset($allAttributes[$key])) {
                                        $allAttributes[$key] = [];
                                    }
                                    if (!in_array($value, $allAttributes[$key], true)) {
                                        $allAttributes[$key][] = $value;
                                    }
                                }
                            }
                        }

                        $sizeOrderMap = ['XS' => 0, 'S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, '2XL' => 5, '3XL' => 6, '4XL' => 7, '5XL' => 8];
                        $sortSizeValues = function (array $values) use ($sizeOrderMap) {
                            usort($values, function ($a, $b) use ($sizeOrderMap) {
                                $oa = $sizeOrderMap[strtoupper(trim($a))] ?? 999;
                                $ob = $sizeOrderMap[strtoupper(trim($b))] ?? 999;
                                if ($oa === $ob) {
                                    return strcasecmp($a, $b);
                                }
                                return $oa <=> $ob;
                            });
                            return $values;
                        };

                        $sortedAttributes = [];
                        $colorKey = null;
                        $sizeKey = null;
                        foreach ($allAttributes as $key => $values) {
                            $norm = strtolower($key);
                            if ($norm === 'color' || $norm === 'colour') {
                                $colorKey = $key;
                            } elseif ($norm === 'size') {
                                $sizeKey = $key;
                            }
                        }
                        if ($colorKey) {
                            $sortedAttributes[$colorKey] = $allAttributes[$colorKey];
                        }
                        if ($sizeKey) {
                            $sortedAttributes[$sizeKey] = $sortSizeValues($allAttributes[$sizeKey]);
                        }
                        foreach ($allAttributes as $key => $values) {
                            if (!isset($sortedAttributes[$key])) {
                                $sortedAttributes[$key] = strtolower($key) === 'size'
                                    ? $sortSizeValues($values)
                                    : $values;
                            }
                        }
                        $allAttributes = $sortedAttributes;

                        $sizes = collect($allAttributes['Size'] ?? []);
                        $colors = collect($allAttributes['Color'] ?? $allAttributes['Colour'] ?? []);
                    @endphp

                    <div class="product-show-options__attrs">
                        @foreach($allAttributes as $attributeName => $attributeValues)
                            @if($attributeName === 'Size')
                                <!-- Size Selection -->
                                <div class="product-show-options__group">
                                    <div class="product-show-options__head">
                                        <h3 class="product-show-options__label">
                                            Size — <span id="selected-size-name">{{ $selectedVariant->attributes['Size'] ?? ($attributeValues[0] ?? '') }}</span>
                                        </h3>
                                        <button type="button" onclick="openSizeGuide()" class="product-show-options__guide">
                                            Size guide
                                        </button>
                                    </div>
                                    <div class="product-show-options__chips" id="size-selector-buttons">
                                        @foreach($attributeValues as $value)
                                            @php $isSizeSelected = ($selectedVariant->attributes['Size'] ?? $attributeValues[0] ?? '') === $value; @endphp
                                            <button type="button"
                                                    onclick="selectAttribute('{{ $attributeName }}', '{{ $value }}')"
                                                    class="attribute-option {{ $isSizeSelected ? 'border-[#005366] bg-[#005366] text-white' : 'border-gray-300 text-gray-900' }}"
                                                    data-attribute="{{ $attributeName }}"
                                                    data-value="{{ $value }}">
                                                {{ $value }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <select id="{{ strtolower($attributeName) }}-selector" onchange="selectAttribute('{{ $attributeName }}', this.value)" class="hidden" aria-hidden="true">
                                        @foreach($attributeValues as $value)
                                            <option value="{{ $value }}" {{ $loop->first ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($attributeName === 'Color' || $attributeName === 'Colour')
                                <!-- Color Selection -->
                                @php
                                    $colorMap = [
                                        // Basic Colors
                                        'black' => '#000000', 'white' => '#ffffff', 'red' => '#dc2626',
                                        'blue' => '#2563eb', 'green' => '#16a34a', 'yellow' => '#eab308',
                                        'purple' => '#9333ea', 'pink' => '#ec4899', 'orange' => '#ea580c',
                                        'brown' => '#a16207', 'gray' => '#6b7280', 'grey' => '#6b7280',
                                        
                                        // Extended Colors
                                        'navy' => '#1e3a8a', 'maroon' => '#991b1b', 'teal' => '#0d9488',
                                        'lime' => '#65a30d', 'cyan' => '#06b6d4', 'indigo' => '#4f46e5',
                                        'violet' => '#8b5cf6', 'rose' => '#f43f5e', 'amber' => '#f59e0b',
                                        'emerald' => '#10b981', 'sky' => '#0ea5e9', 'fuchsia' => '#d946ef',
                                        
                                        // Dark Variants
                                        'dark chocolate' => '#3c2415', 'dark gray' => '#374151', 
                                        'charcoal' => '#374151', 'dark blue' => '#1e40af',
                                        'dark green' => '#166534', 'dark red' => '#991b1b',
                                        
                                        // Light Variants
                                        'light gray' => '#9ca3af', 'light blue' => '#93c5fd',
                                        'light green' => '#86efac', 'light pink' => '#fbb6ce',
                                        'light yellow' => '#fef3c7', 'cream' => '#fef7cd',
                                        
                                        // Special Colors
                                        'gold' => '#fbbf24', 'silver' => '#9ca3af', 'copper' => '#b45309',
                                        'bronze' => '#92400e', 'platinum' => '#6b7280',
                                        
                                        // Pattern Colors
                                        'camo' => '#365314', 'olive' => '#65a30d', 'khaki' => '#a3a3a3',
                                        'beige' => '#f5f5dc', 'tan' => '#d2b48c', 'mint' => '#a7f3d0',
                                        'lavender' => '#e9d5ff', 'coral' => '#fda4af', 'turquoise' => '#5eead4',
                                        
                                        // Additional Colors from UI
                                        'sport grey' => '#9ca3af', 'dark heather' => '#374151',
                                        'royal blue' => '#1d4ed8', 'sand' => '#fbbf24',
                                        'forest green' => '#166534', 'military green' => '#365314',
                                        'ash grey' => '#6b7280', 'natural' => '#fef3c7',
                                        
                                        // Complete color set from user request (case insensitive)
                                        'black' => '#000000', 'white' => '#ffffff',
                                        'light blue' => '#93c5fd', 'charcoal' => '#374151',
                                        'sport grey' => '#9ca3af', 'dark heather' => '#374151',
                                        'navy' => '#1e3a8a', 'maroon' => '#991b1b',
                                        'light pink' => '#fbb6ce', 'red' => '#dc2626',
                                        'royal blue' => '#1d4ed8', 'sand' => '#fbbf24',
                                        'forest green' => '#166534', 'military green' => '#365314',
                                        'ash grey' => '#6b7280', 'purple' => '#9333ea',
                                        'orange' => '#ea580c', 'natural' => '#fef3c7',
                                    ];
                                @endphp
                                <div class="product-show-options__group">
                                    <div class="product-show-options__head">
                                        <h3 class="product-show-options__label">
                                            {{ $attributeName }} — <span id="selected-color-name">{{ $selectedVariant->attributes[$attributeName] ?? ($attributeValues[0] ?? '') }}</span>
                                        </h3>
                                    </div>
                                    <div class="product-show-options__swatches">
                                        @foreach($attributeValues as $color)
                                            @php
                                                $colorCode = $colorMap[strtolower($color)] ?? '#6b7280';
                                                $isColorSelected = ($selectedVariant->attributes[$attributeName] ?? $attributeValues[0] ?? '') === $color;
                                            @endphp
                                            <button type="button" onclick="selectAttribute('{{ $attributeName }}', '{{ $color }}')"
                                                    class="color-swatch variant-option {{ $isColorSelected ? 'is-selected border-[#005366]' : 'border-transparent' }}"
                                                    data-attribute="{{ $attributeName }}"
                                                    data-value="{{ $color }}"
                                                    aria-label="{{ $attributeName }}: {{ $color }}"
                                                    title="{{ $color }}">
                                                <span style="background: {{ $colorCode }};"></span>
                                                <svg class="color-swatch__check" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <!-- Other Attributes -->
                                <div class="product-show-options__group">
                                    <h3 class="product-show-options__label">{{ $attributeName }}</h3>
                                    <div class="product-show-options__chips">
                                        @foreach($attributeValues as $value)
                                            <button type="button" onclick="selectAttribute('{{ $attributeName }}', '{{ $value }}')"
                                                    class="attribute-option {{ $loop->first ? 'border-[#005366] bg-gray-50 text-[#005366]' : 'border-gray-300 text-gray-600' }}"
                                                    data-attribute="{{ $attributeName }}"
                                                    data-value="{{ $value }}">
                                                {{ $value }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Hidden data for JavaScript -->
                    <script type="application/json" id="variants-data">
                        {!! $variants->map(function ($variant) use ($product, $isFlashSale, $flashDiscountPct, $flashOriginalPriceUsd) {
                            $rawVariantPrice = $variant->price;
                            $variantPriceUSD = (float) ($rawVariantPrice ?? $product->getEffectivePrice());
                            $variantListUSD = (float) $variant->getCompareAtPrice();

                            if ($isFlashSale && $flashDiscountPct > 0) {
                                if ($rawVariantPrice !== null) {
                                    // Variant table stores pre-deal price → apply same flash %
                                    $variantListUSD = max($variantListUSD, (float) $rawVariantPrice);
                                    $variantPriceUSD = round((float) $rawVariantPrice * (1 - $flashDiscountPct / 100), 2);
                                } else {
                                    $variantListUSD = max(
                                        $variantListUSD,
                                        (float) ($flashOriginalPriceUsd ?? $variantListUSD)
                                    );
                                    $variantPriceUSD = (float) $product->getEffectivePrice();
                                }
                            }

                            $variantPriceConverted = convert_currency($variantPriceUSD);
                            $variantListConverted = convert_currency($variantListUSD);

                            return [
                                'id' => $variant->id,
                                'attributes' => $variant->attributes ?? [],
                                'size' => $variant->attributes['Size'] ?? null,
                                'color' => $variant->attributes['Color'] ?? null,
                                'colour' => $variant->attributes['Colour'] ?? null,
                                'price' => $variantPriceConverted,
                                'price_usd' => $variantPriceUSD,
                                'list_price' => $variantListConverted,
                                'list_price_usd' => $variantListUSD,
                                'quantity' => $variant->quantity,
                                'variant_name' => $variant->variant_name,
                                'media' => $variant->media,
                                'flash' => $isFlashSale,
                                'flash_percent' => $flashDiscountPct,
                            ];
                        })->values()->toJson() !!}
                    </script>
                @endif

                @include('products.partials.show-ai-redesign', [
                    'product' => $product,
                    'tryOnMedia' => $tryOnMedia,
                    'firstMediaUrl' => $firstMediaUrl ?? ($tryOnMedia['image'] ?? ''),
                ])

                <div class="product-show-purchase__promo-divider" aria-hidden="true"></div>
                @include('products.partials.show-volume-discounts', ['volumeDiscountTiers' => $volumeDiscountTiers])
                @include('products.partials.show-customization', ['product' => $product])

                <!-- Action Buttons -->
                <div class="product-show-purchase__actions-bar">
                    <div class="product-show-purchase__actions-inner">
                        <div class="product-show-purchase__qty" aria-label="Quantity">
                            <button type="button" class="product-show-purchase__qty-btn" onclick="changeProductQuantity(-1)" aria-label="Decrease quantity">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </button>
                            <span class="product-show-purchase__qty-value" id="product-quantity">1</span>
                            <button type="button" class="product-show-purchase__qty-btn" onclick="changeProductQuantity(1)" aria-label="Increase quantity">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <div class="product-show-purchase__actions">
                            <button id="add-to-cart-btn"
                                    type="button"
                                    onclick="addToCart()"
                                    class="product-show-purchase__btn product-show-purchase__btn--cart disabled:opacity-50 disabled:cursor-not-allowed">
                                <span id="cart-text">Add to Cart</span>
                                <div id="cart-loading" class="hidden">
                                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-[#005366]"></div>
                                </div>
                            </button>
                            <button type="button" onclick="buyNow()" class="product-show-purchase__btn product-show-purchase__btn--buy">
                                Buy Now
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button"
                        class="product-show-purchase__tryon"
                        data-tryon-open
                        data-tryon-name="{{ $product->name }}"
                        data-tryon-type="{{ $product->getDisplayCategoryName() }}"
                        data-tryon-url="{{ route('products.show', $product->slug) }}"
                        data-tryon-image="{{ $tryOnMedia['image'] }}"
                        data-tryon-back="{{ $tryOnMedia['back'] }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    Try it on with AI
                </button>

                @if($product->shop)
                    <div class="product-show-purchase__shop-divider" aria-hidden="true"></div>

                    <div class="product-show-purchase__shop-card">
                        <p class="product-show-purchase__shop-eyebrow">Sold by</p>
                        <div class="product-show-purchase__shop-body">
                            <a href="{{ route('shops.show', $product->shop->shop_slug ?? $product->shop->id) }}"
                               class="product-show-purchase__shop-info-link">
                                @if ($pdpShopLogo)
                                    <img src="{{ $pdpShopLogo }}"
                                         alt="{{ $product->shop->shop_name }} logo"
                                         class="product-show-purchase__shop-avatar product-show-purchase__shop-avatar--photo"
                                         loading="lazy">
                                @else
                                    <div class="product-show-purchase__shop-avatar" aria-hidden="true">{{ $shopInitials }}</div>
                                @endif
                                <div class="product-show-purchase__shop-copy">
                                    <h4 class="product-show-purchase__shop-name">{{ $product->shop->shop_name ?? $product->shop->name ?? 'Shop' }}</h4>
                                    <div class="product-show-purchase__shop-stats">
                                        @if ($pdpShopRatingCount > 0 && $pdpShopRating !== null)
                                            <span class="product-show-purchase__shop-rating">
                                                <svg class="product-show-purchase__shop-star" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                                {{ number_format($pdpShopRating, 1) }}
                                                <span class="product-show-purchase__shop-rating-count">({{ number_format($pdpShopRatingCount) }})</span>
                                            </span>
                                        @endif
                                        @if ($soldCount > 0)
                                            @if ($pdpShopRatingCount > 0)
                                                <span class="product-show-purchase__shop-stat-sep" aria-hidden="true">·</span>
                                            @endif
                                            <span>{{ number_format($soldCount) }} sold<span class="product-show-purchase__shop-sold-detail"> on this item</span></span>
                                        @endif
                                    </div>
                                    @if (! empty($pdpShopTrustLine))
                                        <p class="product-show-purchase__shop-trust">{{ $pdpShopTrustLine }}</p>
                                    @endif
                                </div>
                            </a>
                            <div class="product-show-purchase__shop-actions">
                                <button type="button"
                                        id="pdpContactShopBtn"
                                        class="product-show-purchase__shop-message"
                                        aria-label="Message {{ $product->shop->shop_name }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span class="product-show-purchase__shop-message-label">Message</span>
                                </button>
                                <a href="{{ route('shops.show', $product->shop->shop_slug ?? $product->shop->id) }}"
                                   class="product-show-purchase__shop-visit">
                                    Visit store
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                @include('products.partials.show-listing-below')
            </div>
            </div>
                </div>
            </div>
        </div>

        <div class="product-show-below">
            <div class="product-show-below__reviews">
                @include('products.partials.show-gallery-reviews')
            </div>

            @include('products.partials.show-frequently-bought-together', [
                'fbtProducts' => $fbtProducts,
                'product' => $product,
            ])

            @include('products.partials.show-customize-hero')

            @include('products.partials.show-you-might-love-these', [
                'collectionProducts' => $collectionProducts,
                'product' => $product,
            ])

            @include('products.partials.show-recently-viewed', ['product' => $product])

            <div class="product-show-info__tail">
                @php
                    $productTags = collect($product->keywords ?? [])->filter()->take(5);
                @endphp
                @if($productTags->isNotEmpty())
                    <dl class="product-show-purchase__meta-list mt-4">
                        <div class="product-show-purchase__meta-list-row">
                            <dt>Tags</dt>
                            <dd class="product-show-purchase__tags">
                                @foreach($productTags as $tag)
                                    <a href="{{ route('products.index', ['search' => $tag]) }}" class="product-show-purchase__tag">{{ $tag }}</a>
                                @endforeach
                            </dd>
                        </div>
                    </dl>
                @endif
            </div>
        </div>
    </div>

</section>

<!-- Gallery Modal -->
<div id="gallery-modal" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 hidden">
    <div class="relative w-full h-full max-w-7xl mx-auto p-4">
        <!-- Close Button -->
        <button type="button" onclick="closeGalleryModal()"
                class="absolute top-4 right-4 z-10 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-70 transition-colors"
                aria-label="Close gallery">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        <!-- Main Media (Image or Video) -->
        <div class="flex items-center justify-center h-full">
            <!-- Image -->
            <img id="modal-main-image" src="" alt="" class="max-w-full max-h-full object-contain">
            
            <!-- Video Player -->
            <video id="modal-main-video" 
                   class="max-w-full max-h-full object-contain hidden" 
                   controls 
                   playsinline
                   controlsList="nodownload">
                <source id="modal-video-source" src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <!-- Media Type Badge -->
        <div id="modal-media-badge" class="absolute top-4 left-1/2 transform -translate-x-1/2 bg-[#005366] text-white text-xs px-3 py-1 rounded-full font-medium items-center space-x-1 hidden">
            <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
            </svg>
            <span class="inline">VIDEO</span>
        </div>
        
        <!-- Navigation -->
        <button type="button" onclick="previousImage()"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-3 hover:bg-opacity-70 transition-colors"
                aria-label="Previous media">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button type="button" onclick="nextImage()"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-3 hover:bg-opacity-70 transition-colors"
                aria-label="Next media">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
        </button>
        
        <!-- Thumbnail Strip -->
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2">
            <div class="flex space-x-2 bg-black bg-opacity-50 rounded-lg p-2">
                @foreach($allImages as $index => $imageUrl)
                    @php
                        $isModalThumbVideo = str_contains($imageUrl, '.mp4') || str_contains($imageUrl, '.mov') || str_contains($imageUrl, '.avi') || str_contains($imageUrl, '.webm');
                    @endphp
                    <button onclick="selectModalImage('{{ $imageUrl }}', {{ $index }})" 
                            class="w-12 h-12 rounded overflow-hidden border-2 border-transparent hover:border-white transition-colors relative">
                        @if($isModalThumbVideo)
                            <div class="w-full h-full bg-[#005366]/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#005366]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
                                </svg>
                            </div>
                        @else
                            <img src="{{ $imageUrl }}" alt="" class="w-full h-full object-cover">
                        @endif
                    </button>
                @endforeach
        </div>
    </div>
        
        <!-- Media Counter -->
        <div class="absolute top-4 left-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
            <span id="modal-image-counter">1</span> / {{ !empty($allImages) ? count($allImages) : 0 }}
</div>
    </div>
</div>

<!-- Enhanced Color Picker -->
<script src="{{ asset('js/color-picker.js') }}"></script>

<style>
/* Hide scrollbar for thumbnail container */
.scrollbar-hide {
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar { 
    display: none;  /* Safari and Chrome */
}

/* Mobile scrollbar hiding for Related Products and Recently Viewed */
@media (max-width: 1023px) {
    .mobile-scroll-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .mobile-scroll-hide::-webkit-scrollbar {
        display: none;
    }
}

/* Cart drawer — slide-in panel (replaces centered modal) */
body.cart-drawer-open {
    overflow: hidden;
}
.cart-drawer-backdrop {
    position: fixed;
    inset: 0;
    z-index: 94;
    background: rgba(17, 24, 39, 0.45);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.28s ease;
}
.cart-drawer-backdrop.is-open {
    opacity: 1;
    pointer-events: auto;
}
.cart-drawer {
    position: fixed;
    inset: 0 0 0 auto;
    z-index: 95;
    display: flex;
    flex-direction: column;
    width: min(100vw, 420px);
    height: 100vh;
    height: 100dvh;
    background: #fff;
    border-left: 1px solid #e5e7eb;
    box-shadow: -8px 0 32px rgba(0, 0, 0, 0.12);
    transform: translateX(100%);
    transition: transform 0.28s ease;
}
.cart-drawer.is-open {
    transform: translateX(0);
}
.cart-drawer__scroll {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
}
.cart-drawer__footer {
    flex-shrink: 0;
    border-top: 1px solid #e5e7eb;
    background: #fff;
    box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.04);
}
.cart-drawer-freeship {
    padding: 10px 16px;
    background: #fffbeb;
    border-bottom: 1px solid #fde68a;
}
.cart-drawer-freeship--done {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ecfdf5;
    border-bottom-color: #bbf7d0;
    font-size: 13px;
    font-weight: 600;
    color: #166534;
}
.cart-drawer-freeship__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 9999px;
    background: #22c55e;
    color: #fff;
    font-size: 11px;
    flex-shrink: 0;
}
.cart-drawer-freeship__text {
    margin: 0 0 6px;
    font-size: 12px;
    line-height: 1.35;
    color: #78350f;
}
.cart-drawer-freeship__track {
    height: 6px;
    border-radius: 9999px;
    background: #fde68a;
    overflow: hidden;
}
.cart-drawer-freeship__fill {
    display: block;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #f59e0b, #ea580c);
    transition: width 0.25s ease;
}
.cart-drawer-bill {
    padding: 12px 16px 10px;
    background: #f9fafb;
}
.cart-drawer-bill__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    font-size: 13px;
    color: #4b5563;
    line-height: 1.4;
}
.cart-drawer-bill__row + .cart-drawer-bill__row {
    margin-top: 6px;
}
.cart-drawer-bill__row-value {
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
}
.cart-drawer-bill__row-value--free {
    color: #16a34a;
    font-weight: 700;
}
.cart-drawer-bill__row--error {
    color: #e2150c;
}
.cart-drawer-bill__shipping-label {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}
.cart-drawer-bill__zone-edit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    padding: 0;
    border: none;
    border-radius: 6px;
    background: transparent;
    color: #005366;
    cursor: pointer;
    vertical-align: middle;
}
.cart-drawer-bill__zone-edit:hover {
    background: rgba(0, 83, 102, 0.08);
}
.cart-drawer-bill__total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #e5e7eb;
    font-size: 15px;
    font-weight: 700;
    color: #111827;
}
.cart-drawer-bill__total-value {
    font-size: 17px;
    color: #005366;
}
.cart-popup-zone-editor {
    padding: 0 16px 10px;
    background: #f9fafb;
}
.cart-popup-zone-editor--hidden {
    display: none;
}
.cart-popup-zone-editor__select {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 13px;
    color: #111827;
    background: #fff;
}
.cart-popup-zone-editor__select:focus {
    outline: none;
    border-color: #005366;
    box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.12);
}
.cart-drawer-promo {
    padding: 12px 16px;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 55%, #fef3c7 100%);
    border-top: 2px solid #fb923c;
    border-bottom: 1px solid #fdba74;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
}
.cart-drawer-promo__head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.cart-drawer-promo__code {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 58px;
    padding: 8px 10px;
    border-radius: 8px;
    background: #005366;
    color: #fff;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.06em;
    box-shadow: 0 3px 10px rgba(0, 83, 102, 0.35);
}
.cart-drawer-promo__title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.25;
    color: #9a3412;
}
.cart-drawer-promo__sub {
    margin: 2px 0 0;
    font-size: 11px;
    line-height: 1.35;
    color: #c2410c;
}
.cart-drawer-promo__form {
    display: flex;
    gap: 8px;
}
.cart-drawer-promo__input {
    flex: 1;
    min-width: 0;
    padding: 10px 12px;
    border: 1px solid #fdba74;
    border-radius: 8px;
    font-size: 13px;
    background: #fff;
}
.cart-drawer-promo__input:focus {
    outline: none;
    border-color: #ea580c;
    box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15);
}
.cart-drawer-promo__submit {
    flex-shrink: 0;
    padding: 10px 14px;
    border: none;
    border-radius: 8px;
    background: #ea580c;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(234, 88, 12, 0.35);
    white-space: nowrap;
}
.cart-drawer-promo__submit:hover:not(:disabled) {
    background: #c2410c;
}
.cart-drawer-promo__submit:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}
.cart-drawer-promo--success {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #166534;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-top: 2px solid #86efac;
    border-bottom: 1px solid #bbf7d0;
}
.cart-drawer-promo--success strong {
    color: #15803d;
}
.cart-popup-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
    background: #fff;
    flex-shrink: 0;
}
.cart-popup-head__title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.cart-popup-head__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 9999px;
    background: #dcfce7;
    color: #16a34a;
    flex-shrink: 0;
}
.cart-popup-head__title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 700;
    line-height: 1.35;
    color: #111827;
}
.cart-popup-head__sub {
    margin: 2px 0 0;
    font-size: 0.875rem;
    color: #4b5563;
}
.cart-popup-head__close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #6b7280;
    cursor: pointer;
    flex-shrink: 0;
}
.cart-popup-head__close:hover {
    color: #111827;
    background: #f3f4f6;
}
.cart-drawer__scroll .cart-popup-body {
    padding: 16px 20px 8px;
}
.cart-popup-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.cart-popup-item {
    padding: 16px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    transition: box-shadow 0.2s ease;
}
.cart-popup-item:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}
.cart-popup-item__inner {
    display: flex;
    gap: 16px;
}
.cart-popup-item__media {
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    background: #f7f7f7;
}
.cart-popup-item__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cart-popup-item__media-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    background: #f3f4f6;
    color: #9ca3af;
}
.cart-popup-item__name {
    margin: 0 0 4px;
    font-size: 0.9375rem;
    font-weight: 600;
    line-height: 1.4;
    color: #111827;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.cart-popup-item__shop {
    margin: 0 0 8px;
    font-size: 0.75rem;
    color: #4b5563;
}
.cart-popup-item__shop a {
    color: #005366;
    font-weight: 500;
    text-decoration: none;
}
.cart-popup-item__shop a:hover {
    text-decoration: underline;
}
.cart-popup-item__variant {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    margin: 0 4px 4px 0;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    background: #f3f4f6;
    color: #4b5563;
}
.cart-popup-item__custom {
    margin-bottom: 8px;
    font-size: 0.75rem;
    color: #4b5563;
}
.cart-popup-item__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 12px;
}
.cart-popup-qty {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.cart-popup-qty__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    color: #111827;
    cursor: pointer;
    transition: background-color 0.2s ease, border-color 0.2s ease;
}
.cart-popup-qty__btn:hover:not(:disabled) {
    border-color: #005366;
    background: rgba(0, 83, 102, 0.06);
}
.cart-popup-qty__btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
.cart-popup-qty__value {
    min-width: 1.5rem;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
}
.cart-popup-item__price {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 700;
    color: #005366;
    text-align: right;
}
.cart-popup-item__unit {
    margin: 2px 0 0;
    font-size: 0.75rem;
    color: #9ca3af;
    text-align: right;
}
.cart-popup-item__remove {
    padding: 4px;
    border: 0;
    background: transparent;
    color: #9ca3af;
    cursor: pointer;
    border-radius: 8px;
    transition: color 0.2s ease, background-color 0.2s ease;
}
.cart-popup-item__remove:hover {
    color: #e2150c;
    background: rgba(226, 21, 12, 0.08);
}
.cart-popup-summary {
    padding: 0;
    background: transparent;
}
.cart-popup-summary__rows,
.cart-popup-summary__zone-label,
.cart-popup-summary__zone-select,
.cart-popup-summary__total {
    display: none;
}
.cart-popup-actions {
    padding: 10px 16px 12px;
    background: #fff;
}
.cart-popup-actions__buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
}
.cart-popup-actions__buttons .btn-cta,
.cart-popup-actions__buttons .btn-outline-petrol {
    flex: 1;
    min-height: 48px;
    justify-content: center;
    font-size: 0.9375rem;
}
.cart-popup-actions__continue {
    display: block;
    width: 100%;
    padding: 0;
    border: 0;
    background: transparent;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #005366;
    text-align: center;
    cursor: pointer;
    text-decoration: underline;
    text-underline-offset: 3px;
}
.cart-popup-actions__continue:hover {
    color: #003d4d;
}
.cart-popup-recs {
    padding: 8px 20px 24px;
    background: #fff;
}
.cart-popup-recs__title {
    margin: 0 0 12px;
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
}
.cart-popup-recs__grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}
.cart-popup-recs__empty {
    grid-column: 1 / -1;
    padding: 16px 0;
    text-align: center;
    font-size: 0.875rem;
    color: #9ca3af;
}
.cross-sell-product {
    display: flex;
    flex-direction: column;
    padding: 8px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.cross-sell-product:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
}
.cross-sell-product__media {
    position: relative;
    aspect-ratio: 1;
    margin-bottom: 8px;
    border-radius: 8px;
    overflow: hidden;
    background: #f7f7f7;
}
.cross-sell-product__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.cross-sell-product__badge {
    position: absolute;
    top: 4px;
    left: 4px;
    padding: 2px 6px;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    background: #005366;
    color: #fff;
}
.cross-sell-product__name {
    margin: 0 0 8px;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.35;
    color: #111827;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.cross-sell-product__footer {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 8px;
    margin-top: auto;
}
.cross-sell-product__price {
    font-size: 0.875rem;
    font-weight: 700;
    color: #005366;
}
.cross-sell-product__price-old {
    display: block;
    font-size: 0.6875rem;
    color: #9ca3af;
    text-decoration: line-through;
}
.cross-sell-add-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    border: 0;
    border-radius: 9999px;
    background: #e2150c;
    color: #fff;
    font-size: 0.6875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease;
}
.cross-sell-add-btn:hover {
    background: #c0120a;
}
.cart-popup-empty {
    padding: 32px 16px;
    text-align: center;
    font-size: 0.9375rem;
    color: #9ca3af;
}
.cart-popup-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
    padding: 48px 24px;
}
@media (max-width: 640px) {
    .cart-drawer {
        width: 100vw;
    }
    .cart-popup-head,
    .cart-drawer__scroll .cart-popup-body,
    .cart-popup-summary,
    .cart-popup-actions,
    .cart-popup-recs {
        padding-left: 16px;
        padding-right: 16px;
    }
    .cart-popup-actions__buttons {
        flex-direction: column;
    }
}

/* Smooth scrolling */
#thumbnail-container {
    scroll-behavior: smooth;
}

/* Gallery modal animations */
#gallery-modal {
    transition: opacity 0.3s ease-in-out;
}

#gallery-modal.hidden {
    opacity: 0;
    pointer-events: none;
}

/* Main Image Effects */
#main-image {
    transition: transform 0.3s ease-out, opacity 0.15s ease-in-out;
    cursor: zoom-in;
}

/* Hover Effects - Disabled for zoom effect */
.product-show-gallery__main:hover #main-image {
    /* Scale handled by JavaScript for zoom effect */
}

/* Zoom Icon Animation */
.group:hover .zoom-icon {
    animation: zoomPulse 1.5s infinite;
}

@keyframes zoomPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Image Counter Badge */
#image-counter {
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Loading Spinner */
#image-loading {
    backdrop-filter: blur(2px);
}

/* Smooth Image Transitions */
.image-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

/* Hover Border Effect */
.group:hover .hover-border {
    border-color: #005366;
    box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.1);
}

/* Thumbnail Hover Effects */
#thumbnail-container button:hover img {
    transform: scale(1.1);
    filter: brightness(1.1);
}

/* Gallery Modal Enhancements */
#gallery-modal img {
    transition: all 0.3s ease-in-out;
}

#gallery-modal:hover img {
    transform: scale(1.02);
}

/* Responsive Image Effects */
@media (max-width: 768px) {
    .group:hover #main-image {
        transform: scale(1.02);
    }
}
/* Returns Info Popup */
#returns-info-popup {
    animation: fadeInScale 0.2s ease-out;
}
@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: translateX(-50%) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) scale(1);
    }
}

/* Close popup when clicking outside */
.returns-popup-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 40;
}

/* Hide default select arrows - Force override */
select {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: none !important;
}

select::-ms-expand {
    display: none !important;
}

select::-webkit-appearance {
    -webkit-appearance: none !important;
}


/* Video Player Styles */
video#main-video,
video#modal-main-video {
    background-color: #000;
}

video#main-video::-webkit-media-controls-panel {
    background-color: rgba(0, 0, 0, 0.8);
}

video#modal-main-video::-webkit-media-controls-panel {
    background-color: rgba(0, 0, 0, 0.8);
}

/* Video Badge Animation */
.absolute.top-3.left-3 {
    animation: fadeInSlide 0.5s ease-out;
}

@keyframes fadeInSlide {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Video player responsive */
@media (max-width: 768px) {
    video#main-video,
    video#modal-main-video {
        max-height: 100%;
        object-fit: contain;
    }
}

/* Video Play Overlay */
#video-play-overlay {
    transition: opacity 0.3s ease-in-out;
}

#video-play-overlay:hover .w-20 {
    transform: scale(1.1);
}

/* Video Poster */
video[poster] {
    object-fit: cover;
}

/* Play button pulse animation */
@keyframes playPulse {
    0%, 100% { 
        transform: scale(1); 
        box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.4);
    }
    50% { 
        transform: scale(1.05); 
        box-shadow: 0 0 0 15px rgba(147, 51, 234, 0);
    }
}

#video-play-overlay .w-20 {
    animation: playPulse 2s infinite;
}

/* Video container hover */
#image-container:has(video) {
    cursor: pointer;
}

#image-container:has(video):hover #video-play-overlay .w-20 {
    transform: scale(1.15);
}

/* Mobile Stock Badge Responsive */
.mobile-stock-badge {
    display: none;
}

@media (max-width: 1023px) {
    .mobile-stock-badge {
        display: flex;
        background-color: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.2);
        backdrop-filter: blur(4px);
    }
    
    .mobile-stock-badge svg {
        width: 0.875rem;
        height: 0.875rem;
    }
    
    .mobile-stock-badge span {
        font-size: 0.75rem;
        font-weight: 600;
    }
}

@media (max-width: 640px) {
    .mobile-stock-badge {
        top: 0.5rem;
        left: 0.5rem;
        padding: 0.25rem 0.5rem;
    }
    
    .mobile-stock-badge svg {
        width: 0.75rem;
        height: 0.75rem;
    }
    
    .mobile-stock-badge span {
        font-size: 0.6875rem;
    }
}
</style>

<script>
// Global variables for variant selection
let selectedAttributes = {};
let variants = [];

// Helpers to ensure consistent attribute/value comparisons
const normalizeAttributeKey = (key) => {
    return (key ?? '').toString().trim();
};

const normalizeAttributeValue = (value) => {
    return (value ?? '').toString().trim();
};

// Gallery variables
let currentImageIndex = 0;
let allImages = [
    @if(!empty($allImages))
        @foreach($allImages as $index => $imageUrl)
            '{{ $imageUrl }}'{{ $index < count($allImages) - 1 ? ',' : '' }}
        @endforeach
    @endif
];

const VOLUME_DISCOUNT_TIERS = @json($volumeDiscountTiers ?? []);
let selectedVolumeTierIndex = null;

function getVolumeDiscountPercentForQuantity(qty) {
    if (!VOLUME_DISCOUNT_TIERS.length) {
        return 0;
    }
    let best = 0;
    VOLUME_DISCOUNT_TIERS.forEach(function (tier) {
        const min = parseInt(tier.min_quantity, 10) || 0;
        const pct = parseInt(tier.discount_percent, 10) || 0;
        if (qty >= min && pct > best) {
            best = pct;
        }
    });
    return best;
}

function applyVolumeDiscountDisplay() {
    const hint = document.getElementById('volume-discount-hint');
    if (!hint) {
        return;
    }
    const pct = getVolumeDiscountPercentForQuantity(getProductQuantity());
    if (pct > 0) {
        hint.textContent = pct + '% volume discount applied';
        hint.classList.remove('hidden');
    } else {
        hint.classList.add('hidden');
    }
}

function selectVolumeTier(index) {
    document.querySelectorAll('[data-volume-tier]').forEach(function (btn) {
        btn.classList.remove('is-selected');
    });
    const btn = document.querySelector('[data-volume-tier="' + index + '"]');
    if (!btn) {
        return;
    }
    btn.classList.add('is-selected');
    selectedVolumeTierIndex = index;
    const minQty = parseInt(btn.dataset.minQuantity, 10) || 1;
    const qtyEl = document.getElementById('product-quantity');
    if (qtyEl) {
        qtyEl.textContent = String(minQty);
    }
    applyVolumeDiscountDisplay();
}

// Helper function for showing alerts with SweetAlert2 or fallback
function showAlert(options) {
    if (typeof Swal !== 'undefined') {
        return Swal.fire(options);
    } else {
        // Fallback to native confirm/alert
        if (options.showCancelButton) {
            const result = confirm(options.text || options.html?.replace(/<[^>]*>/g, '') || '');
            return Promise.resolve({ isConfirmed: result });
        } else {
            alert((options.title ? options.title + '\n\n' : '') + (options.text || options.html?.replace(/<[^>]*>/g, '') || ''));
            return Promise.resolve({ isConfirmed: true });
        }
    }
}

// Buy Now Function - Add to cart and go to checkout (defined early to ensure availability)
window.buyNow = function buyNow() {
    // Validate required customizations first
    const validation = validateRequiredCustomizations();
    if (!validation.isValid) {
        const message = `<div class="text-left">
                <p class="mb-3 text-gray-600">Please fill in all required personalization fields:</p>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    ${validation.missingFields.map(field => `<li>${field}</li>`).join('')}
                </ul>
            </div>`;
        
        showAlert({
            icon: 'warning',
            title: 'Missing Information',
            html: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-3 rounded-lg'
            }
        });
        
        expandCustomization();
        
        const customizationContainer = document.getElementById('customization-container');
        if (customizationContainer) {
            setTimeout(() => {
                customizationContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
        return;
    }
    
    // Get selected variant and customizations
    const selectedVariant = getSelectedVariant();
    
    // Check if variant is out of stock
    if (selectedVariant && selectedVariant.quantity !== null && selectedVariant.quantity <= 0) {
        showAlert({
            icon: 'error',
            title: 'Out of Stock',
            text: 'This product is currently out of stock. Please choose another product.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-3 rounded-lg'
            }
        });
        return;
    }
    
    const totalPriceValue = getCartUnitPriceWithCustomizations();
    const customizations = getSelectedCustomizations();
    const aiRedesignImage = (customizations['AI Redesign'] && customizations['AI Redesign'].image)
        ? customizations['AI Redesign'].image
        : null;

    const productData = {
        id: {{ $product->id }},
        name: '{{ addslashes($product->name) }}',
        slug: '{{ $product->slug }}',
        price: totalPriceValue,
        image: aiRedesignImage || '@php
            if ($media && count($media) > 0) {
                if (is_string($media[0])) {
                    echo $media[0];
                } elseif (is_array($media[0])) {
                    echo $media[0]["url"] ?? $media[0]["path"] ?? reset($media[0]) ?? "";
                }
            }
        @endphp',
        shop: '{{ $product->shop->name ?? "Unknown Shop" }}',
        quantity: getProductQuantity(),
        selectedVariant: selectedVariant,
        customizations: customizations,
        addedAt: Date.now()
    };
    
    // Add to localStorage
    addToLocalCart(productData);
    
    // Track Facebook Pixel AddToCart
    if (typeof fbq !== 'undefined') {
        fbq('track', 'AddToCart', {
            content_name: productData.name,
            content_ids: [productData.id],
            content_type: 'product',
            value: totalPriceValue,
            currency: CURRENT_CURRENCY
        });
    }
    
    // Event tracking Ä‘Æ°á»£c xá»­ lÃ½ bá»Ÿi GTM thÃ´ng qua dataLayer
    if (typeof dataLayer !== 'undefined') {
        const gaItem = {
            item_id: '{{ $product->sku ?? $product->id }}',
            item_name: '{{ addslashes($product->name) }}',
            item_category: @json($primaryCategory),
            item_variant: selectedVariant && selectedVariant.attributes ? Object.values(selectedVariant.attributes).join(' / ') : undefined,
            price: totalPriceValue,
            quantity: getProductQuantity()
        };
        if (!gaItem.item_variant) {
            delete gaItem.item_variant;
        }
        dataLayer.push({
            'event': 'add_to_cart',
            'currency': CURRENT_CURRENCY,
            'value': totalPriceValue * getProductQuantity(),
            'items': [gaItem]
        });
    }

    if (typeof window !== 'undefined' && window.ttq) {
        const tiktokAddToCartPayload = {
            contents: [{
                content_id: TIKTOK_PRODUCT_ID,
                content_type: 'product',
                content_name: productData.name,
                quantity: productData.quantity || 1,
                price: totalPriceValue
            }],
            value: totalPriceValue * (productData.quantity || 1),
            currency: CURRENT_CURRENCY
        };

        if (TIKTOK_PRIMARY_CATEGORY) {
            tiktokAddToCartPayload.contents[0].content_category = TIKTOK_PRIMARY_CATEGORY;
        }

        if (selectedVariant && selectedVariant.attributes) {
            const variantLabel = Object.values(selectedVariant.attributes)
                .filter(Boolean)
                .join(' / ')
                .trim();
            if (variantLabel) {
                tiktokAddToCartPayload.contents[0].content_variant = variantLabel;
            }
        }

        window.ttq.track('AddToCart', tiktokAddToCartPayload);
    }
    
    // Sync with backend
    syncCartToBackend(productData)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                syncLocalStorageWithBackend();
            }
        })
        .catch(error => {
            console.log('Backend sync failed, proceeding anyway:', error);
        })
        .finally(() => {
            // Track InitiateCheckout
            if (typeof fbq !== 'undefined') {
                fbq('track', 'InitiateCheckout', {
                    content_ids: [productData.id],
                    content_type: 'product',
                    value: totalPriceValue,
                    currency: CURRENT_CURRENCY,
                    num_items: 1
                });
                
                console.log('âœ… Facebook Pixel: Buy Now - AddToCart & InitiateCheckout tracked');
            }

            if (typeof gtag === 'function') {
                const gaItem = {
                    item_id: '{{ $product->sku ?? $product->id }}',
                    item_name: '{{ addslashes($product->name) }}',
                    item_category: @json($primaryCategory),
                    item_variant: selectedVariant && selectedVariant.attributes ? Object.values(selectedVariant.attributes).join(' / ') : undefined,
                    price: totalPriceValue,
                    quantity: 1
                };
                if (!gaItem.item_variant) {
                    delete gaItem.item_variant;
                }

                // Event tracking Ä‘Æ°á»£c xá»­ lÃ½ bá»Ÿi GTM thÃ´ng qua dataLayer
                if (typeof dataLayer !== 'undefined') {
                    dataLayer.push({
                        'event': 'begin_checkout',
                        'currency': CURRENT_CURRENCY,
                        'value': totalPriceValue,
                        'items': [gaItem]
                    });

                    console.log('âœ… GTM: begin_checkout tracked from buyNow', {
                        value: totalPriceValue
                    });
                }
            }

            if (typeof window !== 'undefined' && window.ttq) {
                const tiktokCheckoutPayload = {
                    contents: [{
                        content_id: TIKTOK_PRODUCT_ID,
                        content_type: 'product',
                        content_name: productData.name,
                        quantity: productData.quantity || 1,
                        price: totalPriceValue
                    }],
                    value: totalPriceValue,
                    currency: CURRENT_CURRENCY
                };

                if (TIKTOK_PRIMARY_CATEGORY) {
                    tiktokCheckoutPayload.contents[0].content_category = TIKTOK_PRIMARY_CATEGORY;
                }

                if (selectedVariant && selectedVariant.attributes) {
                    const variantLabel = Object.values(selectedVariant.attributes)
                        .filter(Boolean)
                        .join(' / ')
                        .trim();
                    if (variantLabel) {
                        tiktokCheckoutPayload.contents[0].content_variant = variantLabel;
                    }
                }

                window.ttq.track('InitiateCheckout', tiktokCheckoutPayload);
            }
            
            // Redirect to checkout
            window.location.href = '{{ route("checkout.index") }}';
        });
}

// Video control function
function playVideoOnClick(event) {
    console.log('playVideoOnClick called', event);
    event.preventDefault();
    event.stopPropagation();
    
    const video = document.getElementById('main-video');
    const overlay = document.getElementById('video-play-overlay');
    
    console.log('Video element:', video);
    console.log('Overlay element:', overlay);
    
    if (video && overlay) {
        // Hide overlay immediately
        overlay.style.display = 'none';
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        overlay.style.pointerEvents = 'none';
        console.log('Overlay hidden immediately');
        
        // Play video
        video.play().then(() => {
            console.log('Video playing successfully');
        }).catch((error) => {
            console.log('Video play failed:', error);
            // Show overlay again if play failed
            overlay.style.display = 'flex';
            overlay.style.opacity = '1';
            overlay.style.visibility = 'visible';
            overlay.style.pointerEvents = 'auto';
        });
    }
}

// Generate video thumbnail from first frame
function generateVideoThumbnail(videoUrl, callback) {
    const video = document.createElement('video');
    video.crossOrigin = 'anonymous';
    video.preload = 'metadata';
    
    video.addEventListener('loadedmetadata', function() {
        // Seek to 1 second or 10% of duration, whichever is smaller
        const seekTime = Math.min(1, video.duration * 0.1);
        video.currentTime = seekTime;
    });
    
    video.addEventListener('seeked', function() {
        // Create canvas to capture frame
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Set canvas size to video size
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert canvas to data URL
        const thumbnailDataUrl = canvas.toDataURL('image/jpeg', 0.8);
        
        // Call callback with thumbnail
        if (callback) {
            callback(thumbnailDataUrl);
        }
    });
    
    video.addEventListener('error', function() {
        console.warn('Could not generate thumbnail for video:', videoUrl);
        if (callback) {
            callback(null);
        }
    });
    
    // Start loading video
    video.src = videoUrl;
}

// Set video thumbnail as poster
function setVideoThumbnail(videoElement, videoUrl) {
    if (!videoElement || !videoUrl) return;
    
    // Check if video already has a poster
    if (videoElement.poster && videoElement.poster.trim() !== '') {
        return; // Already has poster, don't override
    }
    
    generateVideoThumbnail(videoUrl, function(thumbnailDataUrl) {
        if (thumbnailDataUrl && videoElement) {
            videoElement.poster = thumbnailDataUrl;
            console.log('Video thumbnail generated and set as poster');
        }
    });
}

// Generate thumbnails for all videos on page load
function generateVideoThumbnailsOnLoad() {
    // Check if current media is video and needs thumbnail
    const mainVideo = document.getElementById('main-video');
    if (mainVideo && mainVideo.src && !mainVideo.poster) {
        setTimeout(() => {
            setVideoThumbnail(mainVideo, mainVideo.src);
        }, 1000);
    }
    
    // Generate thumbnails for video thumbnails in gallery
    document.querySelectorAll('#thumbnail-container button').forEach((btn, index) => {
        const mediaUrl = allImages[index];
        if (mediaUrl && (mediaUrl.includes('.mp4') || mediaUrl.includes('.mov') || mediaUrl.includes('.avi') || mediaUrl.includes('.webm'))) {
            // Check if thumbnail already shows video content
            const thumbnailImg = btn.querySelector('img');
            if (thumbnailImg) {
                // This is an image thumbnail, generate video thumbnail
                setTimeout(() => {
                    generateVideoThumbnail(mediaUrl, function(thumbnailDataUrl) {
                        if (thumbnailDataUrl) {
                            thumbnailImg.src = thumbnailDataUrl;
                            thumbnailImg.alt = 'Video thumbnail';
                        }
                    });
                }, 500 * (index + 1)); // Stagger the generation
            }
        }
    });
}

// Show overlay when video pauses/ends
function setupVideoControls() {
    const video = document.getElementById('main-video');
    const overlay = document.getElementById('video-play-overlay');
    
    if (video && overlay) {
        // Add click event to video element
        video.addEventListener('click', function(e) {
            e.stopPropagation();
            playVideoOnClick(e);
        });
        
        // Show overlay when paused/ended
        video.addEventListener('pause', () => {
            console.log('Video pause - showing overlay');
            overlay.style.display = 'flex';
            overlay.style.visibility = 'visible';
            overlay.style.pointerEvents = 'auto';
            overlay.style.opacity = '1';
        });
        
        video.addEventListener('ended', () => {
            console.log('Video ended - showing overlay');
            overlay.style.display = 'flex';
            overlay.style.visibility = 'visible';
            overlay.style.pointerEvents = 'auto';
            overlay.style.opacity = '1';
        });
        
        // Hide overlay when playing
        video.addEventListener('play', () => {
            console.log('Video play - hiding overlay');
            overlay.style.display = 'none';
            overlay.style.visibility = 'hidden';
            overlay.style.pointerEvents = 'none';
            overlay.style.opacity = '0';
        });
    }
}

// Initialize variants data when page loads
document.addEventListener('DOMContentLoaded', function() {
    const variantsDataElement = document.getElementById('variants-data');
    if (variantsDataElement) {
        variants = JSON.parse(variantsDataElement.textContent);
        
        // Set initial selections from first variant
        if (variants.length > 0) {
            const firstVariant = variants[0];
            selectedAttributes = { ...firstVariant.attributes };
            updateAllAttributeButtons();
            updateVariantSelection();
        } else {
            // No variants, check product stock
            const productStock = {{ $product->quantity ?? 0 }};
            updateStockStatusBadge(productStock);
        }
    }
    
    // Preload all images for smooth transitions
    preloadImages();
    
    // Initialize image effects
    initializeImageEffects();
    
    // Setup video controls and overlay
    setupVideoControls();
    
    // Add direct click handler for video
    const mainVideo = document.getElementById('main-video');
    if (mainVideo) {
        mainVideo.addEventListener('click', function(e) {
            console.log('Video clicked directly');
            playVideoOnClick(e);
        });
    }
    
    // Generate video thumbnails if needed
    generateVideoThumbnailsOnLoad();
    
    // Save current product to recently viewed
    saveToRecentlyViewed();
    
});


// Preload all images
function preloadImages() {
    if (allImages && allImages.length > 0) {
        allImages.forEach(imageUrl => {
            const img = new Image();
            img.src = imageUrl;
        });
    }
}

// Initialize image effects
function initializeImageEffects() {
    const mainImage = document.getElementById('main-image');
    const mainVideo = document.getElementById('main-video');
    const imageContainer = document.getElementById('image-container');
    
    if (mainImage && imageContainer) {
        // Add fade-in animation on load
        mainImage.addEventListener('load', function() {
            this.classList.add('image-fade-in');
        });
        
        // Add zoom effect on mouse move (only for images, not videos)
        imageContainer.addEventListener('mousemove', function(e) {
            // Don't apply zoom if video is visible
            if (mainVideo && !mainVideo.classList.contains('hidden')) {
                return;
            }
            
            const rect = imageContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Calculate percentage position
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            // Apply transform origin and scale
            mainImage.style.transformOrigin = `${xPercent}% ${yPercent}%`;
            mainImage.style.transform = 'scale(2)';
            mainImage.style.cursor = 'zoom-in';
        });
        
        // Reset transform on mouse leave
        imageContainer.addEventListener('mouseleave', function() {
            mainImage.style.transformOrigin = 'center center';
            mainImage.style.transform = 'scale(1)';
            mainImage.style.cursor = 'zoom-in';
        });
        
        // Click to open gallery modal (only for images, videos have their own controls)
        imageContainer.addEventListener('click', function(e) {
            if (e.target.closest('[data-tryon-open], .product-show-gallery__tryon, .product-show-gallery__wishlist, .product-show-gallery__nav')) {
                return;
            }
            // Don't open modal if clicking on video or video controls
            if (mainVideo && !mainVideo.classList.contains('hidden')) {
                // Check if click is on video element itself or video overlay
                if (e.target === mainVideo || 
                    mainVideo.contains(e.target) || 
                    e.target.closest('#video-play-overlay') ||
                    e.target.closest('video')) {
                    // Play video on click
                    playVideoOnClick(e);
                    return; // Don't open modal
                }
            }
            openGalleryModal();
        });
    }
}
function changeMainImage(mediaUrl, index = null) {
    const mainImage = document.getElementById('main-image');
    const mainVideo = document.getElementById('main-video');
    const imageContainer = document.getElementById('image-container');
    const imageLoading = document.getElementById('image-loading');
    const imageCounter = document.getElementById('image-counter');
    const videoOverlay = document.getElementById('video-play-overlay');
    
    // Check if media is video
    const isVideo = mediaUrl.includes('.mp4') || mediaUrl.includes('.mov') || mediaUrl.includes('.avi') || mediaUrl.includes('.webm');
    
    // Update current image index
    if (index !== null) {
        currentImageIndex = index;
    } else {
        currentImageIndex = allImages.indexOf(mediaUrl);
    }
    
    // Update image counter
    if (imageCounter) {
        imageCounter.textContent = currentImageIndex + 1;
    }
    
    if (isVideo) {
        // Get poster from first available image
        const posterImage = allImages.find(url => 
            !url.includes('.mp4') && !url.includes('.mov') && !url.includes('.avi') && !url.includes('.webm')
        );
        
        // Hide image, show video
        if (mainImage) {
            mainImage.classList.add('hidden');
        }
        if (mainVideo) {
            mainVideo.classList.remove('hidden');
            mainVideo.src = mediaUrl;
            if (posterImage) {
                mainVideo.poster = posterImage;
            } else {
                // Generate thumbnail from video if no poster image
                setTimeout(() => {
                    setVideoThumbnail(mainVideo, mediaUrl);
                }, 500);
            }
            mainVideo.load();
            
            // Show play overlay
            if (videoOverlay) {
                videoOverlay.style.display = 'flex';
                videoOverlay.style.opacity = '1';
            }
        } else {
            // Create video element if doesn't exist
            const videoEl = document.createElement('video');
            videoEl.id = 'main-video';
            videoEl.className = 'w-full h-full object-cover cursor-pointer';
            videoEl.controls = true;
            videoEl.playsinline = true;
            videoEl.addEventListener('click', function(e) {
                e.stopPropagation();
                playVideoOnClick(e);
            });
            if (posterImage) {
                videoEl.poster = posterImage;
            }
            videoEl.innerHTML = `<source src="${mediaUrl}" type="video/mp4">`;
            
            const container = mainImage.parentElement;
            container.insertBefore(videoEl, mainImage);
            mainImage.classList.add('hidden');
            
            // Create overlay if doesn't exist
            if (!videoOverlay) {
                const overlayEl = document.createElement('div');
                overlayEl.id = 'video-play-overlay';
                overlayEl.className = 'absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 transition-opacity cursor-pointer z-20';
                overlayEl.addEventListener('click', function(e) {
                    e.stopPropagation();
                    playVideoOnClick(e);
                });
                overlayEl.innerHTML = `
                    <div class="w-20 h-20 bg-white bg-opacity-90 rounded-full flex items-center justify-center shadow-2xl hover:bg-opacity-100 hover:scale-110 transition-all duration-300">
                        <svg class="w-10 h-10 text-[#005366] ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                        </svg>
                    </div>
                `;
                imageContainer.appendChild(overlayEl);
            }
            
            // Generate thumbnail from video if no poster image
            if (!posterImage) {
                setTimeout(() => {
                    setVideoThumbnail(videoEl, mediaUrl);
                }, 500);
            }
            
            // Setup video event listeners
            setupVideoControls();
        }
    } else {
        // Hide video, show image
        if (mainVideo) {
            mainVideo.classList.add('hidden');
            mainVideo.pause();
        }
        if (videoOverlay) {
            videoOverlay.style.display = 'none';
        }
        if (mainImage) {
            mainImage.classList.remove('hidden');
            
            // Show loading spinner
            if (imageLoading) {
                imageLoading.classList.remove('hidden');
            }
            
            // Create new image element for smooth transition
            const newImage = new Image();
            newImage.onload = function() {
                // Hide loading spinner
                if (imageLoading) {
                    imageLoading.classList.add('hidden');
                }
                
                // Update main image with fade effect
                mainImage.style.opacity = '0';
                setTimeout(() => {
                    mainImage.src = mediaUrl;
                    mainImage.style.opacity = '1';
                }, 150);
            };
            
            newImage.onerror = function() {
                // Hide loading spinner on error
                if (imageLoading) {
                    imageLoading.classList.add('hidden');
                }
                console.error('Failed to load image:', mediaUrl);
            };
            
            // Start loading the new image
            newImage.src = mediaUrl;
        }
    }
    
    // Update active thumbnail
    document.querySelectorAll('#thumbnail-container button').forEach((btn, btnIndex) => {
        if (btnIndex === currentImageIndex) {
            btn.classList.remove('border-gray-200');
            btn.classList.add('border-[#005366]');
        } else {
            btn.classList.remove('border-[#005366]');
            btn.classList.add('border-gray-200');
        }
    });
}
// Gallery Modal Functions
function openGalleryModal() {
    const modal = document.getElementById('gallery-modal');
    const imageCounter = document.getElementById('modal-image-counter');
    
    modal.classList.remove('hidden');
    imageCounter.textContent = currentImageIndex + 1;
    
    // Display current media (image or video)
    updateModalMedia(allImages[currentImageIndex]);
    
    // Update modal thumbnails
    updateModalThumbnails();
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeGalleryModal() {
    const modal = document.getElementById('gallery-modal');
    const modalVideo = document.getElementById('modal-main-video');
    
    // Pause video if playing
    if (modalVideo && !modalVideo.paused) {
        modalVideo.pause();
    }
    
    modal.classList.add('hidden');
    
    // Restore body scroll
    document.body.style.overflow = 'auto';
}

function previousImage() {
    currentImageIndex = currentImageIndex > 0 ? currentImageIndex - 1 : allImages.length - 1;
    updateModalImage();
}

function nextImage() {
    currentImageIndex = currentImageIndex < allImages.length - 1 ? currentImageIndex + 1 : 0;
    updateModalImage();
}

function navigateMainGallery(direction) {
    if (!allImages || allImages.length === 0) {
        return;
    }

    if (direction === 'prev') {
        currentImageIndex = currentImageIndex > 0 ? currentImageIndex - 1 : allImages.length - 1;
    } else {
        currentImageIndex = currentImageIndex < allImages.length - 1 ? currentImageIndex + 1 : 0;
    }

    changeMainImage(allImages[currentImageIndex], currentImageIndex);
}

function selectModalImage(imageUrl, index) {
    currentImageIndex = index;
    updateModalImage();
}

function updateModalImage() {
    const imageCounter = document.getElementById('modal-image-counter');
    
    // Update media (image or video)
    updateModalMedia(allImages[currentImageIndex]);
    
    imageCounter.textContent = currentImageIndex + 1;
    
    // Update modal thumbnails
    updateModalThumbnails();
    
    // Update main image and thumbnails
    changeMainImage(allImages[currentImageIndex], currentImageIndex);
}

// New function to handle both image and video in modal
function updateModalMedia(mediaUrl) {
    const modalImage = document.getElementById('modal-main-image');
    const modalVideo = document.getElementById('modal-main-video');
    const modalVideoSource = document.getElementById('modal-video-source');
    const modalMediaBadge = document.getElementById('modal-media-badge');
    
    const isVideo = mediaUrl.includes('.mp4') || mediaUrl.includes('.mov') || mediaUrl.includes('.avi') || mediaUrl.includes('.webm');
    
    if (isVideo) {
        // Get poster from first available image
        const posterImage = allImages.find(url => 
            !url.includes('.mp4') && !url.includes('.mov') && !url.includes('.avi') && !url.includes('.webm')
        );
        
        // Show video, hide image
        if (modalImage) {
            modalImage.classList.add('hidden');
        }
        if (modalVideo) {
            modalVideo.classList.remove('hidden');
            if (modalVideoSource) {
                modalVideoSource.src = mediaUrl;
            }
            if (posterImage) {
                modalVideo.poster = posterImage;
            }
            modalVideo.load();
        }
        if (modalMediaBadge) {
            modalMediaBadge.classList.remove('hidden');
            modalMediaBadge.classList.add('flex');
        }
    } else {
        // Show image, hide video
        if (modalVideo) {
            modalVideo.classList.add('hidden');
            modalVideo.pause();
        }
        if (modalImage) {
            modalImage.classList.remove('hidden');
            modalImage.src = mediaUrl;
        }
        if (modalMediaBadge) {
            modalMediaBadge.classList.add('hidden');
            modalMediaBadge.classList.remove('flex');
        }
    }
}

function updateModalThumbnails() {
    document.querySelectorAll('#gallery-modal .absolute.bottom-4 button').forEach((btn, index) => {
        if (index === currentImageIndex) {
            btn.classList.add('border-white');
            btn.classList.remove('border-transparent');
        } else {
            btn.classList.remove('border-white');
        btn.classList.add('border-transparent');
        }
    });
}

// Thumbnail scrolling — vertical on desktop, horizontal on mobile
function scrollThumbnails(direction) {
    const container = document.getElementById('thumbnail-container');
    if (!container) {
        return;
    }

    const isVertical = window.matchMedia('(min-width: 768px)').matches;
    const scrollAmount = isVertical ? 180 : 280;

    if (isVertical) {
        if (direction === 'up' || direction === 'left') {
            container.scrollTop -= scrollAmount;
        } else {
            container.scrollTop += scrollAmount;
        }
        return;
    }

    if (direction === 'left' || direction === 'up') {
        container.scrollLeft -= scrollAmount;
    } else {
        container.scrollLeft += scrollAmount;
    }
}

function syncProductShowStickyColumns() {
    if (typeof window.syncSiteHeaderMetrics === 'function') {
        window.syncSiteHeaderMetrics();
        return;
    }
    const header = document.getElementById('site-header');
    const stickyTop = header ? Math.ceil(header.getBoundingClientRect().height) + 8 : 88;
    document.documentElement.style.setProperty('--product-show-sticky-top', stickyTop + 'px');
}

document.addEventListener('DOMContentLoaded', syncProductShowStickyColumns);
window.addEventListener('load', syncProductShowStickyColumns);
window.addEventListener('resize', syncProductShowStickyColumns);
window.addEventListener('siteHeaderMetricsUpdated', syncProductShowStickyColumns);

// Keyboard navigation for gallery
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('gallery-modal');
    if (!modal.classList.contains('hidden')) {
        switch(e.key) {
            case 'Escape':
                closeGalleryModal();
                break;
            case 'ArrowLeft':
                previousImage();
                break;
            case 'ArrowRight':
                nextImage();
                break;
        }
    }
});

function selectAttribute(attributeName, value) {
            const normalizedAttribute = normalizeAttributeKey(attributeName);
            const normalizedValue = normalizeAttributeValue(value);
            
            if (normalizedAttribute !== attributeName) {
                delete selectedAttributes[attributeName];
            }
            
            selectedAttributes[normalizedAttribute] = normalizedValue;
            updateVariantSelection();
            updateAllAttributeButtons();
            
            // Update selected color name display if it's a color attribute
            if (normalizedAttribute === 'Color' || normalizedAttribute === 'Colour') {
                const selectedColorName = document.getElementById('selected-color-name');
                if (selectedColorName) {
                    selectedColorName.textContent = normalizedValue;
                }
            }
            if (normalizedAttribute === 'Size') {
                const selectedSizeName = document.getElementById('selected-size-name');
                if (selectedSizeName) {
                    selectedSizeName.textContent = normalizedValue;
                }
            }
        }

        // Legacy functions for backward compatibility
        function selectColor(color) {
            selectAttribute('Color', color);
        }
        
        function selectColour(color) {
            selectAttribute('Colour', color);
        }

        function selectSize(size) {
            selectAttribute('Size', size);
        }

function updateAllAttributeButtons() {
    document.querySelectorAll('.color-swatch').forEach(btn => {
        const attribute = normalizeAttributeKey(btn.dataset.attribute);
        const value = normalizeAttributeValue(btn.dataset.value);
        const selectedValue = normalizeAttributeValue(selectedAttributes[attribute]);

        if (selectedValue && selectedValue === value) {
            btn.classList.add('is-selected', 'border-[#005366]');
            btn.classList.remove('border-transparent', 'border-gray-300', 'hover:border-gray-300', 'hover:border-gray-400');
        } else {
            btn.classList.remove('is-selected', 'border-[#005366]', 'ring-2', 'ring-[#005366]', 'ring-offset-2', 'border-gray-300');
            btn.classList.add('border-transparent');
        }
    });
    
    // Update attribute buttons
    document.querySelectorAll('.attribute-option').forEach(btn => {
        const attribute = normalizeAttributeKey(btn.dataset.attribute);
        const value = normalizeAttributeValue(btn.dataset.value);
        const selectedValue = normalizeAttributeValue(selectedAttributes[attribute]);
        
        if (selectedValue && selectedValue === value) {
            btn.classList.add('border-[#005366]', 'bg-[#005366]', 'text-white');
            btn.classList.remove('border-gray-300', 'text-gray-600', 'text-gray-700', 'text-gray-900', 'bg-gray-50', 'text-[#005366]');
        } else {
            btn.classList.remove('border-[#005366]', 'bg-gray-50', 'text-[#005366]', 'bg-[#005366]', 'text-white');
            btn.classList.add('border-gray-300', 'text-gray-900');
        }
    });
    
    // Update dropdowns
    Object.keys(selectedAttributes).forEach(attributeKey => {
        const normalizedAttribute = normalizeAttributeKey(attributeKey);
        const selector = document.getElementById(`${normalizedAttribute.toLowerCase()}-selector`);
        if (selector) {
            selector.value = normalizeAttributeValue(selectedAttributes[attributeKey]) || '';
        }
    });
}

// Helper function to convert HTML to text
function htmlToText(html) {
    if (!html) return '';
    
    // Create a temporary div element
    const temp = document.createElement('div');
    temp.innerHTML = html;
    
    // Get text content (this automatically strips HTML tags)
    let text = temp.textContent || temp.innerText || '';
    
    // Decode HTML entities
    text = text.replace(/&nbsp;/g, ' ');
    text = text.replace(/&amp;/g, '&');
    text = text.replace(/&lt;/g, '<');
    text = text.replace(/&gt;/g, '>');
    text = text.replace(/&quot;/g, '"');
    text = text.replace(/&#39;/g, "'");
    
    // Clean up extra spaces
    text = text.replace(/\s+/g, ' ').trim();
    
    return text;
}

// Legacy functions for backward compatibility
function updateColorButtons() {
    updateAllAttributeButtons();
}

function updateSizeButtons() {
    updateAllAttributeButtons();
}
function updateVariantSelection() {
    // Find matching variant based on selected attributes
    const matchingVariant = variants.find(variant => {
        if (!variant.attributes) return false;
        
        // Check if all selected attributes match
        for (const [attribute, value] of Object.entries(selectedAttributes)) {
            const normalizedAttribute = normalizeAttributeKey(attribute);
            const normalizedSelectedValue = normalizeAttributeValue(value);
            const variantValueRaw = variant.attributes[normalizedAttribute] ?? variant.attributes[attribute];
            const normalizedVariantValue = normalizeAttributeValue(variantValueRaw);
            
            if (normalizedSelectedValue && normalizedVariantValue !== normalizedSelectedValue) {
                return false;
            }
        }
        
        // Check if variant has attributes that are not selected (should not match)
        for (const [attribute, value] of Object.entries(variant.attributes)) {
            const normalizedAttribute = normalizeAttributeKey(attribute);
            const normalizedVariantValue = normalizeAttributeValue(value);
            const selectedValueRaw = selectedAttributes[normalizedAttribute] ?? selectedAttributes[attribute];
            const normalizedSelectedValue = normalizeAttributeValue(selectedValueRaw);
            
            if (normalizedSelectedValue && normalizedSelectedValue !== normalizedVariantValue) {
                return false;
            }
        }
        
        return true;
    });
    
    if (matchingVariant) {
        // Update variant name
        let variantName = '';
        if (matchingVariant.variant_name) {
            variantName = matchingVariant.variant_name;
        } else if (matchingVariant.attributes && Object.keys(matchingVariant.attributes).length > 0) {
            const attrParts = [];
            for (const [key, value] of Object.entries(matchingVariant.attributes)) {
                attrParts.push(value);
            }
            variantName = attrParts.join(' - ');
        } else if (matchingVariant.color && matchingVariant.size) {
            variantName = `${matchingVariant.color} - ${matchingVariant.size}`;
        } else if (matchingVariant.size) {
            variantName = `Size: ${matchingVariant.size}`;
        } else if (matchingVariant.color) {
            variantName = `Color: ${matchingVariant.color}`;
        } else if (matchingVariant.colour) {
            variantName = `Colour: ${matchingVariant.colour}`;
        } else {
            variantName = 'Standard';
        }
        
        const selectedVariantNameEl = document.getElementById('selected-variant-name');
        if (selectedVariantNameEl) {
            selectedVariantNameEl.textContent = variantName;
        }
        
        // Update price
        const currencySymbol = typeof CURRENCY_SYMBOL !== 'undefined' ? CURRENCY_SYMBOL : '$';
        const variantPriceEl = document.getElementById('selected-variant-price');
        if (variantPriceEl) {
            variantPriceEl.textContent = `${currencySymbol}${parseFloat(matchingVariant.price).toFixed(2)}`;
        }
        
        // Update stock
        const stockElement = document.getElementById('selected-variant-stock');
        if (stockElement) {
            if (matchingVariant.quantity !== null) {
                stockElement.textContent = matchingVariant.quantity > 0 
                    ? `Stock: ${matchingVariant.quantity} available`
                    : 'Stock: Out of stock';
                stockElement.style.display = 'block';
            } else {
                stockElement.style.display = 'none';
            }
        }
        
        // Update Add to Cart button based on stock
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const cartText = document.getElementById('cart-text');
        if (addToCartBtn && cartText) {
            if (matchingVariant.quantity !== null && matchingVariant.quantity <= 0) {
                addToCartBtn.disabled = true;
                cartText.textContent = 'Out of stock';
                addToCartBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                addToCartBtn.disabled = false;
                cartText.textContent = 'Add to Cart';
                addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        
        // Update stock status badge
        updateStockStatusBadge(matchingVariant.quantity);
        
        // Update description
        const descElement = document.getElementById('selected-variant-description');
        if (descElement) {
            if (matchingVariant.description) {
                // Convert HTML to text before setting textContent
                descElement.textContent = htmlToText(matchingVariant.description);
                descElement.style.display = 'block';
            } else {
                descElement.style.display = 'none';
            }
        }
        
        // Update attributes
        const attributesElement = document.getElementById('selected-variant-attributes');
        if (attributesElement && matchingVariant.attributes) {
            attributesElement.innerHTML = '';
            Object.entries(matchingVariant.attributes).forEach(([key, value]) => {
                const attrDiv = document.createElement('div');
                attrDiv.className = 'text-xs';
                attrDiv.innerHTML = `
                    <span class="font-medium text-gray-600">${key.charAt(0).toUpperCase() + key.slice(1)}:</span>
                    <span class="text-gray-700">${value}</span>
                `;
                attributesElement.appendChild(attrDiv);
            });
        }
        
        // Update main image if variant has specific media
        if (matchingVariant.media && matchingVariant.media.length > 0) {
            const firstMediaUrl = Array.isArray(matchingVariant.media[0]) 
                ? matchingVariant.media[0].url 
                : matchingVariant.media[0];
            if (firstMediaUrl) {
                document.getElementById('main-image').src = firstMediaUrl;
                
                // Update thumbnail selection
                document.querySelectorAll('[onclick*="changeMainImage"]').forEach(btn => {
                    btn.classList.remove('border-[#005366]');
                    btn.classList.add('border-transparent');
                    
                    const img = btn.querySelector('img');
                    if (img && img.src === firstMediaUrl) {
                        btn.classList.add('border-[#005366]');
                        btn.classList.remove('border-transparent');
                    }
                });
            }
        }
        
        // Update main price display
        const mainPriceElement = document.getElementById('base-price');
        if (mainPriceElement) {
            mainPriceElement.textContent = `${CURRENCY_SYMBOL}${parseFloat(matchingVariant.price).toFixed(2)}`;
            if (matchingVariant.price_usd !== undefined) {
                mainPriceElement.dataset.price = matchingVariant.price_usd;
            }
            mainPriceElement.dataset.priceConverted = matchingVariant.price;
        }

        syncPdpFlashPrice(matchingVariant);
        syncPdpListPrice(matchingVariant);

        updateCustomizationPrice();
    } else {
        // No matching variant found, enable button and reset text
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const cartText = document.getElementById('cart-text');
        if (addToCartBtn && cartText) {
            addToCartBtn.disabled = false;
            cartText.textContent = 'Add to Cart';
            addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        
        // Update stock status badge to out of stock
        updateStockStatusBadge(0);
    }
}

function updateStockStatusBadge(quantity) {
    const inStock = quantity === null || quantity > 0;
    const engagementDot = document.getElementById('engagement-stock-dot');
    const engagementLabel = document.getElementById('engagement-stock-label');
    const engagementQty = document.getElementById('engagement-stock-qty');

    if (engagementLabel) {
        engagementLabel.textContent = inStock ? 'In Stock' : 'Out of Stock';
        engagementLabel.classList.toggle('is-out', !inStock);
    }

    if (engagementDot) {
        engagementDot.classList.toggle('is-out', !inStock);
    }

    if (engagementQty) {
        if (inStock && quantity !== null && quantity > 0) {
            engagementQty.textContent = ' - ' + quantity + ' items left';
        } else {
            engagementQty.textContent = '';
        }
    }

    const mobileBadge = document.getElementById('mobile-stock-badge');
    if (mobileBadge) {
        const stockText = mobileBadge.querySelector('span');
        const stockIcon = mobileBadge.querySelector('svg');
        if (stockText) {
            stockText.textContent = inStock ? 'In Stock' : 'Out of Stock';
            stockText.className = inStock ? 'text-sm font-medium text-green-700' : 'text-sm font-medium text-red-700';
        }
        if (stockIcon) {
            stockIcon.className = inStock ? 'w-4 h-4 text-green-600' : 'w-4 h-4 text-red-600';
        }
        mobileBadge.className = inStock
            ? 'absolute top-3 left-3 bg-green-100 rounded-full px-3 py-1.5 items-center space-x-2 pointer-events-none z-10 lg:hidden mobile-stock-badge flex'
            : 'absolute top-3 left-3 bg-red-100 rounded-full px-3 py-1.5 items-center space-x-2 pointer-events-none z-10 lg:hidden mobile-stock-badge flex';
    }
}

function getProductQuantity() {
    const el = document.getElementById('product-quantity');
    const parsed = parseInt(el ? el.textContent : '1', 10);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
}

function changeProductQuantity(delta) {
    const el = document.getElementById('product-quantity');
    if (!el) {
        return;
    }

    const selectedVariant = getSelectedVariant();
    const maxQty = selectedVariant && selectedVariant.quantity !== null && selectedVariant.quantity > 0
        ? selectedVariant.quantity
        : 99;

    let qty = getProductQuantity() + delta;
    if (qty < 1) {
        qty = 1;
    }
    if (qty > maxQty) {
        qty = maxQty;
    }

    el.textContent = String(qty);
    applyVolumeDiscountDisplay();
}

function toggleReturnsInfo() {
    const popup = document.getElementById('returns-info-popup');
    if (!popup) {
        return;
    }
    popup.classList.toggle('hidden');
}

// Recently Viewed Functions
function saveToRecentlyViewed() {
    const currentProduct = {
        id: {{ $product->id }},
        slug: '{{ $product->slug }}',
        name: '{{ addslashes($product->name) }}',
        price: {{ $productPriceUSD }},
        originalPrice: {{ $productListPriceUSD }},
        image: '@php
            if ($media && count($media) > 0) {
                if (is_string($media[0])) {
                    echo $media[0];
                } elseif (is_array($media[0])) {
                    echo $media[0]["url"] ?? $media[0]["path"] ?? reset($media[0]) ?? "";
                }
            }
        @endphp',
        shop: '{{ $product->shop->name ?? "Unknown Shop" }}',
        shop_slug: '{{ $product->shop->shop_slug ?? "" }}',
        timestamp: Date.now()
    };
    
    // Get existing recently viewed products
    let recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
    
    // Remove current product if it exists
    recentlyViewed = recentlyViewed.filter(p => p.id !== currentProduct.id);
    
    // Add current product to the beginning
    recentlyViewed.unshift(currentProduct);
    
    // Keep only last 10 products in history (but only show 5)
    recentlyViewed = recentlyViewed.slice(0, 10);
    
    // Save back to localStorage
    localStorage.setItem('recentlyViewed', JSON.stringify(recentlyViewed));
}

function loadRecentlyViewed() {
    // Recently viewed is handled by partials/recently-viewed-section.blade.php
}

function closeReturnsInfo() {
    const popup = document.getElementById('returns-info-popup');
    if (!popup) {
        return;
    }
    popup.classList.add('hidden');
}

// Close popup on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReturnsInfo();
    }
});

// Cart Functions
function addToCart() {
    console.log('addToCart function called');
    const btn = document.getElementById('add-to-cart-btn');
    const cartIcon = document.getElementById('cart-icon');
    const cartText = document.getElementById('cart-text');
    const cartLoading = document.getElementById('cart-loading');
    
    // Check if required elements exist
    if (!btn) {
        console.error('Add to cart button not found');
        return;
    }
    
    console.log('Button found, checking variant...');
    
    // Validate required customizations first
    const validation = validateRequiredCustomizations();
    if (!validation.isValid) {
        const message = `<div class="text-left">
                <p class="mb-3 text-gray-600">Please fill in all required personalization fields:</p>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    ${validation.missingFields.map(field => `<li>${field}</li>`).join('')}
                </ul>
            </div>`;
        
        showAlert({
            icon: 'warning',
            title: 'Missing Information',
            html: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-3 rounded-lg'
            }
        });
        
        expandCustomization();
        
        const customizationContainer = document.getElementById('customization-container');
        if (customizationContainer) {
            setTimeout(() => {
                customizationContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
        return;
    }
    
    // Get selected variant to check quantity first
    const selectedVariant = getSelectedVariant();
    console.log('Selected variant:', selectedVariant);
    
    // Check if variant is out of stock
    if (selectedVariant && selectedVariant.quantity !== null && selectedVariant.quantity <= 0) {
        showAlert({
            icon: 'error',
            title: 'Out of Stock',
            text: 'This product is currently out of stock. Please choose another product.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-3 rounded-lg'
            }
        });
        return;
    }
    
    console.log('Variant check passed, proceeding...');
    
    // Disable button and show loading
    btn.disabled = true;
    if (cartIcon) cartIcon.classList.add('hidden');
    if (cartLoading) cartLoading.classList.remove('hidden');
    if (cartText) cartText.textContent = 'Adding...';
    
    const totalPriceValue = getCartUnitPriceWithCustomizations();
    const customizations = getSelectedCustomizations();
    const aiRedesignImage = (customizations['AI Redesign'] && customizations['AI Redesign'].image)
        ? customizations['AI Redesign'].image
        : null;
    
    // Get current product data
    const productData = {
        id: {{ $product->id }},
        name: '{{ addslashes($product->name) }}',
        slug: '{{ $product->slug }}',
        price: totalPriceValue,
        image: aiRedesignImage || '@php
            if ($media && count($media) > 0) {
                if (is_string($media[0])) {
                    echo $media[0];
                } elseif (is_array($media[0])) {
                    echo $media[0]["url"] ?? $media[0]["path"] ?? reset($media[0]) ?? "";
                }
            }
        @endphp',
        shop: '{{ $product->shop->name ?? "Unknown Shop" }}',
        quantity: getProductQuantity(),
        selectedVariant: selectedVariant,
        customizations: customizations,
        addedAt: Date.now()
    };
    
    // Add to localStorage immediately for fast UX
    addToLocalCart(productData);
    
    // Track Facebook Pixel AddToCart event
    if (typeof fbq !== 'undefined') {
        fbq('track', 'AddToCart', {
            content_name: productData.name,
            content_ids: [productData.id],
            content_type: 'product',
            value: totalPriceValue,
            currency: CURRENT_CURRENCY
        });
    }

    // Event tracking Ä‘Æ°á»£c xá»­ lÃ½ bá»Ÿi GTM thÃ´ng qua dataLayer
    if (typeof dataLayer !== 'undefined') {
        const gaItem = {
            item_id: '{{ $product->sku ?? $product->id }}',
            item_name: '{{ addslashes($product->name) }}',
            item_category: @json($primaryCategory),
            item_variant: selectedVariant && selectedVariant.attributes ? Object.values(selectedVariant.attributes).join(' / ') : undefined,
            price: totalPriceValue,
            quantity: getProductQuantity()
        };
        if (!gaItem.item_variant) {
            delete gaItem.item_variant;
        }
        dataLayer.push({
            'event': 'add_to_cart',
            'currency': CURRENT_CURRENCY,
            'value': totalPriceValue * getProductQuantity(),
            'items': [gaItem]
        });
    }

    if (typeof window !== 'undefined' && window.ttq) {
        const tiktokAddToCartPayload = {
            contents: [{
                content_id: TIKTOK_PRODUCT_ID,
                content_type: 'product',
                content_name: productData.name,
                quantity: productData.quantity || 1,
                price: totalPriceValue
            }],
            value: totalPriceValue * (productData.quantity || 1),
            currency: CURRENT_CURRENCY
        };

        if (TIKTOK_PRIMARY_CATEGORY) {
            tiktokAddToCartPayload.contents[0].content_category = TIKTOK_PRIMARY_CATEGORY;
        }

        if (selectedVariant && selectedVariant.attributes) {
            const variantLabel = Object.values(selectedVariant.attributes)
                .filter(Boolean)
                .join(' / ')
                .trim();
            if (variantLabel) {
                tiktokAddToCartPayload.contents[0].content_variant = variantLabel;
            }
        }

        window.ttq.track('AddToCart', tiktokAddToCartPayload);
    }
    
    // Try to sync with backend
    syncCartToBackend(productData)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success - sync localStorage with backend
                syncLocalStorageWithBackend();
                showCartSuccess();
            } else {
                console.error('Backend sync failed:', data.message);
                showCartSuccess('Cart saved locally');
            }
        })
        .catch((error) => {
            console.log('Network error - cart saved locally:', error);
            showCartSuccess('Cart saved locally');
        })
        .finally(() => {
            console.log('Finally block executed, showing popup...');
            // Reset button safely
            btn.disabled = false;
            if (cartIcon) cartIcon.classList.remove('hidden');
            if (cartLoading) cartLoading.classList.add('hidden');
            if (cartText) cartText.textContent = 'Add to Cart';
            
            // Show cart popup
            console.log('About to show cart popup with productData:', productData);
            showCartPopup(productData);
            handlePostAddToCartPromo();
        });
}

function addToLocalCart(productData) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    // Check if product already exists in cart using same logic as backend
    const existingIndex = cart.findIndex(item => {
        if (item.id !== productData.id) return false;
        
        // Compare variants using attributes
        const variantMatch = compareVariantsLocal(item.selectedVariant, productData.selectedVariant);
        const customizationMatch = compareCustomizationsLocal(item.customizations, productData.customizations);
        
        return variantMatch && customizationMatch;
    });
    
    if (existingIndex !== -1) {
        cart[existingIndex].quantity += productData.quantity || 1;
        cart[existingIndex].price = productData.price;
        if (productData.customizations) {
            cart[existingIndex].customizations = productData.customizations;
        }
    } else {
        // Add new item
        cart.push(productData);
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

function compareVariantsLocal(variant1, variant2) {
    // Compare attributes if both have them
    if (variant1 && variant1.attributes && variant2 && variant2.attributes) {
        // Sort keys for consistent comparison
        const sorted1 = Object.keys(variant1.attributes).sort().reduce((result, key) => {
            result[key] = variant1.attributes[key];
            return result;
        }, {});
        const sorted2 = Object.keys(variant2.attributes).sort().reduce((result, key) => {
            result[key] = variant2.attributes[key];
            return result;
        }, {});
        
        return JSON.stringify(sorted1) === JSON.stringify(sorted2);
    }
    
    // Fallback: compare entire objects
    return JSON.stringify(variant1) === JSON.stringify(variant2);
}

function compareCustomizationsLocal(custom1, custom2) {
    return JSON.stringify(custom1 || {}) === JSON.stringify(custom2 || {});
}

function syncCartToBackend(productData) {
    return fetch('/api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(productData)
    });
}

function syncPdpFlashPrice(variant) {
    const row = document.querySelector('.product-show-purchase__price-row--flash');
    if (!row || !variant) {
        return;
    }

    const origEl = document.getElementById('flash-original-price');
    const saveEl = document.getElementById('flash-save-badge');
    const saleUsd = parseFloat(variant.price_usd != null ? variant.price_usd : variant.price) || 0;
    const listUsd = parseFloat(variant.list_price_usd != null ? variant.list_price_usd : 0) || 0;
    const listConverted = parseFloat(variant.list_price != null ? variant.list_price : 0) || 0;
    const currencySymbol = typeof CURRENCY_SYMBOL !== 'undefined' ? CURRENCY_SYMBOL : '$';
    const pct = parseInt(variant.flash_percent, 10) || 0;

    if (origEl) {
        if (listUsd > saleUsd && listConverted > 0) {
            origEl.textContent = `${currencySymbol}${listConverted.toFixed(2)}`;
            origEl.dataset.price = String(listUsd);
            origEl.dataset.priceConverted = String(listConverted);
            origEl.classList.remove('hidden');
        } else {
            origEl.classList.add('hidden');
        }
    }

    if (saveEl && pct > 0) {
        saveEl.textContent = `${pct}% OFF`;
        saveEl.classList.remove('hidden');
    }

    const savingsEl = document.querySelector('.product-show-flash-sale__savings strong');
    if (savingsEl && listUsd > saleUsd) {
        savingsEl.textContent = `${currencySymbol}${(listUsd - saleUsd).toFixed(2)}`;
    }
}

function syncPdpListPrice(variant) {
    if (document.querySelector('.product-show-purchase__price-row--flash')) {
        return;
    }

    const listEl = document.getElementById('list-price');
    const saveEl = document.getElementById('list-save-badge');
    if (!listEl) {
        return;
    }

    const saleUsd = parseFloat(variant.price_usd != null ? variant.price_usd : variant.price) || 0;
    const listUsd = parseFloat(variant.list_price_usd != null ? variant.list_price_usd : 0) || 0;
    const listConverted = parseFloat(variant.list_price != null ? variant.list_price : listEl.dataset.priceConverted) || 0;
    const currencySymbol = typeof CURRENCY_SYMBOL !== 'undefined' ? CURRENCY_SYMBOL : '$';

    if (listUsd > saleUsd && listConverted > 0) {
        listEl.textContent = `${currencySymbol}${listConverted.toFixed(2)}`;
        listEl.dataset.price = String(listUsd);
        listEl.dataset.priceConverted = String(listConverted);
        listEl.classList.remove('hidden');
        if (saveEl) {
            const pct = Math.round(((listUsd - saleUsd) / listUsd) * 100);
            saveEl.textContent = `Save ${pct}%`;
            saveEl.classList.toggle('hidden', pct <= 0);
        }
    } else {
        listEl.classList.add('hidden');
        if (saveEl) {
            saveEl.classList.add('hidden');
        }
    }
}

function getSelectedVariant() {
    const variantsDataElement = document.getElementById('variants-data');
    if (!variantsDataElement) return null;
    
    const parsedVariants = JSON.parse(variantsDataElement.textContent);
    
    // Find matching variant based on selected attributes
    const matchingVariant = parsedVariants.find(variant => {
        if (!variant.attributes) return false;
        
        // Check if all selected attributes match
        for (const [attribute, value] of Object.entries(selectedAttributes)) {
            if (variant.attributes[attribute] !== value) {
                return false;
            }
        }
        
        return true;
    });
    
    // Return full variant info including price
    const variant = matchingVariant || parsedVariants[0] || null;
    return variant ? { 
        id: variant.id,
        attributes: variant.attributes,
        price: variant.price,
        price_usd: variant.price_usd,
        list_price: variant.list_price,
        list_price_usd: variant.list_price_usd,
        quantity: variant.quantity,
        variant_name: variant.variant_name,
        media: variant.media
    } : null;
}

function getCurrentUnitBasePrice() {
    const basePriceEl = document.getElementById('base-price');
    if (basePriceEl) {
        const converted = parseFloat(basePriceEl.dataset.priceConverted);
        if (!Number.isNaN(converted)) {
            return converted;
        }
    }

    const selectedVariant = getSelectedVariant();
    if (selectedVariant && selectedVariant.price != null) {
        return Number(selectedVariant.price) || 0;
    }

    return Number({{ $productPriceConverted }}) || 0;
}

function getCartUnitPriceWithCustomizations() {
    const basePrice = getCurrentUnitBasePrice();
    const customizations = getSelectedCustomizations();
    let customizationTotal = 0;

    Object.values(customizations).forEach(customization => {
        customizationTotal += parseFloat(customization.price) || 0;
    });

    return Math.round((basePrice + customizationTotal + Number.EPSILON) * 100) / 100;
}

function getSelectedCustomizations() {
    const customizations = {};
    const inputs = document.querySelectorAll('.customization-input');
    
    inputs.forEach(input => {
        const label = input.dataset.label || input.name;
        
        if (input.type === 'radio' && input.checked) {
            customizations[label] = {
                value: input.value,
                price: parseFloat(input.dataset.price) || 0
            };
        } else if (input.type === 'checkbox' && input.checked) {
            customizations[label] = {
                value: input.value,
                price: parseFloat(input.dataset.price) || 0
            };
        } else if (input.type === 'file') {
            if (input.files && input.files.length > 0) {
                customizations[label] = {
                    value: input.files[0].name,
                    price: parseFloat(input.dataset.price) || 0
                };
            }
        } else if (input.type === 'text' || input.type === 'number' || input.tagName === 'TEXTAREA') {
            if (input.value.trim() !== '') {
                customizations[label] = {
                    value: input.value.trim(),
                    price: parseFloat(input.dataset.price) || 0
                };
            }
        }
    });

    if (typeof window.getAiRedesignCustomization === 'function') {
        const aiCustom = window.getAiRedesignCustomization();
        if (aiCustom && aiCustom.image) {
            customizations['AI Redesign'] = aiCustom;
            customizations._ai = {
                prompt: aiCustom.prompt || '',
                design_url: aiCustom.image,
                photo_url: aiCustom.photo_url || null,
                source: 'ai_redesign',
            };
        }
    }
    
    return customizations;
}

function isCustomizationExpanded() {
    const container = document.getElementById('customization-container');
    return container && !container.classList.contains('is-collapsed');
}

function setCustomizationExpanded(expanded) {
    const section = document.getElementById('product-show-customization');
    const container = document.getElementById('customization-container');
    const toggle = document.getElementById('customization-toggle');

    if (!container) {
        return;
    }

    if (section && section.dataset.required === 'true') {
        expanded = true;
    }

    container.classList.toggle('is-collapsed', !expanded);

    if (toggle) {
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        const label = toggle.querySelector('[data-toggle-label]');
        if (label) {
            label.textContent = expanded ? 'Hide' : 'Show';
        }
    }

    if (expanded) {
        updateCustomizationPrice();
    } else {
        clearOptionalCustomizationInputs();
        const priceDisplay = document.getElementById('customization-price-display');
        if (priceDisplay) {
            priceDisplay.classList.add('hidden');
        }
    }
}

function expandCustomization() {
    setCustomizationExpanded(true);
}

function clearOptionalCustomizationInputs() {
    const container = document.getElementById('customization-container');
    if (!container) {
        return;
    }

    container.querySelectorAll('.customization-input').forEach(input => {
        if (input.type === 'radio' || input.type === 'checkbox') {
            input.checked = false;
        } else if (input.type === 'file') {
            input.value = '';
        } else {
            input.value = '';
        }
    });
}

function clearCustomizationFieldErrors() {
    document.querySelectorAll('.product-show-customization__box').forEach(box => {
        box.classList.remove('is-invalid');
        const error = box.querySelector('[data-field-error]');
        if (error) {
            error.textContent = '';
            error.classList.add('hidden');
        }
    });
}

function markCustomizationFieldInvalid(box, message) {
    box.classList.add('is-invalid');
    const error = box.querySelector('[data-field-error]');
    if (error) {
        error.textContent = message;
        error.classList.remove('hidden');
    }
}

function validateRequiredCustomizations() {
    const container = document.getElementById('customization-container');

    if (!container) {
        return { isValid: true, missingFields: [] };
    }

    clearCustomizationFieldErrors();

    if (!isCustomizationExpanded()) {
        const hasRequired = container.querySelector('.product-show-customization__box[data-required="true"]');
        if (hasRequired) {
            expandCustomization();
        }
    }

    const missingFields = [];
    const requiredBoxes = container.querySelectorAll('.product-show-customization__box[data-required="true"]');

    requiredBoxes.forEach(box => {
        const label = box.dataset.fieldLabel || 'Personalization field';
        let hasValidInput = false;
        const inputs = box.querySelectorAll('.customization-input');

        inputs.forEach(input => {
            if (input.type === 'radio' || input.type === 'checkbox') {
                if (input.checked) {
                    hasValidInput = true;
                }
            } else if (input.type === 'file') {
                if (input.files && input.files.length > 0) {
                    hasValidInput = true;
                }
            } else if (input.value.trim() !== '') {
                hasValidInput = true;
            }
        });

        if (!hasValidInput && inputs.length > 0 && inputs[0].type === 'radio') {
            const radioName = inputs[0].name;
            hasValidInput = container.querySelector(`input[name="${radioName}"]:checked`) !== null;
        }

        if (!hasValidInput) {
            missingFields.push(label);
            markCustomizationFieldInvalid(box, 'This field is required.');
        }
    });

    return {
        isValid: missingFields.length === 0,
        missingFields: missingFields,
        needToEnableCustomization: false
    };
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    // Update cart count in header if exists
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = totalItems;
        element.style.display = totalItems > 0 ? 'flex' : 'none';
    });
    
    // Dispatch custom event to update header
    window.dispatchEvent(new CustomEvent('cartUpdated'));
}

function showCartSuccess(message = 'Added to cart successfully!') {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-[60] flex items-center gap-2 px-4 py-3 rounded-xl shadow-lg border border-green-100 bg-green-50 text-green-700';
    notification.innerHTML = `
        <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span class="text-sm font-semibold">${message}</span>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function hasCartPromoBeenClaimedLocally() {
    try {
        return sessionStorage.getItem(CART_PROMO_CLAIMED_KEY) === '1';
    } catch (e) {
        return false;
    }
}

function markCartPromoClaimedLocally() {
    try {
        sessionStorage.setItem(CART_PROMO_CLAIMED_KEY, '1');
    } catch (e) {}
}

function resolveSelectedShippingZoneId() {
    let selectedZoneId = localStorage.getItem('selectedShippingZoneId');

    if (selectedZoneId) {
        let zoneIdToCheck = selectedZoneId;
        if (selectedZoneId.includes(':')) {
            zoneIdToCheck = selectedZoneId.split(':')[0];
        }

        if (zoneIdToCheck.toString().startsWith('general_')) {
            const existsInZonesWithCountries = SHIPPING_ZONES_WITH_COUNTRIES && SHIPPING_ZONES_WITH_COUNTRIES.some(z => z.id === zoneIdToCheck);
            const existsInZones = SHIPPING_ZONES && SHIPPING_ZONES.some(z => z.id === zoneIdToCheck);
            if (!existsInZonesWithCountries && !existsInZones) {
                selectedZoneId = DEFAULT_SHIPPING_ZONE_ID;
            }
        } else {
            const parsed = parseInt(zoneIdToCheck);
            if (!isNaN(parsed)) {
                const existsInZonesWithCountries = SHIPPING_ZONES_WITH_COUNTRIES && SHIPPING_ZONES_WITH_COUNTRIES.some(z => z.id === parsed);
                const existsInZones = SHIPPING_ZONES && SHIPPING_ZONES.some(z => z.id === parsed);
                if (!existsInZonesWithCountries && !existsInZones) {
                    selectedZoneId = DEFAULT_SHIPPING_ZONE_ID;
                }
            } else {
                selectedZoneId = DEFAULT_SHIPPING_ZONE_ID;
            }
        }
    } else {
        selectedZoneId = DEFAULT_SHIPPING_ZONE_ID;
    }

    return selectedZoneId;
}

function getShippingZoneDisplayLabel(zoneId, shippingInfo) {
    if (AUTH_USER?.country) {
        return AUTH_USER.country;
    }

    if (zoneId && typeof zoneId === 'string' && zoneId.includes(':')) {
        for (const zone of (SHIPPING_ZONES_WITH_COUNTRIES || [])) {
            for (const country of (zone.country_options || [])) {
                if (country.value === zoneId) {
                    return country.label;
                }
            }
        }
    }

    if (zoneId != null) {
        const parsed = typeof zoneId === 'string' && !zoneId.toString().startsWith('general_') && !isNaN(zoneId)
            ? parseInt(zoneId, 10)
            : zoneId;
        const match = (SHIPPING_ZONES || []).find(z => z.id === parsed);
        if (match) {
            return match.display_name || match.name;
        }
    }

    return shippingInfo?.zoneName || shippingInfo?.name || 'Standard';
}

function qualifiesForFreeShipping(baseSubtotalUsd) {
    return baseSubtotalUsd >= FREE_SHIPPING_THRESHOLD_USD;
}

function applyFreeShippingToCost(shippingInfo, baseSubtotalUsd) {
    if (!shippingInfo || shippingInfo.available === false) {
        return { costConverted: 0, isFree: false, unavailable: true };
    }

    if (qualifiesForFreeShipping(baseSubtotalUsd)) {
        return { costConverted: 0, isFree: true, unavailable: false };
    }

    return { costConverted: shippingInfo.costConverted, isFree: false, unavailable: false };
}

function buildShippingZoneOptionsHtml(selectedZoneId) {
    let html = '';

    if (SHIPPING_ZONES_WITH_COUNTRIES && SHIPPING_ZONES_WITH_COUNTRIES.length > 0) {
        html += SHIPPING_ZONES_WITH_COUNTRIES.map(zone => `
            <optgroup label="${zone.name}">
                ${zone.country_options.map(country => `
                    <option value="${country.value}" ${selectedZoneId === country.value || (selectedZoneId === country.zone_id && !selectedZoneId?.toString().includes(':')) ? 'selected' : ''}>
                        ${country.label}
                    </option>
                `).join('')}
            </optgroup>
        `).join('');
    }

    if (SHIPPING_ZONES && SHIPPING_ZONES.length > 0) {
        html += SHIPPING_ZONES.map(zone => `
            <option value="${zone.id}" ${selectedZoneId === zone.id ? 'selected' : ''}>
                ${zone.display_name || zone.name}
            </option>
        `).join('');
    }

    return html;
}

function hasShippingZonePicker() {
    return (SHIPPING_ZONES && SHIPPING_ZONES.length > 0)
        || (SHIPPING_ZONES_WITH_COUNTRIES && SHIPPING_ZONES_WITH_COUNTRIES.length > 0);
}

function shouldHideShippingZonePicker() {
    return !!AUTH_USER?.country;
}

function renderFreeShippingBar(baseSubtotalUsd, currency, currencyRate) {
    const threshold = FREE_SHIPPING_THRESHOLD_USD;
    const progress = Math.min(100, (baseSubtotalUsd / threshold) * 100);
    const remainingUsd = Math.max(0, threshold - baseSubtotalUsd);
    const remainingDisplay = currency !== 'USD' && currencyRate > 0
        ? remainingUsd * currencyRate
        : remainingUsd;

    if (qualifiesForFreeShipping(baseSubtotalUsd)) {
        return `
            <div class="cart-drawer-freeship cart-drawer-freeship--done" id="cart-popup-freeship">
                <span class="cart-drawer-freeship__icon" aria-hidden="true">✓</span>
                <span>You&apos;ve unlocked free shipping!</span>
            </div>
        `;
    }

    return `
        <div class="cart-drawer-freeship" id="cart-popup-freeship">
            <p class="cart-drawer-freeship__text">Add <strong>${CURRENCY_SYMBOL}${remainingDisplay.toFixed(2)}</strong> more for <strong>FREE SHIPPING</strong></p>
            <div class="cart-drawer-freeship__track" aria-hidden="true">
                <span class="cart-drawer-freeship__fill" id="cart-popup-freeship-fill" style="width: ${progress.toFixed(1)}%"></span>
            </div>
        </div>
    `;
}

function renderCartDrawerBill({
    totalItems,
    subtotal,
    baseSubtotalUsd,
    shippingInfo,
    shippingCost,
    shippingIsFree,
    totalPrice,
    currency,
    currencyRate,
    selectedZoneId,
    zoneLabel,
}) {
    const itemLabel = totalItems === 1 ? 'item' : 'items';
    const hideZonePicker = shouldHideShippingZonePicker();
    const showZoneEdit = hasShippingZonePicker() && !hideZonePicker;

    let shippingRowHtml = '';
    if (shippingInfo.available === false) {
        shippingRowHtml = `
            <div class="cart-drawer-bill__row cart-drawer-bill__row--error">
                <span id="cart-popup-shipping-label">Shipping not available for this area</span>
                <span class="cart-drawer-bill__row-value cart-drawer-bill__row--error" id="cart-popup-shipping-value">N/A</span>
            </div>
        `;
    } else {
        const shippingLabelText = hideZonePicker
            ? 'Shipping'
            : `Shipping — ${zoneLabel}`;
        const shippingValueText = shippingIsFree
            ? 'FREE'
            : `${CURRENCY_SYMBOL}${shippingCost.toFixed(2)}`;
        const valueClass = shippingIsFree ? 'cart-drawer-bill__row-value cart-drawer-bill__row-value--free' : 'cart-drawer-bill__row-value';

        shippingRowHtml = `
            <div class="cart-drawer-bill__row">
                <span class="cart-drawer-bill__shipping-label" id="cart-popup-shipping-label">
                    ${shippingLabelText}
                    ${showZoneEdit ? `
                        <button type="button" class="cart-drawer-bill__zone-edit" onclick="toggleShippingZoneEditor()" aria-label="Change shipping region" title="Change region">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                    ` : ''}
                </span>
                <span class="${valueClass}" id="cart-popup-shipping-value">${shippingValueText}</span>
            </div>
        `;
    }

    const exchangeHtml = currency !== 'USD' && currencyRate !== 1.0
        ? `<div class="cart-drawer-bill__row" id="cart-popup-exchange-rate"><span>Rate: 1 USD = ${currencyRate.toFixed(4)} ${currency}</span></div>`
        : '';

    const zoneEditorHtml = showZoneEdit
        ? `
            <div class="cart-popup-zone-editor cart-popup-zone-editor--hidden" id="cart-popup-zone-editor">
                <select id="shipping-zone-select" onchange="updateShippingZone(this.value)" class="cart-popup-zone-editor__select" aria-label="Shipping region">
                    ${buildShippingZoneOptionsHtml(selectedZoneId)}
                </select>
            </div>
        `
        : '';

    return `
        ${renderFreeShippingBar(baseSubtotalUsd, currency, currencyRate)}
        <div class="cart-drawer-bill" id="cart-popup-bill">
            ${exchangeHtml}
            <div class="cart-drawer-bill__row">
                <span id="cart-popup-subtotal-label">Subtotal (${totalItems} ${itemLabel})</span>
                <span class="cart-drawer-bill__row-value" id="cart-popup-subtotal-value">${CURRENCY_SYMBOL}${subtotal.toFixed(2)}</span>
            </div>
            ${shippingRowHtml}
            <div class="cart-drawer-bill__total">
                <span>Total</span>
                <span class="cart-drawer-bill__total-value" id="cart-popup-total-value">${CURRENCY_SYMBOL}${parseFloat(totalPrice).toFixed(2)}</span>
            </div>
        </div>
        ${zoneEditorHtml}
    `;
}

function updateCartDrawerBillTotals(cartItems, summary, currency, currencyRate) {
    const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);

    let baseSubtotal = 0;
    if (summary.base_subtotal !== undefined && summary.base_subtotal !== null) {
        baseSubtotal = parseFloat(summary.base_subtotal);
    } else if (cartItems && cartItems.length > 0) {
        baseSubtotal = calculateBaseSubtotalUsd(cartItems, currency, currencyRate);
    } else if (summary.subtotal !== undefined && summary.subtotal !== null) {
        const providedSubtotal = parseFloat(summary.subtotal);
        baseSubtotal = currency !== 'USD' && currencyRate > 0
            ? providedSubtotal / currencyRate
            : providedSubtotal;
    }

    const subtotal = cartItems && cartItems.length > 0
        ? calculateCartSubtotalFromItems(cartItems)
        : (summary.converted_subtotal !== undefined
            ? parseFloat(summary.converted_subtotal)
            : (currency !== 'USD' ? baseSubtotal * currencyRate : baseSubtotal));

    const selectedZoneId = resolveSelectedShippingZoneId();
    const shippingInfo = calculateShippingCost(cartItems, baseSubtotal, selectedZoneId);
    const shippingApplied = applyFreeShippingToCost(shippingInfo, baseSubtotal);
    const shippingCost = shippingApplied.costConverted;
    const totalPrice = subtotal + shippingCost;
    const zoneLabel = getShippingZoneDisplayLabel(selectedZoneId, shippingInfo);

    const freeshipEl = document.getElementById('cart-popup-freeship');
    if (freeshipEl) {
        freeshipEl.outerHTML = renderFreeShippingBar(baseSubtotal, currency, currencyRate);
    }

    const itemLabel = totalItems === 1 ? 'item' : 'items';
    const subtotalLabel = document.getElementById('cart-popup-subtotal-label');
    const subtotalValue = document.getElementById('cart-popup-subtotal-value');
    const shippingLabel = document.getElementById('cart-popup-shipping-label');
    const shippingValue = document.getElementById('cart-popup-shipping-value');
    const totalValue = document.getElementById('cart-popup-total-value');
    const headSub = document.querySelector('.cart-popup-head__sub');
    const zoneSelect = document.getElementById('shipping-zone-select');

    if (subtotalLabel) subtotalLabel.textContent = `Subtotal (${totalItems} ${itemLabel})`;
    if (subtotalValue) subtotalValue.textContent = `${CURRENCY_SYMBOL}${subtotal.toFixed(2)}`;
    if (headSub) headSub.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'items'} in your cart`;

    if (shippingLabel && shippingValue) {
        if (shippingInfo.available === false) {
            shippingLabel.textContent = 'Shipping not available for this area';
            shippingValue.textContent = 'N/A';
            shippingValue.className = 'cart-drawer-bill__row-value cart-drawer-bill__row--error';
        } else {
            const hideZonePicker = shouldHideShippingZonePicker();
            const labelText = hideZonePicker ? 'Shipping' : `Shipping — ${zoneLabel}`;
            shippingLabel.innerHTML = hideZonePicker || !hasShippingZonePicker()
                ? labelText
                : `${labelText}
                    <button type="button" class="cart-drawer-bill__zone-edit" onclick="toggleShippingZoneEditor()" aria-label="Change shipping region" title="Change region">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>`;
            shippingValue.textContent = shippingApplied.isFree ? 'FREE' : `${CURRENCY_SYMBOL}${shippingCost.toFixed(2)}`;
            shippingValue.className = shippingApplied.isFree
                ? 'cart-drawer-bill__row-value cart-drawer-bill__row-value--free'
                : 'cart-drawer-bill__row-value';
        }
    }

    if (zoneSelect) {
        zoneSelect.value = selectedZoneId || '';
    }

    if (totalValue) {
        totalValue.textContent = `${CURRENCY_SYMBOL}${totalPrice.toFixed(2)}`;
    }

    return { totalItems, subtotal, baseSubtotal, shippingInfo, shippingCost, shippingApplied, totalPrice, selectedZoneId, zoneLabel };
}

function toggleShippingZoneEditor() {
    const editor = document.getElementById('cart-popup-zone-editor');
    if (!editor) {
        return;
    }

    editor.classList.toggle('cart-popup-zone-editor--hidden');
    if (!editor.classList.contains('cart-popup-zone-editor--hidden')) {
        editor.querySelector('select')?.focus();
    }
}

function renderCartPromoOfferBlock() {
    if (AUTH_USER?.email || hasCartPromoBeenClaimedLocally()) {
        return '';
    }

    return `
        <div class="cart-drawer-promo" id="cart-promo-offer">
            <div class="cart-drawer-promo__head">
                <span class="cart-drawer-promo__code" aria-hidden="true">CART5</span>
                <div>
                    <p class="cart-drawer-promo__title">Get 5% off your order</p>
                    <p class="cart-drawer-promo__sub">Enter your email — we&apos;ll send the code instantly.</p>
                </div>
            </div>
            <form class="cart-drawer-promo__form" onsubmit="submitCartPromoEmail(event)">
                <input type="email" name="email" required autocomplete="email" placeholder="Your email" class="cart-drawer-promo__input" aria-label="Email for promo code">
                <button type="submit" class="cart-drawer-promo__submit">Get code</button>
            </form>
        </div>
    `;
}

function handlePostAddToCartPromo() {
    if (!AUTH_USER?.email || hasCartPromoBeenClaimedLocally()) {
        return;
    }

    fetch('/api/promo/claim-cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin',
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            return;
        }

        markCartPromoClaimedLocally();

        if (!data.already_sent) {
            showCartSuccess(`Code ${data.code} sent to ${AUTH_USER.email}!`);
        }
    })
    .catch(() => {});
}

async function submitCartPromoEmail(event) {
    event.preventDefault();

    const form = event.target;
    const emailInput = form.querySelector('[name="email"]');
    const email = emailInput?.value?.trim();
    if (!email) {
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
    }

    try {
        const response = await fetch('/api/promo/claim-cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin',
            body: JSON.stringify({ email })
        });

        const data = await response.json();

        if (data.success) {
            markCartPromoClaimedLocally();

            const block = document.getElementById('cart-promo-offer');
            if (block) {
                block.className = 'cart-drawer-promo cart-drawer-promo--success';
                block.innerHTML = `<span aria-hidden="true">✓</span><span>Code <strong>${data.code}</strong> sent — check your inbox at <strong>${email}</strong></span>`;
            }

            showCartSuccess(
                data.already_sent
                    ? `Code ${data.code} was already sent — check your email!`
                    : `Code ${data.code} sent to ${email}!`
            );
        } else {
            showAlert({
                title: 'Could not send code',
                message: data.message || 'Please try again later.',
                type: 'error'
            });
        }
    } catch (error) {
        showAlert({
            title: 'Error',
            message: 'Could not send promo code. Please try again.',
            type: 'error'
        });
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    }
}

function showCartPopup(addedProduct) {
    closeCartPopup();

    const backdrop = document.createElement('div');
    backdrop.id = 'cart-drawer-backdrop';
    backdrop.className = 'cart-drawer-backdrop';
    backdrop.setAttribute('aria-hidden', 'true');

    const drawer = document.createElement('aside');
    drawer.id = 'cart-drawer';
    drawer.className = 'cart-drawer';
    drawer.setAttribute('role', 'dialog');
    drawer.setAttribute('aria-modal', 'true');
    drawer.setAttribute('aria-label', 'Shopping cart');

    drawer.innerHTML = `
        <div class="cart-popup-loading">
            <div class="animate-spin rounded-full h-10 w-10 border-2 border-[#005366] border-t-transparent"></div>
        </div>
    `;

    document.body.appendChild(backdrop);
    document.body.appendChild(drawer);
    document.body.classList.add('cart-drawer-open');

    requestAnimationFrame(function () {
        backdrop.classList.add('is-open');
        drawer.classList.add('is-open');
    });

    fetch('/api/cart/get', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderCartPopup(drawer, data.cart_items || [], data.summary || {}, data.shipping_details || null);
        } else {
            renderCartPopup(drawer, [], {}, null);
        }
    })
    .catch(error => {
        console.error('Failed to fetch cart:', error);
        drawer.innerHTML = `
            <div class="cart-popup-head">
                <div class="cart-popup-head__title-wrap">
                    <h2 class="cart-popup-head__title">Unable to load cart</h2>
                </div>
                <button type="button" onclick="closeCartPopup()" class="cart-popup-head__close" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="cart-drawer__scroll">
                <div class="cart-popup-body">
                    <p class="text-[#e2150c] mb-2 font-semibold">Something went wrong. Please try again.</p>
                    <p class="text-sm text-gray-500 mb-4">${error.message}</p>
                    <button type="button" onclick="closeCartPopup()" class="btn-outline-petrol">Close</button>
                </div>
            </div>
        `;
    });

    backdrop.addEventListener('click', closeCartPopup);

    if (!window._cartDrawerEscapeHandler) {
        window._cartDrawerEscapeHandler = function (e) {
            if (e.key === 'Escape' && document.getElementById('cart-drawer')) {
                closeCartPopup();
            }
        };
        document.addEventListener('keydown', window._cartDrawerEscapeHandler);
    }
}


function renderCartPopup(popup, cartItems, summary, shippingDetails) {
    const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    
    // Get currency and rate from summary or use defaults
    const currency = summary.currency || CURRENT_CURRENCY || 'USD';
    // Use currency rate from summary, or fallback to CURRENT_CURRENCY_RATE, or default to 1.0
    const currencyRate = parseFloat(summary.currency_rate || CURRENT_CURRENCY_RATE || 1.0);
    
    console.log('Currency info:', {
        currency: currency,
        currencyRate: currencyRate,
        summaryCurrencyRate: summary.currency_rate,
        CURRENT_CURRENCY_RATE: CURRENT_CURRENCY_RATE
    });
    
    // Subtotal/shipping base — unit price already includes customization fees.
    let baseSubtotal = 0;

    if (summary.base_subtotal !== undefined && summary.base_subtotal !== null) {
        baseSubtotal = parseFloat(summary.base_subtotal);
    } else if (cartItems && cartItems.length > 0) {
        baseSubtotal = calculateBaseSubtotalUsd(cartItems, currency, currencyRate);
    } else if (summary.subtotal !== undefined && summary.subtotal !== null) {
        const providedSubtotal = parseFloat(summary.subtotal);
        baseSubtotal = currency !== 'USD' && currencyRate > 0
            ? providedSubtotal / currencyRate
            : providedSubtotal;
    }

    const subtotal = cartItems && cartItems.length > 0
        ? calculateCartSubtotalFromItems(cartItems)
        : (summary.converted_subtotal !== undefined
            ? parseFloat(summary.converted_subtotal)
            : (currency !== 'USD' ? baseSubtotal * currencyRate : baseSubtotal));

    const selectedZoneId = resolveSelectedShippingZoneId();
    const shippingInfo = calculateShippingCost(cartItems, baseSubtotal, selectedZoneId);
    const shippingApplied = applyFreeShippingToCost(shippingInfo, baseSubtotal);
    const shippingCost = shippingApplied.costConverted;
    const totalPrice = subtotal + shippingCost;
    const zoneLabel = getShippingZoneDisplayLabel(selectedZoneId, shippingInfo);

    popup.innerHTML = `
        <div class="cart-popup-head">
            <div class="cart-popup-head__title-wrap">
                <span class="cart-popup-head__icon" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <div>
                    <h2 class="cart-popup-head__title">Added to cart</h2>
                    <p class="cart-popup-head__sub">${totalItems} ${totalItems === 1 ? 'item' : 'items'} in your cart</p>
                </div>
            </div>
            <button type="button" onclick="closeCartPopup()" class="cart-popup-head__close" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="cart-drawer__scroll">
            <div class="cart-popup-body">
                <div class="cart-popup-items" id="cart-popup-items">
                    ${generateCartPopupItems(cartItems)}
                </div>
            </div>

            <div class="cart-popup-recs">
                <h3 class="cart-popup-recs__title">You may also like</h3>
                <div class="cart-popup-recs__grid" id="cross-sell-products">
                    ${generateCrossSellProducts()}
                </div>
            </div>
        </div>

        <div class="cart-drawer__footer">
            <div class="cart-popup-summary">
                ${renderCartDrawerBill({
                    totalItems,
                    subtotal,
                    baseSubtotalUsd: baseSubtotal,
                    shippingInfo,
                    shippingCost,
                    shippingIsFree: shippingApplied.isFree,
                    totalPrice,
                    currency,
                    currencyRate,
                    selectedZoneId,
                    zoneLabel,
                })}
            </div>

            ${renderCartPromoOfferBlock()}

            <div class="cart-popup-actions">
                <div class="cart-popup-actions__buttons">
                    <button type="button" onclick="goToCheckoutFromPopup()" class="btn-cta">
                        Checkout
                    </button>
                    <button type="button" onclick="closeCartPopup(); window.location.href='{{ route('cart.index') }}'" class="btn-outline-petrol">
                        View cart
                    </button>
                </div>
                <button type="button" onclick="closeCartPopup(); window.location.href='{{ route('products.index') }}'" class="cart-popup-actions__continue">
                    Continue shopping
                </button>
            </div>
        </div>
    `;
    
    // Setup event delegation for cart buttons
    setTimeout(() => {
        setupCartPopupEventDelegation();
    }, 100);
}

// Setup event delegation for cart popup buttons
function setupCartPopupEventDelegation() {
    const drawer = document.getElementById('cart-drawer');
    if (!drawer) return;

    drawer.removeEventListener('click', handleCartPopupClick);
    drawer.addEventListener('click', handleCartPopupClick);
}

// Handle all cart popup clicks
function handleCartPopupClick(e) {
    const target = e.target.closest('button');
    
    // Check if clicking on a button first
    if (target) {
        // Handle remove item button
        if (target.classList.contains('remove-cart-item')) {
            e.preventDefault();
            e.stopPropagation();
            const cartItemId = parseInt(target.dataset.cartItemId);
            if (cartItemId) {
                removeCartItemById(cartItemId);
            }
            return;
        }
        
        // Handle decrease quantity button
        if (target.classList.contains('decrease-quantity')) {
            e.preventDefault();
            e.stopPropagation();
            const cartItemId = parseInt(target.dataset.cartItemId);
            const newQuantity = parseInt(target.dataset.newQuantity);
            if (cartItemId && newQuantity >= 0) {
                updateCartItemQuantity(e, cartItemId, newQuantity);
            }
            return;
        }
        
        // Handle increase quantity button
        if (target.classList.contains('increase-quantity')) {
            e.preventDefault();
            e.stopPropagation();
            const cartItemId = parseInt(target.dataset.cartItemId);
            const newQuantity = parseInt(target.dataset.newQuantity);
            if (cartItemId && newQuantity > 0) {
                updateCartItemQuantity(e, cartItemId, newQuantity);
            }
            return;
        }
        
        // Handle cross-sell add button - stop propagation to parent div
        if (target.classList.contains('cross-sell-add-btn')) {
            e.preventDefault();
            e.stopPropagation();
            return; // Let the cross-sell-product handler take care of it
        }
    }
    
    // Check for cross-sell product click (div click, not button)
    const crossSellProduct = e.target.closest('.cross-sell-product');
    if (crossSellProduct) {
        e.preventDefault();
        const productId = parseInt(crossSellProduct.dataset.productId);
        const productName = crossSellProduct.dataset.productName;
        const productPrice = parseFloat(crossSellProduct.dataset.productPrice);
        const productImage = crossSellProduct.dataset.productImage;
        const productSlug = crossSellProduct.dataset.productSlug;
        const hasVariants = crossSellProduct.dataset.hasVariants === 'true';
        
        handleCrossSellClick(productId, productName, productPrice, productImage, productSlug, hasVariants);
        return;
    }
}


// Resolve unit/line totals — `price` includes customization; legacy rows use effective_unit_price from API.
function getCartItemUnitPrice(item) {
    if (item.effective_unit_price !== undefined && item.effective_unit_price !== null) {
        return parseFloat(item.effective_unit_price) || 0;
    }

    return parseFloat(item.price) || 0;
}

function calculateItemTotal(item) {
    const quantity = parseInt(item.quantity, 10) || 1;
    return getCartItemUnitPrice(item) * quantity;
}

function calculateCartSubtotalFromItems(cartItems) {
    if (!cartItems || cartItems.length === 0) {
        return 0;
    }

    return cartItems.reduce((sum, item) => sum + calculateItemTotal(item), 0);
}

function calculateBaseSubtotalUsd(cartItems, currency, currencyRate) {
    if (!cartItems || cartItems.length === 0) {
        return 0;
    }

    return cartItems.reduce((sum, item) => {
        const unitPrice = getCartItemUnitPrice(item);
        const basePriceUsd = currency !== 'USD' && currencyRate > 0
            ? unitPrice / currencyRate
            : unitPrice;

        return sum + basePriceUsd * (parseInt(item.quantity, 10) || 1);
    }, 0);
}

/**
 * Calculate shipping cost for cart items based on categories and zone
 * @param {Array} cartItems - Array of cart items
 * @param {number} baseSubtotal - Base subtotal in USD
 * @param {number|null} zoneId - Selected shipping zone ID (optional)
 * @returns {Object} - Object containing shipping cost details
 */
function calculateShippingCost(cartItems, baseSubtotal, zoneId = null) {
    if (!cartItems || cartItems.length === 0) {
        return {
            cost: 0,
            costConverted: 0,
            rate: null,
            name: null,
            zoneId: null,
            zoneName: null
        };
    }
    
    // Filter rates by zone if zoneId is provided
    let availableRates = SHIPPING_RATES;
    let currentZoneName = null; // Initialize zone name variable for use throughout function
    
    if (zoneId !== null) {
        // Extract zone_id from value if format is "zone_id:country_code"
        let actualZoneId = zoneId;
        if (typeof zoneId === 'string' && zoneId.includes(':')) {
            actualZoneId = zoneId.split(':')[0];
        }
        
        // Determine zone name for display (set before filtering rates)
        if (typeof actualZoneId === 'string' && actualZoneId.startsWith('general_')) {
            // Extract zone name from zoneId (e.g., 'general_euro' -> 'Euro')
            currentZoneName = actualZoneId.replace('general_', '').split('_').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
            ).join(' ');
        } else {
            // Regular zone: get name from SHIPPING_ZONES
            const parsedZoneId = typeof actualZoneId === 'string' && !isNaN(actualZoneId) 
                ? parseInt(actualZoneId) 
                : actualZoneId;
            currentZoneName = SHIPPING_ZONES.find(z => z.id === parsedZoneId)?.name || null;
        }
        
        // Check if it's a general domain zone (starts with 'general_')
        if (typeof actualZoneId === 'string' && actualZoneId.startsWith('general_')) {
            // Extract zone name from zoneId (e.g., 'general_euro' -> 'Euro')
            const zoneName = actualZoneId.replace('general_', '').split('_').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
            ).join(' ');
            
            // Filter rates with null zone_id and matching zone_name
            // Use flexible matching: normalize both zone names for comparison
            const normalizeZoneName = (name) => name ? name.toLowerCase().trim().replace(/\s+/g, ' ') : '';
            const normalizedZoneName = normalizeZoneName(zoneName);
            
            availableRates = SHIPPING_RATES.filter(r => {
                if (r.zone_id !== null) return false;
                if (!r.zone_name) return false;
                const normalizedRateZoneName = normalizeZoneName(r.zone_name);
                return normalizedRateZoneName === normalizedZoneName || 
                       normalizedRateZoneName.includes(normalizedZoneName) ||
                       normalizedZoneName.includes(normalizedRateZoneName);
            });
            
            // If no rates found with matching zone_name, fallback to all general domain rates (zone_id = null)
            // This allows any general rate to be used when specific zone_name rates don't exist
            if (availableRates.length === 0) {
                const allGeneralRates = SHIPPING_RATES.filter(r => r.zone_id === null);
                if (allGeneralRates.length > 0) {
                    availableRates = allGeneralRates;
                }
            }
        } else {
            // Regular zone: filter by zone_id
            // Parse to integer if it's a numeric string
            const parsedZoneId = typeof actualZoneId === 'string' && !isNaN(actualZoneId) 
                ? parseInt(actualZoneId) 
                : actualZoneId;
            availableRates = SHIPPING_RATES.filter(r => r.zone_id === parsedZoneId);
            
            // If no rates found for this specific zone, include general domain rates (zone_id = null) as fallback
            // This allows general rates to be used when zone-specific rates don't exist
            if (availableRates.length === 0) {
                const generalRates = SHIPPING_RATES.filter(r => r.zone_id === null);
                if (generalRates.length > 0) {
                    availableRates = generalRates;
                }
            }
        }
    }
    
    // Group items by category
    const itemsByCategory = {};
    let totalItems = 0;
    
    cartItems.forEach(item => {
        const product = item.product || {};
        const categories = product.categories || [];
        
        // Get first category ID (primary category)
        let categoryId = null;
        if (categories && categories.length > 0) {
            const firstCategory = categories[0];
            categoryId = firstCategory.id || (typeof firstCategory === 'object' ? firstCategory.category_id : null);
        }
        
        // If no category, use null as key for general items
        const key = categoryId || 'general';
        
        if (!itemsByCategory[key]) {
            itemsByCategory[key] = {
                categoryId: categoryId,
                items: [],
                quantity: 0
            };
        }
        
        itemsByCategory[key].items.push(item);
        itemsByCategory[key].quantity += item.quantity;
        totalItems += item.quantity;
    });
    
    // Calculate shipping cost for each category group
    let totalShippingCost = 0;
    let shippingRateUsed = null;
    let shippingName = null;
    let zoneName = null;
    let allGroupsHaveRate = true;
    
    Object.values(itemsByCategory).forEach(group => {
        const categoryId = group.categoryId;
        const quantity = group.quantity;
        
        // Find shipping rate for this category
        let rate = null;
        
        // First, try to find rate specific to this category with all conditions
        if (categoryId) {
            rate = availableRates.find(r => 
                r.category_id === categoryId && 
                (!r.min_items || quantity >= r.min_items) &&
                (!r.max_items || quantity <= r.max_items) &&
                (!r.min_order_value || baseSubtotal >= r.min_order_value) &&
                (!r.max_order_value || baseSubtotal <= r.max_order_value)
            );
        }
        
        // If no category-specific rate with conditions, try category-specific rate without quantity/order value conditions
        if (!rate && categoryId) {
            rate = availableRates.find(r => r.category_id === categoryId);
        }
        
        // If no category-specific rate, try general rate (category_id is null) with all conditions
        if (!rate) {
            rate = availableRates.find(r => 
                r.category_id === null &&
                (!r.min_items || quantity >= r.min_items) &&
                (!r.max_items || quantity <= r.max_items) &&
                (!r.min_order_value || baseSubtotal >= r.min_order_value) &&
                (!r.max_order_value || baseSubtotal <= r.max_order_value)
            );
        }
        
        // If no general rate with conditions, try any general rate (category_id is null)
        if (!rate) {
            rate = availableRates.find(r => r.category_id === null);
        }
        
        // If still no rate found, use default shipping rate (if it meets conditions and matches zone)
        if (!rate && DEFAULT_SHIPPING_RATE) {
            const defaultRate = DEFAULT_SHIPPING_RATE;
            
            // Extract actual zone ID for comparison
            let actualZoneIdForComparison = zoneId;
            if (zoneId !== null) {
                if (typeof zoneId === 'string' && zoneId.includes(':')) {
                    actualZoneIdForComparison = zoneId.split(':')[0];
                }
                // Parse to integer if it's a numeric string
                if (typeof actualZoneIdForComparison === 'string' && !isNaN(actualZoneIdForComparison)) {
                    actualZoneIdForComparison = parseInt(actualZoneIdForComparison);
                }
            }
            
            // Check if default rate meets the conditions and zone
            // Allow default rate if: zoneId is null OR default rate zone matches OR default rate is general (zone_id = null)
            // If availableRates is empty, be more lenient with zone matching
            let zoneMatches = false;
            if (availableRates.length === 0) {
                // When no rates exist for the zone, allow default rate regardless of zone (as final fallback)
                zoneMatches = true;
            } else {
                // Normal zone matching when rates exist
                zoneMatches = zoneId === null || 
                             defaultRate.zone_id === actualZoneIdForComparison || 
                             defaultRate.zone_id === null; // General domain rate can be used for any zone
            }
            
            // When availableRates is empty, be more lenient with conditions (use default rate as last resort)
            const meetsConditions = zoneMatches && (
                availableRates.length === 0 
                    ? true // When no rates available, use default rate regardless of quantity/order value conditions
                    : (
                        (!defaultRate.min_items || quantity >= defaultRate.min_items) &&
                        (!defaultRate.max_items || quantity <= defaultRate.max_items) &&
                        (!defaultRate.min_order_value || baseSubtotal >= defaultRate.min_order_value) &&
                        (!defaultRate.max_order_value || baseSubtotal <= defaultRate.max_order_value)
                    )
            );
            
            if (meetsConditions) {
                rate = defaultRate;
            }
        }
        
        // Priority 6: If still no rate, use first available rate from availableRates
        if (!rate && availableRates.length > 0) {
            rate = availableRates[0]; // Use first available rate
        }
        
        // Priority 7: If still no rate, use first rate from all SHIPPING_RATES
        if (!rate && SHIPPING_RATES.length > 0) {
            rate = SHIPPING_RATES[0]; // Use first rate as final fallback
        }
        
        // Always use a rate if available (never return unavailable)
        if (rate) {
            // Calculate cost for this group: first_item_cost + (quantity - 1) * additional_item_cost
            const groupCost = rate.first_item_cost + (quantity - 1) * rate.additional_item_cost;
            totalShippingCost += groupCost;
            
            // Store the rate used (prefer category-specific rate)
            if (!shippingRateUsed || (categoryId && rate.category_id === categoryId)) {
                shippingRateUsed = rate;
                shippingName = rate.name;
                zoneName = rate.zone_name;
            }
        } else {
            allGroupsHaveRate = false;
        }
    });
    
    // If no rates found for any group, try to use default rate or first available rate
    if (!allGroupsHaveRate || (totalShippingCost === 0 && !shippingRateUsed)) {
        // Try to use default rate
        if (DEFAULT_SHIPPING_RATE) {
            const defaultRate = DEFAULT_SHIPPING_RATE;
            const quantity = cartItems.reduce((sum, item) => sum + (item.quantity || 1), 0);
            const groupCost = defaultRate.first_item_cost + (quantity - 1) * defaultRate.additional_item_cost;
            totalShippingCost = groupCost;
            shippingRateUsed = defaultRate;
            shippingName = defaultRate.name;
            zoneName = defaultRate.zone_name;
        } else if (SHIPPING_RATES.length > 0) {
            // Use first available rate
            const firstRate = SHIPPING_RATES[0];
            const quantity = cartItems.reduce((sum, item) => sum + (item.quantity || 1), 0);
            const groupCost = firstRate.first_item_cost + (quantity - 1) * firstRate.additional_item_cost;
            totalShippingCost = groupCost;
            shippingRateUsed = firstRate;
            shippingName = firstRate.name;
            zoneName = firstRate.zone_name;
        } else {
            // Only return unavailable if absolutely no rates exist
            return {
                cost: 0,
                costConverted: 0,
                rate: null,
                name: null,
                zoneId: zoneId,
                zoneName: currentZoneName,
                available: false
            };
        }
    }
    
    // Convert to current currency if needed
    const costConverted = CURRENT_CURRENCY !== 'USD' && CURRENT_CURRENCY_RATE > 0
        ? totalShippingCost * CURRENT_CURRENCY_RATE
        : totalShippingCost;
    
    return {
        cost: totalShippingCost, // Cost in USD
        costConverted: costConverted, // Cost in current currency
        rate: shippingRateUsed,
        name: shippingName || 'Standard Shipping',
        zoneId: zoneId,
        zoneName: zoneName,
        available: true
    };
}

function generateCartPopupItems(cartItems) {
    if (!cartItems || cartItems.length === 0) {
        return '<p class="cart-popup-empty">Your cart is empty</p>';
    }

    function cartLineName(item) {
        return item.display_name
            || (item.customizations && item.customizations._studio && item.customizations._studio.title)
            || (item.product && item.product.name)
            || 'Custom product';
    }

    function cartLineImage(item, product) {
        if (item.display_image) return item.display_image;
        if (item.customizations && item.customizations._studio && item.customizations._studio.image) {
            return item.customizations._studio.image;
        }
        if (item.is_studio_custom) return null;
        if (product.media) {
            if (Array.isArray(product.media) && product.media.length > 0) {
                const firstMedia = product.media[0];
                return typeof firstMedia === 'object' ? (firstMedia.url || firstMedia) : firstMedia;
            }
            if (typeof product.media === 'string') return product.media;
        }
        return null;
    }

    function visibleCustomizations(item) {
        const rows = item.customizations || {};
        return Object.entries(rows).filter(function (entry) {
            return String(entry[0]).charAt(0) !== '_';
        });
    }
    
    return cartItems.map((item) => {
        const product = item.product || {};
        const shop = product.shop || {};
        const isStudio = !!(item.is_studio_custom || (item.customizations && item.customizations._studio && item.customizations._studio.standalone));
        const name = cartLineName(item);
        const productImage = cartLineImage(item, product);
        const customs = visibleCustomizations(item);
        
        return `
            <article class="cart-popup-item">
                <div class="cart-popup-item__inner">
                    <div class="cart-popup-item__media">
                        ${productImage && productImage !== 'undefined' && productImage !== '' ? `
                            <img src="${productImage}" alt="${name || ''}" onerror="this.parentElement.innerHTML='<div class=\\'cart-popup-item__media-fallback\\'><svg class=\\'w-8 h-8\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z\\'/></svg></div>'">
                        ` : `
                            <div class="cart-popup-item__media-fallback">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        `}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start gap-2 mb-1">
                            <h4 class="cart-popup-item__name">${name || 'Custom product'}</h4>
                            <button type="button" class="cart-popup-item__remove remove-cart-item" data-cart-item-id="${item.id}" aria-label="Remove item">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>

                        ${!isStudio && shop.name ? `
                            <p class="cart-popup-item__shop">Sold by <a href="/shops/${shop.shop_slug || ''}">${shop.name}</a></p>
                        ` : ''}

                        ${item.selected_variant && item.selected_variant.attributes ? `
                            <div class="mb-2">
                                ${Object.entries(item.selected_variant.attributes).map(([key, value]) => `
                                    <span class="cart-popup-item__variant">${key}: ${value}</span>
                                `).join('')}
                            </div>
                        ` : ''}

                        ${customs.length > 0 ? `
                            <div class="cart-popup-item__custom">
                                ${customs.map(([key, custom]) =>
                                    `<div>${key}: ${custom && custom.value ? custom.value : ''}${custom && custom.price > 0 ? ` (+${CURRENCY_SYMBOL}${parseFloat(custom.price).toFixed(2)})` : ''}</div>`
                                ).join('')}
                            </div>
                        ` : ''}

                        <div class="cart-popup-item__footer">
                            <div class="cart-popup-qty">
                                <button type="button" class="cart-popup-qty__btn decrease-quantity ${item.quantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                                        data-cart-item-id="${item.id}"
                                        data-new-quantity="${item.quantity - 1}"
                                        ${item.quantity <= 1 ? 'disabled' : ''}
                                        aria-label="Decrease quantity">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                </button>
                                <span class="cart-popup-qty__value" id="quantity-${item.id}">${item.quantity}</span>
                                <button type="button" class="cart-popup-qty__btn increase-quantity"
                                        data-cart-item-id="${item.id}"
                                        data-new-quantity="${item.quantity + 1}"
                                        aria-label="Increase quantity">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>
                            <div>
                                <p class="cart-popup-item__price">${CURRENCY_SYMBOL}${calculateItemTotal(item).toFixed(2)}</p>
                                ${item.quantity > 1 ? `
                                    <p class="cart-popup-item__unit">${CURRENCY_SYMBOL}${getCartItemUnitPrice(item).toFixed(2)} each</p>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        `;
    }).join('');
}
function updateCartItemQuantity(e, cartItemId, newQuantity) {
    if (newQuantity < 1) {
        removeCartItemById(cartItemId);
        return;
    }
    
    console.log('Updating cart item:', cartItemId, 'to quantity:', newQuantity);
    
    // Show loading state
    const quantitySpan = document.getElementById(`quantity-${cartItemId}`);
    let originalText = '';
    if (quantitySpan) {
        originalText = quantitySpan.textContent;
        quantitySpan.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#005366] mx-auto"></div>';
    }
    
    fetch(`/api/cart/update/${cartItemId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ quantity: newQuantity })
    })
    .then(response => {
        console.log('Update response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Update response data:', data);
        if (data.success) {
            console.log('Quantity updated successfully');
            // Sync localStorage first
            syncLocalStorageWithBackend();
            // Then refresh popup content
            setTimeout(() => {
                refreshCartPopupContent();
            }, 100);
        } else {
            if (quantitySpan) quantitySpan.textContent = originalText;
            const errorMsg = data.message || 'An error occurred while updating quantity';
            showAlert({
                icon: 'error',
                title: 'Unable to Update',
                text: errorMsg,
                confirmButtonText: 'Close',
                confirmButtonColor: '#005366'
            });
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
        if (quantitySpan) quantitySpan.textContent = originalText;
        const errorMsg = error.message || 'An error occurred';
        showAlert({
            icon: 'error',
            title: 'Error',
            text: errorMsg,
            confirmButtonText: 'Close',
            confirmButtonColor: '#005366'
        });
    });
}

function removeCartItemById(cartItemId) {
    showAlert({
        icon: 'question',
        title: 'Confirm Removal',
        text: 'Are you sure you want to remove this product from your cart?',
        showCancelButton: true,
        confirmButtonText: 'Remove',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#E2150C',
        cancelButtonColor: '#6b7280',
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'px-6 py-3 rounded-lg',
            cancelButton: 'px-6 py-3 rounded-lg'
        }
    }).then((result) => {
        if (!result.isConfirmed) return;
        
        console.log('Removing cart item:', cartItemId);
        
        fetch(`/api/cart/remove/${cartItemId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            console.log('Remove response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Remove response data:', data);
            if (data.success) {
                console.log('Item removed successfully');
                // Sync localStorage
                syncLocalStorageWithBackend();
                // Refresh popup content
                setTimeout(() => {
                    refreshCartPopupContent();
                }, 100);
                // Show notification
                showCartSuccess('Product removed from cart');
            } else {
                const errorMsg = data.message || 'An error occurred while removing the product';
                showAlert({
                    icon: 'error',
                    title: 'Unable to Remove',
                    text: errorMsg,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#005366'
                });
            }
        })
        .catch(error => {
            console.error('Error removing item:', error);
            const errorMsg = error.message || 'An error occurred';
            showAlert({
                icon: 'error',
                title: 'Error',
                text: errorMsg,
                confirmButtonText: 'Close',
                confirmButtonColor: '#005366'
            });
        });
    });
}
function refreshCartPopupContent() {
    // Fetch latest cart data and update popup
    fetch('/api/cart/get', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.cart_items) {
            console.log('Refreshing popup with:', data.cart_items.length, 'items');
            
            // Update items container
            const cartItemsContainer = document.getElementById('cart-popup-items');
            if (cartItemsContainer) {
                cartItemsContainer.innerHTML = generateCartPopupItems(data.cart_items);
            }
            
            const summary = data.summary || {};
            const currency = data.currency || CURRENT_CURRENCY || 'USD';
            const currencyRate = parseFloat(data.currency_rate || 1.0);

            updateCartDrawerBillTotals(data.cart_items, summary, currency, currencyRate);
            
            // Update header cart count
            updateCartCount();
            
            // Setup event delegation after refresh
            setTimeout(() => {
                setupCartPopupEventDelegation();
            }, 100);
            
            // If cart is empty, close popup
            if (data.cart_items.length === 0) {
                closeCartPopup();
                showCartSuccess('Cart is empty');
            }
        }
    })
    .catch(error => {
        console.error('Failed to refresh popup:', error);
        // Don't close popup, just show error in items area
        const cartItemsContainer = document.getElementById('cart-popup-items');
        if (cartItemsContainer) {
            cartItemsContainer.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-red-600">Unable to update cart</p>
                    <p class="text-sm text-gray-500">${error.message}</p>
                    <button onclick="refreshCartPopupContent()" class="mt-2 text-[#005366] hover:underline">Try again</button>
        </div>
            `;
        }
    });
}

function closeCartPopup() {
    const backdrop = document.getElementById('cart-drawer-backdrop');
    const drawer = document.getElementById('cart-drawer');

    if (!drawer) {
        backdrop?.remove();
        document.body.classList.remove('cart-drawer-open');
        return;
    }

    drawer.classList.remove('is-open');
    backdrop?.classList.remove('is-open');

    setTimeout(function () {
        drawer.remove();
        backdrop?.remove();
        document.body.classList.remove('cart-drawer-open');
    }, 280);
}

/**
 * Update shipping zone and recalculate shipping cost
 * @param {string|number} zoneId - Selected shipping zone ID
 */
function updateShippingZone(zoneId) {
    if (!zoneId) return;
    
    // Extract zone_id from value if format is "zone_id:country_code"
    let actualZoneId = zoneId;
    if (typeof zoneId === 'string' && zoneId.includes(':')) {
        actualZoneId = zoneId.split(':')[0];
    }
    
    // Check if it's a general domain zone (starts with 'general_') or a numeric ID
    // For general domain zones, keep as string; for regular zones, parse as integer
    if (!actualZoneId.toString().startsWith('general_')) {
        const parsed = parseInt(actualZoneId);
        if (!isNaN(parsed)) {
            actualZoneId = parsed;
        }
    }
    
    // Save selected zone to localStorage (save the full value including country code)
    localStorage.setItem('selectedShippingZoneId', zoneId);
    
    // Get current cart items from popup
    const cartItemsContainer = document.getElementById('cart-popup-items');
    if (!cartItemsContainer) return;
    
    // Fetch latest cart data to get accurate items
    fetch('/api/cart/get', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.cart_items) {
            const summary = data.summary || {};
            const currency = data.currency || CURRENT_CURRENCY || 'USD';
            const currencyRate = parseFloat(data.currency_rate || 1.0);

            updateCartDrawerBillTotals(data.cart_items, summary, currency, currencyRate);

            const editor = document.getElementById('cart-popup-zone-editor');
            if (editor) {
                editor.classList.add('cart-popup-zone-editor--hidden');
            }
        }
    })
    .catch(error => {
        console.error('Failed to update shipping zone:', error);
    });
}

function triggerCheckoutTrackingFromLocalCart() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');

    if (cart.length === 0) {
        return;
    }

    let cartTotal = 0;
    const productIds = [];

    const gaItems = cart.map((item, index) => {
        const quantity = parseInt(item.quantity, 10) || 1;
        const unitPrice = parseFloat(item.price) || 0;
        cartTotal += unitPrice * quantity;
        productIds.push(item.id);

        const gaItem = {
            item_id: (item.selectedVariant && item.selectedVariant.id) ? String(item.selectedVariant.id) : String(item.id),
            item_name: item.name || `Cart Item ${index + 1}`,
            price: Number(unitPrice.toFixed(2)),
            quantity
        };

        if (item.selectedVariant && item.selectedVariant.attributes) {
            const variantAttributes = Object.values(item.selectedVariant.attributes || {}).filter(Boolean);
            if (variantAttributes.length > 0) {
                gaItem.item_variant = variantAttributes.join(' / ');
            }
        }

        return gaItem;
    });

    if (typeof fbq !== 'undefined') {
        fbq('track', 'InitiateCheckout', {
            content_ids: productIds,
            content_type: 'product',
            value: cartTotal.toFixed(2),
            currency: CURRENT_CURRENCY,
            num_items: cart.length
        });

        console.log('âœ… Facebook Pixel: InitiateCheckout tracked from popup/cart', {
            items: cart.length,
            total: cartTotal.toFixed(2),
            ids: productIds
        });
    }

    // Event tracking Ä‘Æ°á»£c xá»­ lÃ½ bá»Ÿi GTM thÃ´ng qua dataLayer
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': 'begin_checkout',
            'currency': CURRENT_CURRENCY,
            'value': Number(cartTotal.toFixed(2)),
            'items': gaItems
        });

        console.log('âœ… GTM: begin_checkout tracked from popup/cart', {
            items: gaItems.length,
            value: cartTotal.toFixed(2)
        });
    }
}

function goToCheckoutFromPopup() {
    triggerCheckoutTrackingFromLocalCart();
    closeCartPopup();
    window.location.href = '{{ route("checkout.index") }}';
}

@php
    $crossSellData = $fbtProducts->map(function($product) {
        $media = $product->getEffectiveMedia();
        
        // Get image URL safely
        $imageUrl = null;
        if ($media && count($media) > 0) {
            if (is_string($media[0])) {
                $imageUrl = $media[0];
            } elseif (is_array($media[0])) {
                $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? null;
            }
        }
        
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->getEffectivePrice(),
            'originalPrice' => $product->getCompareAtPrice(),
            'image' => $imageUrl,
            'has_variants' => $product->variants()->count() > 0,
        ];
    })->toArray();
@endphp

function generateCrossSellProducts() {
    const relatedProducts = @json($crossSellData ?? []);
    
    if (!relatedProducts || relatedProducts.length === 0) {
        return '<p class="cart-popup-recs__empty">No recommendations available</p>';
    }
    
    const crossSellProducts = relatedProducts.slice(0, 4);
    
    return crossSellProducts.map(product => `
        <div class="cross-sell-product"
             data-product-id="${product.id}"
             data-product-name="${product.name}"
             data-product-price="${product.price}"
             data-product-image="${product.image || ''}"
             data-product-slug="${product.slug}"
             data-has-variants="${product.has_variants}">
            ${product.image && product.image !== 'undefined' && product.image !== '' ? `
                <div class="cross-sell-product__media">
                    <img src="${product.image}" alt="${product.name}"
                         onerror="this.parentElement.innerHTML='<div class=\\'cross-sell-product__media flex items-center justify-center text-gray-400\\'><svg class=\\'w-8 h-8\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\\'/></svg></div>'">
                    ${product.has_variants ? `<span class="cross-sell-product__badge">Options</span>` : ''}
                </div>
            ` : `
                <div class="cross-sell-product__media flex items-center justify-center text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            `}
            <h4 class="cross-sell-product__name">${product.name}</h4>
            <div class="cross-sell-product__footer">
                <div>
                    <span class="cross-sell-product__price">${CURRENCY_SYMBOL}${product.price}</span>
                    ${product.originalPrice && product.originalPrice > product.price ? `
                        <span class="cross-sell-product__price-old">${CURRENCY_SYMBOL}${product.originalPrice}</span>
                    ` : ''}
                </div>
                <button type="button" class="cross-sell-add-btn">
                    ${product.has_variants ? `
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>View</span>
                    ` : `
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add</span>
                    `}
                </button>
            </div>
        </div>
    `).join('');
}

// Handle cross-sell product click
function handleCrossSellClick(productId, productName, productPrice, productImage, productSlug, hasVariants) {
    if (hasVariants) {
        // If product has variants, redirect to product page to select
        window.location.href = `/products/${productSlug}`;
    } else {
        // If no variants, add directly to cart
        addCrossSellToCart(productId, productName, productPrice, productImage);
    }
}

function addCrossSellToCart(productId, productName, productPrice, productImage, options) {
    options = options || {};
    const crossSellProduct = {
        id: productId,
        name: productName,
        price: productPrice,
        image: productImage && productImage !== 'undefined' && productImage !== '' ? productImage : null,
        quantity: 1,
        addedAt: Date.now()
    };
    
    addToLocalCart(crossSellProduct);
    
    return fetch('/api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            id: productId,
            quantity: 1,
            price: productPrice,
            selectedVariant: null,
            customizations: null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            syncLocalStorageWithBackend();
            if (!options.silent) {
                refreshCartPopupContent();
                showCartSuccess('Product added to cart successfully!');
            }
        }
        return data;
    })
    .catch(error => {
        console.error('Failed to add cross-sell product:', error);
        if (!options.silent) {
            showAlert({
                icon: 'error',
                title: 'Unable to Add',
                text: 'An error occurred while adding the product to cart',
                confirmButtonText: 'Close',
                confirmButtonColor: '#005366'
            });
        }
        throw error;
    });
}

function addFbtBundleToCart() {
    const dataEl = document.getElementById('fbt-bundle-data');
    const btn = document.getElementById('fbt-bundle-add-btn');
    if (!dataEl || !btn) return;

    let bundleItems = [];
    try {
        bundleItems = JSON.parse(dataEl.textContent || '[]');
    } catch (e) {
        return;
    }

    const companionItems = bundleItems.filter(function (item) { return !item.isCurrent; });
    const variantItems = companionItems.filter(function (item) { return item.hasVariants; });

    if (variantItems.length > 0) {
        showAlert({
            icon: 'info',
            title: 'Select options first',
            html: '<p class="text-left text-sm text-gray-600">Some bundle items have variants. Open each product to choose size or color, or add this item alone first.</p>',
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366'
        });
        return;
    }

    const validation = validateRequiredCustomizations();
    if (!validation.isValid) {
        showAlert({
            icon: 'warning',
            title: 'Complete this item first',
            text: 'Please finish personalization for the current product before adding the bundle.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366'
        });
        expandCustomization();
        const customizationContainer = document.getElementById('customization-container');
        if (customizationContainer) {
            customizationContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    btn.disabled = true;
    const originalLabel = btn.textContent;
    btn.textContent = 'Adding...';

    addToCart();

    Promise.all(companionItems.map(function (item) {
        return addCrossSellToCart(item.id, item.name, item.price, item.image, { silent: true });
    })).then(function () {
        refreshCartPopupContent();
        showCartSuccess('Bundle added to cart!');
    }).catch(function () {
        showCartSuccess('Items saved locally');
    }).finally(function () {
        btn.disabled = false;
        btn.textContent = originalLabel;
    });
}

document.addEventListener('click', function (event) {
    const quickAddBtn = event.target.closest('[data-pdp-quick-add]');
    if (!quickAddBtn) return;
    event.preventDefault();
    addCrossSellToCart(
        parseInt(quickAddBtn.dataset.productId, 10),
        quickAddBtn.dataset.productName,
        parseFloat(quickAddBtn.dataset.productPrice),
        quickAddBtn.dataset.productImage
    );
});


// Legacy functions for backward compatibility - redirecting to new functions
function updateCartQuantity(index, newQuantity) {
    console.warn('Legacy updateCartQuantity called with index:', index);
    // This should not be used anymore as we're using backend IDs
    // Refresh the popup to get correct IDs
    refreshCartPopupContent();
}

function removeCartItem(index) {
    console.warn('Legacy removeCartItem called with index:', index);
    // This should not be used anymore as we're using backend IDs
    // Refresh the popup to get correct IDs
    refreshCartPopupContent();
}
function syncLocalStorageWithBackend() {
    // Fetch current cart from backend
    fetch('/api/cart/get', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.cart_items) {
            // Convert backend cart items to localStorage format with full product info
            const backendCart = data.cart_items.map(item => {
                const product = item.product || {};
                const shop = product.shop || {};
                const isStudio = !!(item.is_studio_custom || (item.customizations && item.customizations._studio && item.customizations._studio.standalone));

                let productImage = item.display_image || null;
                if (!productImage && product.media && !isStudio) {
                    if (Array.isArray(product.media) && product.media.length > 0) {
                        const firstMedia = product.media[0];
                        productImage = typeof firstMedia === 'object' ? (firstMedia.url || firstMedia) : firstMedia;
                    } else if (typeof product.media === 'string') {
                        productImage = product.media;
                    }
                }
                
                return {
                    cart_item_id: item.id, // Backend cart item ID
                id: item.product_id || item.id,
                    name: item.display_name || (item.customizations && item.customizations._studio && item.customizations._studio.title) || product.name || 'Custom product',
                    slug: isStudio ? '' : (product.slug || ''),
                price: parseFloat(item.price),
                    image: productImage,
                    shop: isStudio ? '' : (shop.name || 'Unknown Shop'),
                    shop_slug: isStudio ? '' : (shop.shop_slug || ''),
                quantity: item.quantity,
                selectedVariant: item.selected_variant,
                customizations: item.customizations,
                addedAt: Date.now()
                };
            });
            
            // Update localStorage to match backend
            localStorage.setItem('cart', JSON.stringify(backendCart));
            
            // Update header count
            updateCartCount();
            
            console.log('LocalStorage synced with backend:', backendCart);
        }
    })
    .catch(error => {
        console.error('Failed to sync with backend:', error);
    });
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    syncLocalStorageWithBackend();

    if (document.getElementById('customization-container')) {
        setCustomizationExpanded(true);
    }
});

// Customization Functions
function toggleCustomization() {
    const container = document.getElementById('customization-container');
    if (!container) {
        return;
    }

    setCustomizationExpanded(container.classList.contains('is-collapsed'));
}

function updateCustomizationPrice() {
    if (!isCustomizationExpanded()) {
        return;
    }
    
    let customizationTotal = 0;
    
    const inputs = document.querySelectorAll('.customization-input');
    
    inputs.forEach(input => {
        if (input.type === 'radio') {
            if (input.checked) {
                const price = parseFloat(input.dataset.price) || 0;
                customizationTotal += price;
            }
        } else if (input.type === 'checkbox') {
            if (input.checked) {
                const price = parseFloat(input.dataset.price) || 0;
                customizationTotal += price;
            }
        } else if (input.type === 'file') {
            if (input.files && input.files.length > 0) {
                const price = parseFloat(input.dataset.price) || 0;
                customizationTotal += price;
            }
        } else if (input.value.trim() !== '') {
            const price = parseFloat(input.dataset.price) || 0;
            customizationTotal += price;
        }
    });
    
    const basePrice = getCurrentUnitBasePrice();
    const totalPrice = basePrice + customizationTotal;
    
    const customizationPriceElement = document.getElementById('customization-price');
    const totalPriceElement = document.getElementById('total-price');
    const priceDisplay = document.getElementById('customization-price-display');
    
    if (customizationTotal > 0) {
        priceDisplay.classList.remove('hidden');
        customizationPriceElement.textContent = '+' + CURRENCY_SYMBOL + customizationTotal.toFixed(2);
        totalPriceElement.textContent = CURRENCY_SYMBOL + totalPrice.toFixed(2);
    } else {
        priceDisplay.classList.add('hidden');
        if (totalPriceElement) {
            totalPriceElement.textContent = CURRENCY_SYMBOL + basePrice.toFixed(2);
        }
    }
}

// Size Guide Modal Functions
let currentGender = 'male';
let currentUnit = 'cm';

// Size data for different product types and genders
const sizeData = {
    'baseball-jackets': {
        male: {
            'LENGTH': { 'S': 66, 'M': 69, 'L': 71, 'XL': 74, '2XL': 76, '3XL': 79, '4XL': 81, '5XL': 83 },
            'BUST': { 'S': 102, 'M': 112, 'L': 122, 'XL': 132, '2XL': 142, '3XL': 152, '4XL': 158, '5XL': 164 },
            'SLEEVE': { 'S': 62, 'M': 63, 'L': 65, 'XL': 66, '2XL': 67, '3XL': 68, '4XL': 69, '5XL': 70 }
        },
        female: {
            'LENGTH': { 'S': 60, 'M': 63, 'L': 66, 'XL': 69, '2XL': 71, '3XL': 74, '4XL': 76, '5XL': 78 },
            'BUST': { 'S': 86, 'M': 91, 'L': 97, 'XL': 102, '2XL': 107, '3XL': 112, '4XL': 117, '5XL': 122 },
            'SLEEVE': { 'S': 58, 'M': 59, 'L': 60, 'XL': 61, '2XL': 62, '3XL': 63, '4XL': 64, '5XL': 65 }
        },
        youth: {
            'LENGTH': { 'S': 50, 'M': 53, 'L': 56, 'XL': 59, '2XL': 62, '3XL': 65, '4XL': 68, '5XL': 71 },
            'BUST': { 'S': 66, 'M': 71, 'L': 76, 'XL': 81, '2XL': 86, '3XL': 91, '4XL': 96, '5XL': 102 },
            'SLEEVE': { 'S': 45, 'M': 47, 'L': 49, 'XL': 51, '2XL': 53, '3XL': 55, '4XL': 57, '5XL': 59 }
        },
        unisex: {
            'LENGTH': { 'S': 66, 'M': 69, 'L': 71, 'XL': 74, '2XL': 76, '3XL': 79, '4XL': 81, '5XL': 83 },
            'BUST': { 'S': 102, 'M': 112, 'L': 122, 'XL': 132, '2XL': 142, '3XL': 152, '4XL': 158, '5XL': 164 },
            'SLEEVE': { 'S': 62, 'M': 63, 'L': 65, 'XL': 66, '2XL': 67, '3XL': 68, '4XL': 69, '5XL': 70 }
        },
        kids: {
            'LENGTH': { 'S': 40, 'M': 43, 'L': 46, 'XL': 49, '2XL': 52, '3XL': 55, '4XL': 58, '5XL': 61 },
            'BUST': { 'S': 56, 'M': 61, 'L': 66, 'XL': 71, '2XL': 76, '3XL': 81, '4XL': 86, '5XL': 91 },
            'SLEEVE': { 'S': 35, 'M': 37, 'L': 39, 'XL': 41, '2XL': 43, '3XL': 45, '4XL': 47, '5XL': 49 }
        }
    },
    't-shirts': {
        male: {
            'LENGTH': { 'S': 70, 'M': 72, 'L': 74, 'XL': 76, '2XL': 78, '3XL': 80, '4XL': 82, '5XL': 84 },
            'BUST': { 'S': 96, 'M': 101, 'L': 106, 'XL': 111, '2XL': 116, '3XL': 121, '4XL': 126, '5XL': 131 },
            'SLEEVE': { 'S': 20, 'M': 21, 'L': 22, 'XL': 23, '2XL': 24, '3XL': 25, '4XL': 26, '5XL': 27 }
        },
        female: {
            'LENGTH': { 'S': 64, 'M': 66, 'L': 68, 'XL': 70, '2XL': 72, '3XL': 74, '4XL': 76, '5XL': 78 },
            'BUST': { 'S': 86, 'M': 91, 'L': 97, 'XL': 102, '2XL': 107, '3XL': 112, '4XL': 117, '5XL': 122 },
            'SLEEVE': { 'S': 18, 'M': 19, 'L': 20, 'XL': 21, '2XL': 22, '3XL': 23, '4XL': 24, '5XL': 25 }
        },
        youth: {
            'LENGTH': { 'S': 50, 'M': 53, 'L': 56, 'XL': 59, '2XL': 62, '3XL': 65, '4XL': 68, '5XL': 71 },
            'BUST': { 'S': 66, 'M': 71, 'L': 76, 'XL': 81, '2XL': 86, '3XL': 91, '4XL': 96, '5XL': 102 },
            'SLEEVE': { 'S': 15, 'M': 16, 'L': 17, 'XL': 18, '2XL': 19, '3XL': 20, '4XL': 21, '5XL': 22 }
        },
        unisex: {
            'LENGTH': { 'S': 70, 'M': 72, 'L': 74, 'XL': 76, '2XL': 78, '3XL': 80, '4XL': 82, '5XL': 84 },
            'BUST': { 'S': 96, 'M': 101, 'L': 106, 'XL': 111, '2XL': 116, '3XL': 121, '4XL': 126, '5XL': 131 },
            'SLEEVE': { 'S': 20, 'M': 21, 'L': 22, 'XL': 23, '2XL': 24, '3XL': 25, '4XL': 26, '5XL': 27 }
        },
        kids: {
            'LENGTH': { 'S': 40, 'M': 43, 'L': 46, 'XL': 49, '2XL': 52, '3XL': 55, '4XL': 58, '5XL': 61 },
            'BUST': { 'S': 56, 'M': 61, 'L': 66, 'XL': 71, '2XL': 76, '3XL': 81, '4XL': 86, '5XL': 91 },
            'SLEEVE': { 'S': 15, 'M': 16, 'L': 17, 'XL': 18, '2XL': 19, '3XL': 20, '4XL': 21, '5XL': 22 }
        }
    },
    'hoodies': {
        male: {
            'LENGTH': { 'S': 68, 'M': 70, 'L': 72, 'XL': 74, '2XL': 76, '3XL': 78, '4XL': 80, '5XL': 82 },
            'BUST': { 'S': 104, 'M': 109, 'L': 114, 'XL': 119, '2XL': 124, '3XL': 129, '4XL': 134, '5XL': 139 },
            'SLEEVE': { 'S': 64, 'M': 65, 'L': 66, 'XL': 67, '2XL': 68, '3XL': 69, '4XL': 70, '5XL': 71 }
        },
        female: {
            'LENGTH': { 'S': 62, 'M': 64, 'L': 66, 'XL': 68, '2XL': 70, '3XL': 72, '4XL': 74, '5XL': 76 },
            'BUST': { 'S': 88, 'M': 93, 'L': 98, 'XL': 103, '2XL': 108, '3XL': 113, '4XL': 118, '5XL': 123 },
            'SLEEVE': { 'S': 60, 'M': 61, 'L': 62, 'XL': 63, '2XL': 64, '3XL': 65, '4XL': 66, '5XL': 67 }
        },
        youth: {
            'LENGTH': { 'S': 48, 'M': 51, 'L': 54, 'XL': 57, '2XL': 60, '3XL': 63, '4XL': 66, '5XL': 69 },
            'BUST': { 'S': 68, 'M': 73, 'L': 78, 'XL': 83, '2XL': 88, '3XL': 93, '4XL': 98, '5XL': 103 },
            'SLEEVE': { 'S': 47, 'M': 49, 'L': 51, 'XL': 53, '2XL': 55, '3XL': 57, '4XL': 59, '5XL': 61 }
        },
        unisex: {
            'LENGTH': { 'S': 68, 'M': 70, 'L': 72, 'XL': 74, '2XL': 76, '3XL': 78, '4XL': 80, '5XL': 82 },
            'BUST': { 'S': 104, 'M': 109, 'L': 114, 'XL': 119, '2XL': 124, '3XL': 129, '4XL': 134, '5XL': 139 },
            'SLEEVE': { 'S': 64, 'M': 65, 'L': 66, 'XL': 67, '2XL': 68, '3XL': 69, '4XL': 70, '5XL': 71 }
        },
        kids: {
            'LENGTH': { 'S': 38, 'M': 41, 'L': 44, 'XL': 47, '2XL': 50, '3XL': 53, '4XL': 56, '5XL': 59 },
            'BUST': { 'S': 58, 'M': 63, 'L': 68, 'XL': 73, '2XL': 78, '3XL': 83, '4XL': 88, '5XL': 93 },
            'SLEEVE': { 'S': 37, 'M': 39, 'L': 41, 'XL': 43, '2XL': 45, '3XL': 47, '4XL': 49, '5XL': 51 }
        }
    }
};

function openSizeGuide() {
    const modal = document.getElementById('size-guide-modal');
    if (!modal) return;
    modal.classList.remove('hidden');
    document.body.classList.add('size-guide-open');
    updateSizeTable();
}

function closeSizeGuide() {
    const modal = document.getElementById('size-guide-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.classList.remove('size-guide-open');
}

function selectGender(gender) {
    currentGender = gender;
    document.querySelectorAll('[data-size-guide-gender]').forEach(function (btn) {
        var active = btn.getAttribute('data-size-guide-gender') === gender;
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    updateSizeTable();
}

function selectUnit(unit) {
    currentUnit = unit;
    document.querySelectorAll('[data-size-guide-unit]').forEach(function (btn) {
        btn.classList.toggle('is-active', btn.getAttribute('data-size-guide-unit') === unit);
    });
    updateSizeTable();
}

function updateSizeTable() {
    const productTypeEl = document.getElementById('product-type-selector');
    const tableBody = document.getElementById('size-table-body');
    if (!productTypeEl || !tableBody) return;

    const productType = productTypeEl.value;

    if (!sizeData[productType] || !sizeData[productType][currentGender]) {
        tableBody.innerHTML = '<tr><td colspan="9" class="size-guide-table__empty">No size data available for this selection</td></tr>';
        return;
    }

    const measurements = sizeData[productType][currentGender];
    const sizes = ['S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'];
    let tableHTML = '';

    Object.entries(measurements).forEach(function (entry) {
        const measurement = entry[0];
        const values = entry[1];
        tableHTML += '<tr class="size-guide-table__row">';
        tableHTML += '<td class="size-guide-table__measure">' + measurement + '</td>';

        sizes.forEach(function (size) {
            let value = values[size] || '—';
            if (value !== '—' && currentUnit === 'inches') {
                value = Math.round(value * 0.393701);
            }
            tableHTML += '<td>' + value + '</td>';
        });

        tableHTML += '</tr>';
    });

    tableBody.innerHTML = tableHTML;
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSizeGuide();
    }
});

// Close modal on overlay click
document.addEventListener('DOMContentLoaded', function() {
    const sizeGuideModal = document.getElementById('size-guide-modal');
    if (sizeGuideModal) {
        sizeGuideModal.addEventListener('click', function(e) {
            if (e.target === sizeGuideModal) {
                closeSizeGuide();
            }
        });
    }

    var pdpFlashSale = document.getElementById('pdpFlashSale');
    if (pdpFlashSale) {
        var flashEndsAt = new Date(pdpFlashSale.dataset.endsAt).getTime();
        var pdpFlashHours = document.getElementById('pdpFlashHours');
        var pdpFlashMinutes = document.getElementById('pdpFlashMinutes');
        var pdpFlashSeconds = document.getElementById('pdpFlashSeconds');

        function padPdpFlashTime(value) {
            return String(Math.max(0, value)).padStart(2, '0');
        }

        function tickPdpFlashCountdown() {
            var diff = flashEndsAt - Date.now();
            if (diff <= 0) {
                if (pdpFlashHours) pdpFlashHours.textContent = '00';
                if (pdpFlashMinutes) pdpFlashMinutes.textContent = '00';
                if (pdpFlashSeconds) pdpFlashSeconds.textContent = '00';
                return;
            }

            var totalSeconds = Math.floor(diff / 1000);
            var hours = Math.floor(totalSeconds / 3600);
            var minutes = Math.floor((totalSeconds % 3600) / 60);
            var seconds = totalSeconds % 60;

            if (pdpFlashHours) pdpFlashHours.textContent = padPdpFlashTime(hours);
            if (pdpFlashMinutes) pdpFlashMinutes.textContent = padPdpFlashTime(minutes);
            if (pdpFlashSeconds) pdpFlashSeconds.textContent = padPdpFlashTime(seconds);
        }

        tickPdpFlashCountdown();
        setInterval(tickPdpFlashCountdown, 1000);
    }
});
</script>
<!-- Size Guide Modal -->
<div id="size-guide-modal" class="size-guide-modal hidden" role="dialog" aria-modal="true" aria-labelledby="size-guide-modal-title">
    <div class="size-guide-modal__panel">
        <div class="size-guide-modal__head">
            <h2 id="size-guide-modal-title" class="size-guide-modal__title">Size &amp; Fit Info</h2>
            <button type="button" class="size-guide-modal__close" onclick="closeSizeGuide()" aria-label="Close size guide">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="size-guide-modal__body">
            <p class="size-guide-modal__intro">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>If you're in between sizes, order a size up — our items can shrink up to half a size in the wash.</span>
            </p>

            <div class="size-guide-modal__section">
                <span class="size-guide-modal__label" id="size-guide-gender-label">Fit profile</span>
                <div class="size-guide-gender" role="tablist" aria-labelledby="size-guide-gender-label">
                    <button type="button" class="size-guide-chip is-active" data-size-guide-gender="male" onclick="selectGender('male')" role="tab" aria-selected="true">Male</button>
                    <button type="button" class="size-guide-chip" data-size-guide-gender="female" onclick="selectGender('female')" role="tab" aria-selected="false">Female</button>
                    <button type="button" class="size-guide-chip" data-size-guide-gender="youth" onclick="selectGender('youth')" role="tab" aria-selected="false">Youth</button>
                    <button type="button" class="size-guide-chip" data-size-guide-gender="unisex" onclick="selectGender('unisex')" role="tab" aria-selected="false">Unisex</button>
                    <button type="button" class="size-guide-chip" data-size-guide-gender="kids" onclick="selectGender('kids')" role="tab" aria-selected="false">Kids</button>
                </div>
            </div>

            <div class="size-guide-modal__section">
                <label class="size-guide-modal__label" for="product-type-selector">Product type</label>
                <select id="product-type-selector" onchange="updateSizeTable()" class="size-guide-modal__select">
                    <option value="baseball-jackets">Baseball Jackets</option>
                    <option value="t-shirts">T-Shirts</option>
                    <option value="hoodies">Hoodies</option>
                    <option value="t-shirts">Tank Tops</option>
                    <option value="t-shirts">Long Sleeve</option>
                </select>
            </div>

            <div class="size-guide-table-card">
                <div class="size-guide-table-card__head">
                    <h3 class="size-guide-table-card__title">Product measurements</h3>
                    <div class="size-guide-unit" role="group" aria-label="Measurement unit">
                        <button type="button" class="size-guide-unit__btn is-active" data-size-guide-unit="cm" onclick="selectUnit('cm')">cm</button>
                        <button type="button" class="size-guide-unit__btn" data-size-guide-unit="inches" onclick="selectUnit('inches')">in</button>
                    </div>
                </div>
                <div class="size-guide-table-wrap">
                    <table class="size-guide-table">
                        <thead>
                            <tr>
                                <th scope="col">Measure</th>
                                <th scope="col">S</th>
                                <th scope="col">M</th>
                                <th scope="col">L</th>
                                <th scope="col">XL</th>
                                <th scope="col">2XL</th>
                                <th scope="col">3XL</th>
                                <th scope="col">4XL</th>
                                <th scope="col">5XL</th>
                            </tr>
                        </thead>
                        <tbody id="size-table-body"></tbody>
                    </table>
                </div>
                <span class="size-guide-table-scroll-hint">Swipe horizontally to see all sizes</span>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div id="share-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-gray-900">Share product</h3>
            <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center mb-6">
            <a id="share-mail" target="_blank" rel="noopener" class="group">
                <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h18a2 2 0 002-2V8a2 2 0 00-2-2H3a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="text-sm font-medium">Mail</div>
            </a>

            <a id="share-facebook" target="_blank" rel="noopener" class="group">
                <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987H7.898v-2.89h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.463h-1.261c-1.243 0-1.63.772-1.63 1.562v1.875h2.773l-.443 2.89h-2.33V21.88C18.343 21.128 22 16.991 22 12"></path>
                    </svg>
                </div>
                <div class="text-sm font-medium">Facebook</div>
            </a>

            <a id="share-pinterest" target="_blank" rel="noopener" class="group">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.04 2C6.58 2 4 5.64 4 8.94c0 1.89.72 3.57 2.27 4.2.25.11.47 0 .54-.27.05-.19.17-.67.22-.87.07-.27.04-.36-.15-.59-.44-.53-.73-1.22-.73-2.2 0-2.84 2.13-5.4 5.53-5.4 3.01 0 4.66 1.84 4.66 4.29 0 3.23-1.43 5.96-3.55 5.96-1.17 0-2.04-.97-1.76-2.16.34-1.44 1-2.99 1-4.03 0-.93-.5-1.7-1.54-1.7-1.22 0-2.2 1.26-2.2 2.95 0 1.08.36 1.81.36 1.81l-1.46 6.18c-.43 1.82-.06 4.05-.03 4.27.02.13.19.16.27.06.11-.14 1.5-1.86 1.97-3.57.13-.48.76-2.99.76-2.99.38.73 1.49 1.37 2.67 1.37 3.52 0 5.91-3.21 5.91-7.52C19.23 5.35 16.4 2 12.04 2z"></path>
                    </svg>
                </div>
                <div class="text-sm font-medium">Pinterest</div>
            </a>

            <a id="share-twitter" target="_blank" rel="noopener" class="group">
                <div class="w-14 h-14 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center mx-auto mb-2 group-hover:scale-105 transition-transform">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.46 6c-.77.35-1.6.59-2.46.7a4.27 4.27 0 001.87-2.36 8.51 8.51 0 01-2.7 1.03 4.25 4.25 0 00-7.24 3.88 12.07 12.07 0 01-8.76-4.44 4.24 4.24 0 001.32 5.67 4.22 4.22 0 01-1.93-.53v.05a4.25 4.25 0 003.41 4.17 4.26 4.26 0 01-1.92.07 4.25 4.25 0 003.97 2.95A8.53 8.53 0 012 19.54a12.04 12.04 0 006.53 1.92c7.84 0 12.13-6.5 12.13-12.13 0-.18-.01-.36-.02-.54A8.64 8.64 0 0022.46 6z"></path>
                    </svg>
                </div>
                <div class="text-sm font-medium">Twitter</div>
            </a>
        </div>

        <div class="text-center text-gray-500 mb-3">or copy this link</div>
        <div class="flex gap-2">
            <input id="share-link" type="text" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ route('products.show', $product->slug) }}">
            <button onclick="copyShareLink()" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium">Copy</button>
        </div>
    </div>
</div>

<script>
function openShareModal() {
    const url = `{{ route('products.show', $product->slug) }}`;
    const title = `{{ addslashes($product->name) }}`;
    const image = @json(($media && count($media) > 0) ? (is_string($media[0]) ? $media[0] : ($media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? '')) : '');

    // Build share links
    document.getElementById('share-mail').href = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(url)}`;
    document.getElementById('share-facebook').href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
    const pinParams = new URLSearchParams({ url, media: image || url, description: title });
    document.getElementById('share-pinterest').href = `https://pinterest.com/pin/create/button/?${pinParams.toString()}`;
    document.getElementById('share-twitter').href = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;

    // Set input value
    const input = document.getElementById('share-link');
    if (input) input.value = url;

    // Show modal
    const modal = document.getElementById('share-modal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeShareModal() {
    const modal = document.getElementById('share-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}
function copyShareLink() {
    const input = document.getElementById('share-link');
    input.select();
    input.setSelectionRange(0, 99999);
    try {
        document.execCommand('copy');
    } catch (e) {
        navigator.clipboard?.writeText(input.value);
    }
    showCartSuccess('Copied link to clipboard');
}

// Close on backdrop click
document.addEventListener('DOMContentLoaded', function() {
    const shareModal = document.getElementById('share-modal');
    if (shareModal) {
        shareModal.addEventListener('click', function(e) {
            if (e.target === this) closeShareModal();
        });
    }
    
    // Update delivery location based on domain
    updateDeliveryLocation();
});

/**
 * Update delivery location and estimate based on current domain and default shipping rate
 */
function updateDeliveryLocation() {
    const customerLocationEl = document.getElementById('customer-location');
    const deliveryEstimateEl = document.getElementById('delivery-estimate');
    
    if (!customerLocationEl || !deliveryEstimateEl) return;
    
    // Get location from domain configuration
    let locationName = 'your location';
    let deliveryEstimate = 'Calculating delivery time...';
    
    // Use default shipping rate information if available
    if (typeof DEFAULT_SHIPPING_RATE !== 'undefined' && DEFAULT_SHIPPING_RATE) {
        // Use description from default shipping rate if available
        if (DEFAULT_SHIPPING_RATE.description) {
            deliveryEstimate = DEFAULT_SHIPPING_RATE.description;
        }
        
        // Use zone name from default shipping rate if available
        if (DEFAULT_SHIPPING_RATE.zone_name) {
            locationName = DEFAULT_SHIPPING_RATE.zone_name;
        }
    }
    
    // Try to get location from domain or shipping zones if not set from default rate
    if (locationName === 'your location' && typeof CURRENT_DOMAIN !== 'undefined' && CURRENT_DOMAIN) {
        // Map domain to country/region name
        const domainMap = {
            'us': 'United States',
            'uk': 'United Kingdom',
            'ca': 'Canada',
            'au': 'Australia',
            'mx': 'Mexico',
            'eu': 'Europe',
            'asia': 'Asia',
            'global': 'International'
        };
        
        const domainKey = CURRENT_DOMAIN.toLowerCase();
        locationName = domainMap[domainKey] || CURRENT_DOMAIN.toUpperCase();
    }
    
    // If we have shipping zones with countries, try to get a representative country
    if (locationName === 'your location' && DOMAIN_COUNTRIES && DOMAIN_COUNTRIES.length > 0) {
        // Get the first country as representative
        const firstCountry = DOMAIN_COUNTRIES[0];
        const countryNames = {
            'US': 'United States',
            'GB': 'United Kingdom',
            'CA': 'Canada',
            'AU': 'Australia',
            'MX': 'Mexico',
            'DE': 'Germany',
            'FR': 'France',
            'IT': 'Italy',
            'ES': 'Spain',
            'NL': 'Netherlands',
            'BE': 'Belgium',
            'CH': 'Switzerland',
            'AT': 'Austria',
            'SE': 'Sweden',
            'NO': 'Norway',
            'DK': 'Denmark',
            'FI': 'Finland',
            'IE': 'Ireland',
            'PT': 'Portugal',
            'GR': 'Greece',
            'PL': 'Poland',
            'CZ': 'Czech Republic',
            'HU': 'Hungary',
            'RO': 'Romania',
            'BG': 'Bulgaria',
            'HR': 'Croatia',
            'SK': 'Slovakia',
            'SI': 'Slovenia',
            'EE': 'Estonia',
            'LV': 'Latvia',
            'LT': 'Lithuania',
            'JP': 'Japan',
            'CN': 'China',
            'KR': 'South Korea',
            'SG': 'Singapore',
            'MY': 'Malaysia',
            'TH': 'Thailand',
            'ID': 'Indonesia',
            'PH': 'Philippines',
            'VN': 'Vietnam',
            'IN': 'India',
            'NZ': 'New Zealand',
            'BR': 'Brazil',
            'AR': 'Argentina',
            'CL': 'Chile',
            'CO': 'Colombia',
            'PE': 'Peru',
            'ZA': 'South Africa',
            'EG': 'Egypt',
            'AE': 'United Arab Emirates',
            'SA': 'Saudi Arabia',
            'IL': 'Israel',
            'TR': 'Turkey',
            'RU': 'Russia',
            'UA': 'Ukraine'
        };
        
        const countryCode = firstCountry.code;
        locationName = countryNames[countryCode] || countryCode;
        
        // If multiple countries, show zone name
        if (DOMAIN_COUNTRIES.length > 1 && firstCountry.zone_name) {
            locationName = firstCountry.zone_name;
        }
    }
    
    // Set delivery estimate based on domain/zone if not set from default shipping rate
    if (deliveryEstimate === 'Calculating delivery time...' && DOMAIN_COUNTRIES && DOMAIN_COUNTRIES.length > 0) {
        // Estimate delivery time (can be customized per zone)
        deliveryEstimate = 'Estimated delivery: 5-10 business days';
        
        // You can customize this based on zone if needed
        if (DOMAIN_COUNTRIES[0].zone_name) {
            const zoneName = DOMAIN_COUNTRIES[0].zone_name.toLowerCase();
            if (zoneName.includes('domestic') || zoneName.includes('local')) {
                deliveryEstimate = 'Estimated delivery: 3-5 business days';
            } else if (zoneName.includes('international') || zoneName.includes('global')) {
                deliveryEstimate = 'Estimated delivery: 10-15 business days';
            }
        }
    }
    
    // Update the UI
    customerLocationEl.textContent = locationName;
    deliveryEstimateEl.textContent = deliveryEstimate;
}
</script>

@if($product->shop)
<div id="pdpContactModal" class="catalog-modal hidden" role="dialog" aria-modal="true" aria-labelledby="pdp-contact-modal-title">
    <div class="catalog-modal__panel">
        <div class="catalog-modal__head">
            <h2 id="pdp-contact-modal-title" class="catalog-modal__title">Message {{ $product->shop->shop_name }}</h2>
            <button type="button" class="catalog-modal__close" data-pdp-contact-close aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="pdpContactForm">
            <label class="catalog-modal__label" for="pdpContactSubject">Subject</label>
            <input type="text" id="pdpContactSubject" name="subject" required class="catalog-modal__input" value="Question about {{ $product->name }}">

            <label class="catalog-modal__label" for="pdpContactMessage">Message</label>
            <textarea id="pdpContactMessage" name="message" rows="4" required class="catalog-modal__textarea" placeholder="Ask about customization, sizing, or delivery before you order."></textarea>

            <div class="catalog-modal__actions">
                <button type="button" class="btn-outline-petrol" data-pdp-contact-close>Cancel</button>
                <button type="submit" class="btn-cta">Send message</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var pdpContactModal = document.getElementById('pdpContactModal');
    var pdpContactForm = document.getElementById('pdpContactForm');
    var pdpContactOpen = document.getElementById('pdpContactShopBtn');
    var pdpContactUrl = @json(route('shops.contact', $product->shop));

    function openPdpContactModal() {
        if (!pdpContactModal) return;
        pdpContactModal.classList.remove('hidden');
    }
    function closePdpContactModal() {
        if (!pdpContactModal) return;
        pdpContactModal.classList.add('hidden');
    }

    if (pdpContactOpen) {
        pdpContactOpen.addEventListener('click', openPdpContactModal);
    }
    document.querySelectorAll('[data-pdp-contact-close]').forEach(function (btn) {
        btn.addEventListener('click', closePdpContactModal);
    });
    if (pdpContactModal) {
        pdpContactModal.addEventListener('click', function (e) {
            if (e.target === pdpContactModal) closePdpContactModal();
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closePdpContactModal();
    });

    if (pdpContactForm) {
        pdpContactForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = pdpContactForm.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(pdpContactUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    subject: document.getElementById('pdpContactSubject').value,
                    message: document.getElementById('pdpContactMessage').value
                })
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    if (typeof showNotification === 'function') {
                        showNotification(data.message, 'success');
                    }
                    pdpContactForm.reset();
                    var subjectEl = document.getElementById('pdpContactSubject');
                    if (subjectEl) {
                        subjectEl.value = @json('Question about ' . $product->name);
                    }
                    closePdpContactModal();
                } else if (typeof showNotification === 'function') {
                    showNotification(data.message || 'Could not send message.', 'error');
                }
            })
            .catch(function () {
                if (typeof showNotification === 'function') {
                    showNotification('An error occurred. Please try again.', 'error');
                }
            })
            .finally(function () {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    }
});
</script>
@endif

@include('studio.partials.ai-progress')
<script>
    window.PDP_AI_ROUTES = {
        upload: @json(route('studio.upload')),
        redesign: @json(route('studio.product.redesign')),
        timeout: {{ (int) (\App\Support\StudioAiSettings::resolved()['timeout'] ?? 90) }},
    };
</script>
<script src="{{ asset('js/studio-ai-progress.js') }}?v={{ @filemtime(public_path('js/studio-ai-progress.js')) }}"></script>
<script src="{{ asset('js/product-ai-redesign.js') }}?v={{ @filemtime(public_path('js/product-ai-redesign.js')) }}"></script>

@endsection
