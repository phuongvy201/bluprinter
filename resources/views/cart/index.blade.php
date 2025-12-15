@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
@php
    $currentCurrency = currency();
    $currencySymbol = currency_symbol();
    $currentCurrencyRate = currency_rate() ?? 1.0;
    
    // Get current domain
    $currentDomain = \App\Services\CurrencyService::getCurrentDomain();
    
    // Get all shipping rates for current domain with zones
    $shippingRates = \App\Models\ShippingRate::where('domain', $currentDomain)
        ->where('is_active', true)
        ->with('shippingZone')
        ->orderBy('is_default', 'desc')
        ->orderBy('sort_order')
        ->get();
    
    // Get default shipping rate (the one with is_default = true)
    $defaultShippingRate = $shippingRates->where('is_default', true)->first();
    
    // If no default rate, use the first active rate
    if (!$defaultShippingRate && $shippingRates->count() > 0) {
        $defaultShippingRate = $shippingRates->first();
    }
    
    // Get unique shipping zones from rates
    $shippingZones = $shippingRates->pluck('shippingZone')
        ->filter()
        ->unique('id')
        ->sortBy('sort_order')
        ->values();
    
    // Prepare shipping rates data for JavaScript (grouped by zone)
    $shippingRatesByZone = [];
    $shippingRatesData = [];
    
    foreach ($shippingRates as $rate) {
        $rateData = [
            'id' => $rate->id,
            'zone_id' => $rate->shipping_zone_id,
            'zone_name' => $rate->shippingZone ? $rate->shippingZone->name : null,
            'category_id' => $rate->category_id,
            'name' => $rate->name,
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
                'zone_name' => $rate->shippingZone ? $rate->shippingZone->name : 'General',
                'rates' => []
            ];
        }
        $shippingRatesByZone[$zoneId]['rates'][] = $rateData;
    }
    
    // Prepare zones data for dropdown
    $zonesData = $shippingZones->map(function($zone) {
        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'description' => $zone->description,
        ];
    })->toArray();
    
    // Prepare default shipping rate data for JavaScript
    $defaultShippingRateData = null;
    if ($defaultShippingRate) {
        $defaultShippingRateData = [
            'id' => $defaultShippingRate->id,
            'category_id' => $defaultShippingRate->category_id,
            'name' => $defaultShippingRate->name,
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
    
    // Calculate base subtotal in USD for shipping calculation
    $baseSubtotal = 0;
    foreach ($cartItems as $item) {
        $itemPrice = (float) $item->price;
        // Convert to USD if needed
        $basePrice = $currentCurrency !== 'USD' && $currentCurrencyRate > 0 
            ? $itemPrice / $currentCurrencyRate 
            : $itemPrice;
        
        // Add customization prices
        $customizationTotal = 0;
        if ($item->customizations) {
            foreach ($item->customizations as $customization) {
                if (isset($customization['price']) && $customization['price'] > 0) {
                    $customPrice = (float) $customization['price'];
                    $baseCustomPrice = $currentCurrency !== 'USD' && $currentCurrencyRate > 0
                        ? $customPrice / $currentCurrencyRate
                        : $customPrice;
                    $customizationTotal += $baseCustomPrice;
                }
            }
        }
        
        $baseSubtotal += ($basePrice + $customizationTotal) * $item->quantity;
    }
@endphp
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
            <p class="text-gray-600 mt-2">Review your items and proceed to checkout</p>
        </div>

        @if($cartItems->isEmpty())
            <!-- Empty Cart -->
            <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
                <div class="max-w-md mx-auto">
                    <svg class="w-32 h-32 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                    </svg>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
                    <p class="text-gray-600 mb-8">Looks like you haven't added anything to your cart yet.</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center space-x-2 bg-[#005366] text-white px-8 py-3 rounded-xl hover:bg-[#003d4d] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Continue Shopping</span>
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cartItems as $item)
                        <div class="bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition-shadow" data-cart-item-id="{{ $item->id }}">
                            <div class="flex flex-col sm:flex-row gap-6">
                                <!-- Product Image -->
                                <div class="flex-shrink-0">
                                    @php
                                        $media = $item->product->getEffectiveMedia();
                                        $imageUrl = '/images/placeholder.jpg';
                                        if ($media && count($media) > 0) {
                                            if (is_string($media[0])) {
                                                $imageUrl = $media[0];
                                            } elseif (is_array($media[0])) {
                                                $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? '/images/placeholder.jpg';
                                            }
                                        }
                                    @endphp
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $item->product->name }}" 
                                         class="w-full sm:w-32 h-32 object-cover rounded-xl">
                                </div>

                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                                <a href="{{ route('products.show', $item->product->slug) }}" class="hover:text-[#005366]">
                                                    {{ $item->product->name }}
                                                </a>
                                            </h3>
                                            @if($item->product->shop)
                                                <p class="text-sm text-gray-500">
                                                    Sold by: <span class="text-[#005366] font-medium">{{ $item->product->shop->name }}</span>
                                                </p>
                                            @endif
                                        </div>
                                        <div class="flex gap-2">
                                            <button onclick="openEditCartModal({{ $item->id }})" class="p-2 text-gray-400 hover:text-[#005366] transition-colors" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="removeFromCart({{ $item->id }})" class="p-2 text-gray-400 hover:text-red-500 transition-colors" title="Remove">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Variant Info -->
                                    @if($item->selected_variant && isset($item->selected_variant['attributes']) && is_array($item->selected_variant['attributes']))
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            @foreach($item->selected_variant['attributes'] as $key => $value)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                    {{ $key }}: {{ $value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($item->selected_variant && is_array($item->selected_variant))
                                        {{-- Handle legacy data structure --}}
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            @if(isset($item->selected_variant['colour']) && !empty($item->selected_variant['colour']))
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                    Colour: {{ $item->selected_variant['colour'] }}
                                                </span>
                                            @endif
                                            @if(isset($item->selected_variant['size']) && !empty($item->selected_variant['size']))
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                    Size: {{ $item->selected_variant['size'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Customizations -->
                                    @if($item->customizations && count($item->customizations) > 0)
                                        <div class="mb-3">
                                            <p class="text-xs text-gray-500 mb-1">Customizations:</p>
                                            @foreach($item->customizations as $key => $customization)
                                                <p class="text-sm text-gray-700">
                                                    <span class="font-medium">{{ $key }}:</span> {{ $customization['value'] }}
                                                    @if(isset($customization['price']) && $customization['price'] > 0)
                                                        <span class="text-[#005366]">(+{{ format_price((float) $customization['price']) }})</span>
                                                    @endif
                                                </p>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Price and Quantity -->
                                    <div class="flex items-center justify-between mt-4">
                                        <div class="flex items-center space-x-3">
                                            <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" 
                                                    class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                    {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                </svg>
                                            </button>
                                            <span class="text-lg font-semibold min-w-[2rem] text-center">{{ $item->quantity }}</span>
                                            <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" 
                                                    class="w-8 h-8 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-2xl font-bold text-[#005366]">{{ format_price((float) $item->getTotalPriceWithCustomizations()) }}</p>
                                            @if($item->quantity > 1)
                                                <p class="text-sm text-gray-500">{{ format_price((float) ($item->getTotalPriceWithCustomizations() / $item->quantity)) }} each</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal ({{ $cartItems->sum('quantity') }} items)</span>
                                <span class="font-semibold" id="cart-subtotal">{{ format_price((float) $subtotal) }}</span>
                            </div>
                            
                            <!-- Shipping Zone Selector -->
                            @if(count($zonesData) > 1)
                                <div class="flex items-center justify-between text-gray-600 mb-2">
                                    <label for="shipping-zone-select" class="text-sm font-medium">Shipping Zone:</label>
                                    <select id="shipping-zone-select" 
                                            onchange="updateShippingZone(this.value)" 
                                            class="ml-2 px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                                        @foreach($zonesData as $zone)
                                            <option value="{{ $zone['id'] }}" 
                                                    @if($defaultShippingRate && $defaultShippingRate->shipping_zone_id == $zone['id']) selected @endif>
                                                {{ $zone['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            
                            <div class="flex justify-between text-gray-600" id="shipping-cost-row">
                                <span id="shipping-label">Shipping</span>
                                <span class="font-semibold" id="shipping-cost">{{ format_price(0) }}</span>
                            </div>
                            
                            <div class="border-t pt-3 flex justify-between text-lg font-bold text-gray-900">
                                <span>Total</span>
                                <span class="text-[#005366]" id="cart-total">{{ format_price((float) $subtotal) }}</span>
                            </div>
                        </div>

                        <!-- Main Checkout Button -->
                        <a href="{{ route('checkout.index') }}" 
                           onclick="trackInitiateCheckout(event)"
                           class="block w-full bg-[#E2150C] hover:bg-[#c4120a] text-white font-bold py-4 rounded-xl transition-colors duration-200 mb-4 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span>CHECKOUT</span>
                        </a>

                        <!-- Express Checkout Section -->
                        <div class="text-center mb-4">
                            <p class="text-sm text-gray-500 mb-3">Express checkout</p>
                            <div class="flex flex-wrap justify-center gap-2 mb-4">
                                <!-- Payment Method Icons -->
                                <div class="flex items-center justify-center w-12 h-8 bg-gray-100 rounded border">
                                    <span class="text-xs font-bold text-blue-600">AMEX</span>
                                </div>
                                <div class="flex items-center justify-center w-12 h-8 bg-gray-100 rounded border">
                                    <span class="text-xs font-bold text-blue-600">VISA</span>
                                </div>
                                <div class="flex items-center justify-center w-12 h-8 bg-gray-100 rounded border">
                                    <span class="text-xs font-bold text-red-600">MC</span>
                                </div>
                               
                                <div class="flex items-center justify-center w-12 h-8 bg-gray-100 rounded border">
                                    <span class="text-xs font-bold text-blue-600">PayPal</span>
                                </div>
                               
                            </div>
                        </div>


                        <a href="{{ route('products.index') }}" class="block w-full text-center text-[#005366] hover:text-[#003d4d] font-medium py-3 border-2 border-[#005366] rounded-xl hover:bg-[#005366] hover:text-white transition-all duration-200 mb-6">
                            Continue Shopping
                        </a>

                        {{-- Customer Reviews Section - Disabled --}}

                        <!-- Guarantee Section -->
                        <div class="mt-6 pt-6 border-t">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-16 h-16 border-2 border-dashed border-gray-300 rounded-full flex items-center justify-center">
                                        <div class="text-center">
                                            <div class="text-xs font-bold text-gray-700">GUARANTEE</div>
                                            <div class="text-xs font-bold text-gray-700">PERFECT FIT</div>
                                            <div class="flex justify-center mt-1">
                                                <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                                <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                                <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 mb-1">Don't love it? We'll fix it. For free.</p>
                                    <a href="#" class="text-sm text-[#005366] hover:text-[#003d4d] font-medium">
                                        Bluprinter Guarantee »
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Edit Cart Modal -->
        <div id="editCartModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center z-10">
                    <h2 class="text-2xl font-bold text-gray-900">Edit Cart Item</h2>
                    <button onclick="closeEditCartModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="editCartModalContent" class="p-6">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>


        <!-- Recently Viewed Products -->
        <div id="recentlyViewedSection" class="mt-12" style="display: none;">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Recently Viewed Products</h2>
                    <p class="text-gray-600 mt-1">Continue shopping from where you left off</p>
                </div>
                <!-- Mobile Navigation Buttons -->
                <div class="flex gap-2 lg:hidden">
                    <button id="recentlyViewedPrevBtnMobile" class="p-2 bg-white rounded-full shadow-md hover:bg-gray-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button id="recentlyViewedNextBtnMobile" class="p-2 bg-white rounded-full shadow-md hover:bg-gray-50 transition-all disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Products Container -->
            <div class="relative group">
                <!-- Desktop Navigation Buttons -->
                <button id="recentlyViewedPrevBtnDesktop" class="hidden lg:flex absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-10 w-12 h-12 bg-white rounded-full shadow-lg items-center justify-center hover:bg-gray-50 transition-all disabled:opacity-0 disabled:cursor-not-allowed opacity-0 group-hover:opacity-100">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button id="recentlyViewedNextBtnDesktop" class="hidden lg:flex absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-10 w-12 h-12 bg-white rounded-full shadow-lg items-center justify-center hover:bg-gray-50 transition-all disabled:opacity-0 disabled:cursor-not-allowed opacity-0 group-hover:opacity-100">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                
                <div id="recentlyViewedContainer" class="overflow-x-auto hide-scrollbar" style="scroll-behavior: smooth;">
                    <div id="recentlyViewedGrid" class="flex gap-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles for Recently Viewed -->
<style>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<!-- JavaScript for Cart Operations -->
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const cartItemsData = @json($cartItems);
const CURRENCY_SYMBOL = @json($currencySymbol);
const CURRENT_CURRENCY = @json($currentCurrency);
const CURRENT_CURRENCY_RATE = {{ $currentCurrencyRate }};
const SHIPPING_RATES = @json($shippingRatesData);
const SHIPPING_RATES_BY_ZONE = @json($shippingRatesByZone);
const SHIPPING_ZONES = @json($zonesData);
const DEFAULT_SHIPPING_RATE = @json($defaultShippingRateData);
const DEFAULT_SHIPPING_ZONE_ID = @json($defaultShippingRate ? $defaultShippingRate->shipping_zone_id : null);
const BASE_SUBTOTAL = {{ $baseSubtotal }};


function updateQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) return;
    
    fetch(`/api/cart/update/${cartItemId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ quantity: newQuantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update quantity');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function removeFromCart(cartItemId) {
    if (!confirm('Are you sure you want to remove this item?')) return;
    
    fetch(`/api/cart/remove/${cartItemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove from localStorage
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            cart = cart.filter(item => item.id !== cartItemId);
            localStorage.setItem('cart', JSON.stringify(cart));
            
            location.reload();
        } else {
            alert('Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Edit Cart Modal Functions
function openEditCartModal(cartItemId) {
    // Get cart item data from the page
    const cartItem = cartItemsData.find(item => item.id === cartItemId);
    
    if (!cartItem) {
        alert('Cart item not found');
        return;
    }
    
    // Show modal
    const modal = document.getElementById('editCartModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Build modal content
    const content = document.getElementById('editCartModalContent');
    content.innerHTML = buildEditCartModalContent(cartItem);
    window.__editingCartContext = {
        id: cartItemId,
        item: cartItem,
        originalCustomizations: cartItem.customizations || {},
        variants: (cartItem.product && cartItem.product.variants) ? cartItem.product.variants : []
    };
}

function closeEditCartModal() {
    const modal = document.getElementById('editCartModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function buildEditCartModalContent(cartItem) {
    const product = cartItem.product;
    const variants = product.variants || [];
    const selectedVariant = cartItem.selected_variant || {};
    const customizations = cartItem.customizations || {};
    
    return `
        <div class="space-y-6">
            <!-- Product Info -->
            <div class="flex gap-4">
                <img src="${getProductImage(product)}" alt="${product.name}" class="w-24 h-24 object-cover rounded-lg">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">${product.name}</h3>
                    <p class="text-gray-600">${CURRENCY_SYMBOL}${parseFloat(cartItem.price).toFixed(2)} each</p>
                </div>
            </div>
            
            <!-- Variants -->
            ${variants.length > 0 ? `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Variants</label>
                <div class="space-y-2">
                    ${buildVariantOptions(variants, selectedVariant)}
                </div>
            </div>
            ` : ''}

            ${Object.keys(customizations).length > 0 ? `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Customizations</label>
                <div class="space-y-3">
                    ${Object.entries(customizations).map(([key, value]) => `
                        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-center">
                            <div class="sm:col-span-2">
                                <span class="text-sm text-gray-600">${key}</span>
                            </div>
                            <div class="sm:col-span-3">
                                <input type="text" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 customization-input" 
                                       data-label="${key}" placeholder="Value" value="${(value && value.value) ? String(value.value).replace(/"/g, '&quot;') : ''}" 
                                       oninput="updateCartModalTotal()" />
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}
            
            <!-- Quantity -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <div class="flex items-center gap-3">
                    <button onclick="updateModalQuantity(${cartItem.id}, ${cartItem.quantity - 1})" 
                            class="w-10 h-10 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            ${cartItem.quantity <= 1 ? 'disabled' : ''}>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <span class="text-xl font-semibold" id="modalQuantity${cartItem.id}">${cartItem.quantity}</span>
                    <button onclick="updateModalQuantity(${cartItem.id}, ${cartItem.quantity + 1})" 
                            class="w-10 h-10 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Total Price -->
            <div class="border-t pt-4">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-[#005366]" id="modalTotal${cartItem.id}">${CURRENCY_SYMBOL}${(parseFloat(cartItem.price) * cartItem.quantity).toFixed(2)}</span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4">
                <button onclick="saveCartChanges(${cartItem.id})" 
                        class="flex-1 bg-[#005366] hover:bg-[#003d4d] text-white font-bold py-3 rounded-xl transition-colors">
                    Save Changes
                </button>
                <button onclick="closeEditCartModal()" 
                        class="px-6 py-3 border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-medium rounded-xl transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    `;
}

function buildVariantOptions(variants, selectedVariant) {
    // Group variants by attribute type
    const attributeGroups = {};
    variants.forEach(variant => {
        if (variant.attributes) {
            Object.keys(variant.attributes).forEach(key => {
                if (!attributeGroups[key]) {
                    attributeGroups[key] = new Set();
                }
                attributeGroups[key].add(variant.attributes[key]);
            });
        }
    });
    
    return Object.keys(attributeGroups).map(key => {
        const values = Array.from(attributeGroups[key]);
        const selectedValue = selectedVariant && selectedVariant.attributes ? selectedVariant.attributes[key] : '';
        
        return `
            <div>
                <label class="block text-sm text-gray-600 mb-1">${key.charAt(0).toUpperCase() + key.slice(1)}</label>
                <select class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 focus:border-[#005366] focus:outline-none" 
                        id="variant-${key}" 
                        onchange="updateCartModalTotal(${JSON.stringify(variants).replace(/"/g, '&quot;')})">
                    ${values.map(value => `
                        <option value="${value}" ${value === selectedValue ? 'selected' : ''}>${value}</option>
                    `).join('')}
                </select>
            </div>
        `;
    }).join('');
}

function getProductImage(product) {
    if (product.media && product.media.length > 0) {
        const media = product.media[0];
        if (typeof media === 'string') {
            return media;
        } else if (media.url) {
            return media.url;
        } else if (media.path) {
            return media.path;
        }
    }
    return '/images/placeholder.jpg';
}

function updateModalQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) return;
    
    // Update display
    const quantityDisplay = document.getElementById('modalQuantity' + cartItemId);
    if (quantityDisplay) {
        quantityDisplay.textContent = newQuantity;
    }
    
    // Update total
    updateCartModalTotal();
}

function updateCartModalTotal(variants) {
    const ctx = window.__editingCartContext;
    if (!ctx) return;
    if (Array.isArray(variants) && variants.length) {
        ctx.variants = variants;
    }
    const cartItemId = ctx.id;
    const cartItem = ctx.item;
    const quantity = parseInt(document.getElementById('modalQuantity' + cartItemId)?.textContent || '1');

    const selectedVariant = (function getSelectedVariant() {
        const vars = ctx.variants || [];
        if (!vars.length) return null;
        const attributes = {};
        vars.forEach(v => {
            if (v.attributes) {
                Object.keys(v.attributes).forEach(key => {
                    const sel = document.getElementById('variant-' + key);
                    if (sel) attributes[key] = sel.value;
                });
            }
        });
        const match = vars.find(v => v.attributes && Object.keys(attributes).every(k => String(v.attributes[k]) === String(attributes[k])));
        if (match) return { id: match.id, attributes: match.attributes, price: match.price };
        return Object.keys(attributes).length ? { attributes } : null;
    })();

    let unitPrice = (function getBaseUnitPrice() {
        if (selectedVariant && selectedVariant.price != null && selectedVariant.price !== '') {
            const v = parseFloat(selectedVariant.price);
            if (!isNaN(v)) return v;
        }
        const p = cartItem.product || {};
        const candidates = [p.price, p.base_price, (p.template || {}).base_price, cartItem.price];
        for (const c of candidates) {
            const v = parseFloat(c);
            if (!isNaN(v)) return v;
        }
        return 0;
    })();

    const customizations = (function getSelectedCustomizationsPreservePrice() {
        const map = {};
        document.querySelectorAll('.customization-input').forEach(input => {
            const label = input.dataset.label;
            const value = input.value || '';
            const original = ctx.originalCustomizations && ctx.originalCustomizations[label];
            const price = original && original.price ? parseFloat(original.price) || 0 : 0;
            if (value.trim() !== '') {
                map[label] = { value: value.trim(), price };
            }
        });
        return map;
    })();

    let customizationUnitTotal = 0;
    Object.values(customizations).forEach(c => { customizationUnitTotal += parseFloat(c.price) || 0; });
    const unitTotal = unitPrice + customizationUnitTotal;
    const total = unitTotal * quantity;
    const totalDisplay = document.getElementById('modalTotal' + cartItemId);
    if (totalDisplay) totalDisplay.textContent = `${CURRENCY_SYMBOL}${total.toFixed(2)}`;
}

function saveCartChanges(cartItemId) {
    const ctx = window.__editingCartContext;
    if (!ctx || ctx.id !== cartItemId) return;
    const cartItem = ctx.item;
    const newQuantity = parseInt(document.getElementById('modalQuantity' + cartItemId)?.textContent || '1');

    // Recompute payload like in updateCartModalTotal
    const vars = ctx.variants || [];
    const attributes = {};
    vars.forEach(v => {
        if (v.attributes) {
            Object.keys(v.attributes).forEach(key => {
                const sel = document.getElementById('variant-' + key);
                if (sel) attributes[key] = sel.value;
            });
        }
    });
    const match = vars.find(v => v.attributes && Object.keys(attributes).every(k => String(v.attributes[k]) === String(attributes[k])));
    const selectedVariant = match ? { id: match.id, attributes: match.attributes, price: match.price } : (Object.keys(attributes).length ? { attributes } : null);

    const customizations = {};
    document.querySelectorAll('.customization-input').forEach(input => {
        const label = input.dataset.label;
        const value = input.value || '';
        const original = ctx.originalCustomizations && ctx.originalCustomizations[label];
        const price = original && original.price ? parseFloat(original.price) || 0 : 0;
        if (value.trim() !== '') {
            customizations[label] = { value: value.trim(), price };
        }
    });

    let unitPrice = (function () {
        if (selectedVariant && selectedVariant.price != null && selectedVariant.price !== '') {
            const v = parseFloat(selectedVariant.price);
            if (!isNaN(v)) return v;
        }
        const p = cartItem.product || {};
        const candidates = [p.price, p.base_price, (p.template || {}).base_price, cartItem.price];
        for (const c of candidates) {
            const v = parseFloat(c);
            if (!isNaN(v)) return v;
        }
        return 0;
    })();
    Object.values(customizations).forEach(c => { unitPrice += parseFloat(c.price) || 0; });

    fetch(`/api/cart/update/${cartItemId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            quantity: newQuantity,
            selected_variant: selectedVariant,
            customizations: customizations,
            price: unitPrice
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update cart item');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('An error occurred');
    });
}

// Recently Viewed Products Functionality
function loadRecentlyViewed() {
    const recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
    
    if (recentlyViewed.length === 0) {
        document.getElementById('recentlyViewedSection').style.display = 'none';
        return;
    }

    // Show section
    document.getElementById('recentlyViewedSection').style.display = 'block';

    // Limit to 12 products
    const productsToShow = recentlyViewed.slice(0, 12);
    const container = document.getElementById('recentlyViewedGrid');
    
    container.innerHTML = productsToShow.map(product => `
        <div class="flex-shrink-0 w-48 bg-white rounded-lg overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow group">
            <a href="/products/${product.slug}" class="block">
                <div class="relative aspect-square overflow-hidden bg-gray-100">
                    <img src="${product.image}" 
                         alt="${product.name}" 
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                </div>
                <div class="p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-[#005366] transition-colors">${product.name}</h3>
                    <div class="flex items-center gap-1 mb-2">
                        <div class="flex">
                            ${Array(5).fill(0).map((_, i) => `
                                <svg class="w-3 h-3 ${i < 4 ? 'text-yellow-400' : 'text-gray-300'}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            `).join('')}
                        </div>
                        <span class="text-xs text-gray-500">4.5</span>
                    </div>
                    <p class="text-base font-bold text-[#005366]">${CURRENCY_SYMBOL}${parseFloat(product.price).toFixed(2)}</p>
                </div>
            </a>
        </div>
    `).join('');

    // Setup navigation
    setupNavigation();
}

function setupNavigation() {
    const container = document.getElementById('recentlyViewedContainer');
    const prevBtnMobile = document.getElementById('recentlyViewedPrevBtnMobile');
    const nextBtnMobile = document.getElementById('recentlyViewedNextBtnMobile');
    const prevBtnDesktop = document.getElementById('recentlyViewedPrevBtnDesktop');
    const nextBtnDesktop = document.getElementById('recentlyViewedNextBtnDesktop');
    
    if (!container) return;

    // Update button states on scroll
    function updateButtonStates() {
        const scrollLeft = container.scrollLeft;
        const maxScroll = container.scrollWidth - container.clientWidth;
        
        const isAtStart = scrollLeft <= 0;
        const isAtEnd = scrollLeft >= maxScroll - 1;
        
        // Update mobile buttons
        if (prevBtnMobile && nextBtnMobile) {
            prevBtnMobile.disabled = isAtStart;
            nextBtnMobile.disabled = isAtEnd;
        }
        
        // Update desktop buttons
        if (prevBtnDesktop && nextBtnDesktop) {
            prevBtnDesktop.disabled = isAtStart;
            nextBtnDesktop.disabled = isAtEnd;
        }
    }

    // Scroll by one item width (192px + 16px gap)
    const scrollAmount = 208;

    function scrollPrev() {
        container.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
        setTimeout(updateButtonStates, 300);
    }

    function scrollNext() {
        container.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
        setTimeout(updateButtonStates, 300);
    }

    // Attach event handlers
    if (prevBtnMobile) prevBtnMobile.onclick = scrollPrev;
    if (nextBtnMobile) nextBtnMobile.onclick = scrollNext;
    if (prevBtnDesktop) prevBtnDesktop.onclick = scrollPrev;
    if (nextBtnDesktop) nextBtnDesktop.onclick = scrollNext;

    // Update on scroll
    container.addEventListener('scroll', updateButtonStates);
    
    // Initial state
    updateButtonStates();
}


// Track InitiateCheckout when clicking Proceed to Checkout
function trackInitiateCheckout(event) {
    if (typeof fbq !== 'undefined') {
        // Get cart data
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        
        if (cart.length > 0) {
            // Calculate cart total and collect product IDs
            let cartTotal = 0;
            const productIds = [];
            
            cart.forEach(item => {
                const price = parseFloat(item.price) || 0;
                const quantity = parseInt(item.quantity) || 1;
                cartTotal += price * quantity;
                productIds.push(item.id);
            });
            
            // Track InitiateCheckout event
            fbq('track', 'InitiateCheckout', {
                content_ids: productIds,
                content_type: 'product',
                value: cartTotal.toFixed(2),
                currency: 'USD',
                num_items: cart.length
            });
            
            console.log('✅ Facebook Pixel: InitiateCheckout tracked from cart', {
                items: cart.length,
                total: cartTotal.toFixed(2),
                ids: productIds
            });
        }
    }

    // Event tracking được xử lý bởi GTM thông qua dataLayer
    if (typeof dataLayer !== 'undefined') {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');

        if (cart.length > 0) {
            let cartTotal = 0;

            const gaItems = cart.map((item, index) => {
                const quantity = parseInt(item.quantity, 10) || 1;
                const unitPrice = parseFloat(item.price) || 0;
                cartTotal += unitPrice * quantity;

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

            dataLayer.push({
                'event': 'begin_checkout',
                'currency': 'USD',
                'value': Number(cartTotal.toFixed(2)),
                'items': gaItems
            });

            console.log('✅ GTM: begin_checkout tracked from cart', {
                items: gaItems.length,
                value: cartTotal.toFixed(2)
            });
        }
    }
    // Let the link navigate normally
    return true;
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
    if (zoneId !== null) {
        availableRates = SHIPPING_RATES.filter(r => r.zone_id === zoneId);
        // If a specific zone is selected but no rates exist for it, shipping is not available
        if (availableRates.length === 0) {
            return {
                cost: 0,
                costConverted: 0,
                rate: null,
                name: null,
                zoneId: zoneId,
                zoneName: SHIPPING_ZONES.find(z => z.id === zoneId)?.name || null,
                available: false
            };
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
        
        // First, try to find rate specific to this category
        if (categoryId) {
            rate = availableRates.find(r => 
                r.category_id === categoryId && 
                (!r.min_items || quantity >= r.min_items) &&
                (!r.max_items || quantity <= r.max_items) &&
                (!r.min_order_value || baseSubtotal >= r.min_order_value) &&
                (!r.max_order_value || baseSubtotal <= r.max_order_value)
            );
        }
        
        // If no category-specific rate, try general rate (category_id is null)
        if (!rate) {
            rate = availableRates.find(r => 
                r.category_id === null &&
                (!r.min_items || quantity >= r.min_items) &&
                (!r.max_items || quantity <= r.max_items) &&
                (!r.min_order_value || baseSubtotal >= r.min_order_value) &&
                (!r.max_order_value || baseSubtotal <= r.max_order_value)
            );
        }
        
        // If still no rate found, use default shipping rate (if it meets conditions and matches zone)
        if (!rate && DEFAULT_SHIPPING_RATE) {
            const defaultRate = DEFAULT_SHIPPING_RATE;
            // Check if default rate meets the conditions and zone
            const meetsConditions = 
                (zoneId === null || defaultRate.zone_id === zoneId) &&
                (!defaultRate.min_items || quantity >= defaultRate.min_items) &&
                (!defaultRate.max_items || quantity <= defaultRate.max_items) &&
                (!defaultRate.min_order_value || baseSubtotal >= defaultRate.min_order_value) &&
                (!defaultRate.max_order_value || baseSubtotal <= defaultRate.max_order_value);
            
            if (meetsConditions) {
                rate = defaultRate;
            }
        }
        
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
    
    // If no rates found for any group, shipping is not available
    if (!allGroupsHaveRate || (totalShippingCost === 0 && !shippingRateUsed)) {
        return {
            cost: 0,
            costConverted: 0,
            rate: null,
            name: null,
            zoneId: zoneId,
            zoneName: zoneName,
            available: false
        };
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

/**
 * Update shipping zone and recalculate shipping cost
 * @param {string|number} zoneId - Selected shipping zone ID
 */
function updateShippingZone(zoneId) {
    if (!zoneId) return;
    
    zoneId = parseInt(zoneId);
    
    // Save selected zone to localStorage
    localStorage.setItem('selectedShippingZoneId', zoneId);
    
    // Calculate base subtotal from cart items
    const baseSubtotal = calculateBaseSubtotal(cartItemsData);
    
    // Calculate shipping cost with new zone
    const shippingInfo = calculateShippingCost(cartItemsData, baseSubtotal, zoneId);
    const shippingCost = shippingInfo.costConverted;
    
    // Get current subtotal
    const subtotalText = document.getElementById('cart-subtotal').textContent;
    const subtotal = parseFloat(subtotalText.replace(/[^0-9.-]/g, '')) || 0;
    
    // Calculate total
    const total = subtotal + shippingCost;
    
    // Update shipping cost display
    const shippingCostEl = document.getElementById('shipping-cost');
    const shippingLabelEl = document.getElementById('shipping-label');
    const totalEl = document.getElementById('cart-total');
    
    // Check if shipping is available
    if (shippingInfo.available === false) {
        if (shippingCostEl) {
            shippingCostEl.textContent = 'N/A';
            shippingCostEl.classList.add('text-red-600');
        }
        
        if (shippingLabelEl) {
            shippingLabelEl.textContent = 'Shipping not available for this area';
            shippingLabelEl.classList.add('text-red-600');
        }
    } else {
        if (shippingCostEl) {
            shippingCostEl.textContent = formatPrice(shippingCost);
            shippingCostEl.classList.remove('text-red-600');
        }
        
        if (shippingLabelEl) {
            shippingLabelEl.textContent = `Shipping${shippingInfo.zoneName ? ` (${shippingInfo.zoneName})` : shippingInfo.name ? ` (${shippingInfo.name})` : ''}`;
            shippingLabelEl.classList.remove('text-red-600');
        }
    }
    
    if (totalEl) {
        totalEl.textContent = formatPrice(total);
    }
}

/**
 * Format price with currency symbol
 */
function formatPrice(amount) {
    return `${CURRENCY_SYMBOL}${parseFloat(amount).toFixed(2)}`;
}

/**
 * Calculate base subtotal from cart items
 */
function calculateBaseSubtotal(cartItems) {
    let baseSubtotal = 0;
    
    cartItems.forEach(item => {
        const itemPrice = parseFloat(item.price) || 0;
        // Convert to USD if needed
        let basePrice = CURRENT_CURRENCY !== 'USD' && CURRENT_CURRENCY_RATE > 0 
            ? itemPrice / CURRENT_CURRENCY_RATE 
            : itemPrice;
        
        // Add customization prices
        let customizationTotal = 0;
        if (item.customizations) {
            Object.values(item.customizations).forEach(customization => {
                if (customization && customization.price) {
                    const customPrice = parseFloat(customization.price) || 0;
                    let baseCustomPrice = CURRENT_CURRENCY !== 'USD' && CURRENT_CURRENCY_RATE > 0
                        ? customPrice / CURRENT_CURRENCY_RATE
                        : customPrice;
                    customizationTotal += baseCustomPrice;
                }
            });
        }
        
        baseSubtotal += (basePrice + customizationTotal) * item.quantity;
    });
    
    return baseSubtotal;
}

// Initialize shipping cost on page load
function initializeShippingCost() {
    // Get selected zone from localStorage or use default
    let selectedZoneId = localStorage.getItem('selectedShippingZoneId');
    if (selectedZoneId) {
        selectedZoneId = parseInt(selectedZoneId);
        // Verify zone exists
        if (!SHIPPING_ZONES.find(z => z.id === selectedZoneId)) {
            selectedZoneId = DEFAULT_SHIPPING_ZONE_ID;
        }
    } else {
        selectedZoneId = DEFAULT_SHIPPING_ZONE_ID;
    }
    
    // Update dropdown if exists
    const zoneSelect = document.getElementById('shipping-zone-select');
    if (zoneSelect) {
        zoneSelect.value = selectedZoneId || '';
    }
    
    // Calculate base subtotal from cart items
    const baseSubtotal = calculateBaseSubtotal(cartItemsData);
    
    // Calculate and display shipping cost
    const shippingInfo = calculateShippingCost(cartItemsData, baseSubtotal, selectedZoneId);
    const shippingCost = shippingInfo.costConverted;
    
    // Get current subtotal
    const subtotalText = document.getElementById('cart-subtotal').textContent;
    const subtotal = parseFloat(subtotalText.replace(/[^0-9.-]/g, '')) || 0;
    
    // Calculate total
    const total = subtotal + shippingCost;
    
    // Update displays
    const shippingCostEl = document.getElementById('shipping-cost');
    const shippingLabelEl = document.getElementById('shipping-label');
    const totalEl = document.getElementById('cart-total');
    
    if (shippingCostEl) {
        shippingCostEl.textContent = formatPrice(shippingCost);
    }
    
    if (shippingLabelEl) {
        shippingLabelEl.textContent = `Shipping${shippingInfo.zoneName ? ` (${shippingInfo.zoneName})` : shippingInfo.name ? ` (${shippingInfo.name})` : ''}`;
    }
    
    if (totalEl) {
        totalEl.textContent = formatPrice(total);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecentlyViewed();
    initializeShippingCost();
});
</script>

<style>
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

</style>

@endsection

