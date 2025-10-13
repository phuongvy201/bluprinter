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
                                    <img src="{{ $item->product->media[0] ?? '/images/placeholder.jpg' }}" 
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
                                        <button onclick="removeFromCart({{ $item->id }})" class="ml-4 p-2 text-gray-400 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
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
                                            <p class="text-2xl font-bold text-[#005366]">${{ number_format($item->getTotalPrice(), 2) }}</p>
                                            @if($item->quantity > 1)
                                                <p class="text-sm text-gray-500">${{ number_format($item->price, 2) }} each</p>
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
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping</span>
                                @if($shipping == 0)
                                    <span class="text-green-600 font-semibold">FREE</span>
                                @else
                                    <span class="font-semibold">${{ number_format($shipping, 2) }}</span>
                                @endif
                            </div>
                            @if($subtotal < 50 && $subtotal > 0)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <p class="text-xs text-blue-800">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Add ${{ number_format(50 - $subtotal, 2) }} more for FREE shipping!
                                    </p>
                                </div>
                            @endif
                            <div class="border-t pt-3 flex justify-between text-lg font-bold text-gray-900">
                                <span>Total</span>
                                <span class="text-[#005366]">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <button class="w-full bg-[#005366] hover:bg-[#003d4d] text-white font-bold py-4 rounded-xl transition-colors duration-200 mb-3 flex items-center justify-center space-x-2">
                            <span>Proceed to Checkout</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </button>

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
    </div>
</div>

<!-- JavaScript for Cart Operations -->
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
</script>
@endsection

