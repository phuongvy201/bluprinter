@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
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
                                                        <span class="text-[#005366]">(+${{ number_format($customization['price'], 2) }})</span>
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
                                            <p class="text-2xl font-bold text-[#005366]">${{ number_format($item->getTotalPriceWithCustomizations(), 2) }}</p>
                                            @if($item->quantity > 1)
                                                <p class="text-sm text-gray-500">${{ number_format($item->getTotalPriceWithCustomizations() / $item->quantity, 2) }} each</p>
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
                                <span class="font-semibold">${{ number_format($subtotal, 2) }}</span>
                            </div>
                            
                            <!-- Shipping Details -->
                            <div class="border-t pt-3">
                                <div class="flex justify-between items-center text-gray-600 mb-3">
                                    <span>Shipping</span>
                                    <div class="flex items-center space-x-3">
                                        <div class="relative">
                                            <select id="shippingCountry" class="text-sm border-2 border-gray-200 rounded-lg px-3 py-2 appearance-none bg-white pr-8 cursor-pointer hover:border-gray-300 focus:border-[#005366] focus:outline-none transition-colors min-w-[90px] [&::-ms-expand]:hidden [&::-webkit-appearance]:none">
                                                <option value="US">🇺🇸 US</option>
                                                <option value="GB">🇬🇧 UK</option>
                                            </select>
                                            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <span id="shippingCost" class="font-semibold text-right min-w-[5rem] text-lg">
                                            @php
                                                $qualifiesForFreeShipping = $subtotal >= 100;
                                                $actualShipping = $qualifiesForFreeShipping ? 0 : $shipping;
                                            @endphp
                                            @if($actualShipping == 0)
                                                <span class="text-green-600">FREE</span>
                                            @else
                                                ${{ number_format($actualShipping, 2) }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                
                                @if($shippingDetails && $shipping > 0)
                                    <div class="text-xs text-gray-500">
                                        <div class="flex justify-between">
                                            <span>Zone:</span>
                                            <span>{{ $shippingDetails['zone_name'] ?? 'US' }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            @php
                                $qualifiesForFreeShipping = $subtotal >= 100;
                                $actualShipping = $qualifiesForFreeShipping ? 0 : $shipping;
                                $actualTotal = $subtotal + $actualShipping;
                            @endphp
                            <div class="border-t pt-3 flex justify-between text-lg font-bold text-gray-900">
                                <span>Total</span>
                                <span class="text-[#005366]">${{ number_format($actualTotal, 2) }}</span>
                            </div>
                            
                            {{-- Freeship Messages --}}
                            @if($qualifiesForFreeShipping)
                                <div class="text-xs text-green-600 bg-green-50 p-2 rounded mt-2">
                                    🎉 You qualify for free shipping on orders $100+!
                                </div>
                            @else
                                <div class="text-xs text-blue-600 mt-2">
                                    Add ${{ number_format(100 - $subtotal, 2) }} more for free shipping!
                                </div>
                            @endif
                        </div>

                        <a href="{{ route('checkout.index') }}" 
                           onclick="trackInitiateCheckout(event)"
                           class="block w-full bg-[#005366] hover:bg-[#003d4d] text-white font-bold py-4 rounded-xl transition-colors duration-200 mb-3 flex items-center justify-center space-x-2">
                            <span>Proceed to Checkout</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>

                        <a href="{{ route('products.index') }}" class="block w-full text-center text-[#005366] hover:text-[#003d4d] font-medium py-3 border-2 border-[#005366] rounded-xl hover:bg-[#005366] hover:text-white transition-all duration-200">
                            Continue Shopping
                        </a>

                        <!-- Trust Badges -->
                        <div class="mt-6 pt-6 border-t space-y-3">
                            <div class="flex items-center space-x-3 text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Secure Checkout</span>
                            </div>
                            <div class="flex items-center space-x-3 text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Free Returns within 30 days</span>
                            </div>
                            <div class="flex items-center space-x-3 text-sm text-gray-600">
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Bluprinter Guarantee</span>
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
                    <p class="text-gray-600">$${parseFloat(cartItem.price).toFixed(2)} each</p>
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
                    <span class="text-2xl font-bold text-[#005366]" id="modalTotal${cartItem.id}">$${(parseFloat(cartItem.price) * cartItem.quantity).toFixed(2)}</span>
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
    if (totalDisplay) totalDisplay.textContent = '$' + total.toFixed(2);
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
                    <p class="text-base font-bold text-[#005366]">$${parseFloat(product.price).toFixed(2)}</p>
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
    // Let the link navigate normally
    return true;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadRecentlyViewed();
    
    // Shipping calculation with freeship logic
    const shippingCountrySelect = document.getElementById('shippingCountry');
    const shippingCostElement = document.getElementById('shippingCost');
    const totalElement = document.querySelector('.text-\\[\\#005366\\]');
    const subtotal = {{ $subtotal }};
    
    // Function to apply freeship logic
    function applyFreeshipLogic(currentSubtotal, shippingCost) {
        const qualifiesForFreeShipping = currentSubtotal >= 100;
        return qualifiesForFreeShipping ? 0 : shippingCost;
    }
    
    // Function to update shipping display with freeship messages
    function updateShippingDisplay(actualShipping, originalShipping, qualifiesForFreeShipping) {
        if (shippingCostElement) {
            if (qualifiesForFreeShipping) {
                shippingCostElement.innerHTML = '<span class="text-green-600">FREE</span>';
            } else {
                shippingCostElement.innerHTML = actualShipping === 0 ? 
                    '<span class="text-green-600">FREE</span>' : 
                    '$' + actualShipping.toFixed(2);
            }
        }
        
        // Add or update freeship messages
        const shippingSection = document.querySelector('.border-t.pt-3');
        if (shippingSection) {
            // Remove existing freeship messages
            const existingFreeshipMsg = shippingSection.querySelector('.freeship-message');
            const existingProgressMsg = shippingSection.querySelector('.freeship-progress');
            
            if (existingFreeshipMsg) existingFreeshipMsg.remove();
            if (existingProgressMsg) existingProgressMsg.remove();
            
            if (qualifiesForFreeShipping) {
                // Add success message
                const successMsg = document.createElement('div');
                successMsg.className = 'freeship-message text-xs text-green-600 bg-green-50 p-2 rounded mt-2';
                successMsg.innerHTML = '🎉 You qualify for free shipping on orders $100+!';
                shippingSection.insertBefore(successMsg, shippingSection.querySelector('.border-t.pt-3'));
            } else {
                // Add progress message
                const remainingAmount = (100 - subtotal).toFixed(2);
                const progressMsg = document.createElement('div');
                progressMsg.className = 'freeship-progress text-xs text-blue-600 mt-2';
                progressMsg.textContent = `Add $${remainingAmount} more for free shipping!`;
                shippingSection.insertBefore(progressMsg, shippingSection.querySelector('.border-t.pt-3'));
            }
        }
    }
    
    if (shippingCountrySelect) {
        shippingCountrySelect.addEventListener('change', async function() {
            const country = this.value;
            
            try {
                const response = await fetch('/checkout/calculate-shipping', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ country: country })
                });
                
                const data = await response.json();
                
                if (data.success && data.shipping) {
                    const newShipping = parseFloat(data.shipping.total_shipping);
                    const qualifiesForFreeShipping = subtotal >= 100;
                    const actualShipping = applyFreeshipLogic(subtotal, newShipping);
                    const newTotal = subtotal + actualShipping;
                    
                    console.log('Cart shipping update:', {
                        subtotal: subtotal,
                        originalShipping: newShipping,
                        actualShipping: actualShipping,
                        qualifiesForFreeShipping: qualifiesForFreeShipping
                    });
                    
                    // Update shipping cost display with freeship logic
                    updateShippingDisplay(actualShipping, newShipping, qualifiesForFreeShipping);
                    
                    // Update zone
                    const zoneElement = document.querySelector('.text-xs.text-gray-500 span:last-child');
                    if (zoneElement) {
                        zoneElement.textContent = data.shipping.zone_name || country;
                    }
                    
                    // Update total
                    if (totalElement) {
                        totalElement.textContent = '$' + newTotal.toFixed(2);
                    }
                }
            } catch (error) {
                console.error('Shipping calculation error:', error);
            }
        });
    }
    
    // Initialize freeship check on page load
    function initializeFreeshipCheck() {
        const qualifiesForFreeShipping = subtotal >= 100;
        const currentShippingElement = document.getElementById('shippingCost');
        const currentShippingText = currentShippingElement ? currentShippingElement.textContent : '';
        const currentShipping = currentShippingText.includes('FREE') ? 0 : parseFloat(currentShippingText.replace(/\$/g, '')) || 0;
        
        if (qualifiesForFreeShipping) {
            updateShippingDisplay(0, currentShipping, true);
            // Update total if needed
            if (totalElement) {
                const newTotal = subtotal;
                totalElement.textContent = '$' + newTotal.toFixed(2);
            }
        }
        
        console.log('Cart freeship initialization:', {
            subtotal: subtotal,
            qualifiesForFreeShipping: qualifiesForFreeShipping,
            currentShipping: currentShipping
        });
    }
    
    // Run initialization
    initializeFreeshipCheck();
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

/* Specific targeting for shipping selects */
#shippingCountry {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: none !important;
}
</style>

@endsection

