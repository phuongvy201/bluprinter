@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
@php
    $currentCurrency = currency();
    $currencySymbol = currency_symbol();
@endphp
<style>
    .wishlist-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
    }

    .wishlist-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
    }

    .remove-btn {
        transition: all 0.3s ease;
    }

    .remove-btn:hover {
        transform: scale(1.1);
    }

    .add-to-cart-btn {
        transition: all 0.3s ease;
    }

    .add-to-cart-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Toast notification styles */
    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .toast.show {
        transform: translateX(0);
    }

    .toast.success {
        background-color: #10b981;
        color: white;
    }

    .toast.error {
        background-color: #ef4444;
        color: white;
    }

    .toast.warning {
        background-color: #f59e0b;
        color: white;
    }

    .toast-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
    }
</style>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Wishlist</h1>
                    <p class="text-gray-600 mt-2">Your favorite products saved for later</p>
                </div>
                <div class="flex space-x-3">
                    <button id="clear-wishlist-btn" 
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Clear All
                    </button>
                </div>
            </div>
        </div>

        <!-- Clear Wishlist Modal -->
        <div id="clear-wishlist-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Clear All Wishlist</h3>
                        <p class="text-sm text-gray-600">This action cannot be undone</p>
                    </div>
                </div>
                <p class="text-gray-700 mb-6">Are you sure you want to remove all products from your wishlist?</p>
                <div class="flex space-x-3">
                    <button id="confirm-clear-wishlist" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition-colors">
                        Clear All
                    </button>
                    <button id="cancel-clear-wishlist" 
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Remove Item Modal -->
        <div id="remove-item-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Remove Product</h3>
                        <p class="text-sm text-gray-600">Product will be removed from wishlist</p>
                    </div>
                </div>
                <p class="text-gray-700 mb-6">Are you sure you want to remove this product from your wishlist?</p>
                <div class="flex space-x-3">
                    <button id="confirm-remove-item" 
                            class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-2 px-4 rounded-lg transition-colors">
                        Remove
                    </button>
                    <button id="cancel-remove-item" 
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-4 rounded-lg transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        @if($wishlistItems->count() > 0)
            <!-- Wishlist Items -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($wishlistItems as $wishlistItem)
                    <div class="wishlist-card bg-white p-4">
                        <div class="relative">
                            <!-- Product Image -->
                            <div class="mb-4">
                                @php
                                    $media = $wishlistItem->product ? $wishlistItem->product->getEffectiveMedia() : [];
                                    $imageUrl = null;
                                    if ($media && count($media) > 0) {
                                        if (is_string($media[0])) {
                                            $imageUrl = $media[0];
                                        } elseif (is_array($media[0])) {
                                            $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? null;
                                        }
                                    }
                                @endphp
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $wishlistItem->product->name }}" 
                                         class="product-image">
                                @else
                                    <div class="product-image bg-gray-200 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Remove Button -->
                            <button class="remove-btn absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-lg"
                                    onclick="removeFromWishlist({{ $wishlistItem->product_id }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Product Info -->
                        <div class="space-y-2">
                            <h3 class="font-semibold text-gray-900 line-clamp-2">
                                {{ $wishlistItem->product->name ?? 'Product not found' }}
                            </h3>
                            
                            @if($wishlistItem->product)
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-blue-600">
                                        {{ format_price_usd((float) $wishlistItem->product->base_price) }}
                                    </span>
                                    @if($wishlistItem->product->shop)
                                        <span class="text-sm text-gray-500">
                                            by {{ $wishlistItem->product->shop->name }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex space-x-2 pt-2">
                                    <a href="{{ route('products.show', $wishlistItem->product->slug) }}" 
                                       class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg transition-colors">
                                        View Details
                                    </a>
                                    <button class="add-to-cart-btn bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-colors"
                                            onclick="addToCart({{ $wishlistItem->product_id }})">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-gray-500">Product no longer available</p>
                                    <button onclick="removeFromWishlist({{ $wishlistItem->product_id }})" 
                                            class="mt-2 text-red-500 hover:text-red-700 text-sm">
                                        Remove from wishlist
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $wishlistItems->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Your wishlist is empty</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">Start adding products to your wishlist by clicking the heart icon on any product!</p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('products.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Browse Products
                    </a>
                    <a href="{{ route('collections.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        View Collections
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
// Toast notification function
function showToast(message, type = 'success') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    // Set icon based on type
    let iconSvg = '';
    switch(type) {
        case 'success':
            iconSvg = '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            break;
        case 'error':
            iconSvg = '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            break;
        case 'warning':
            iconSvg = '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
            break;
    }
    
    toast.innerHTML = `
        ${iconSvg}
        <span>${message}</span>
    `;
    
    // Add to body
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Remove item modal functionality
let currentProductId = null;
const removeItemModal = document.getElementById('remove-item-modal');
const confirmRemoveBtn = document.getElementById('confirm-remove-item');
const cancelRemoveBtn = document.getElementById('cancel-remove-item');

// Remove from wishlist
function removeFromWishlist(productId) {
    currentProductId = productId;
    removeItemModal.classList.remove('hidden');
}

// Hide remove item modal when cancel button is clicked
cancelRemoveBtn.addEventListener('click', function() {
    removeItemModal.classList.add('hidden');
    currentProductId = null;
});

// Hide remove item modal when clicking outside
removeItemModal.addEventListener('click', function(e) {
    if (e.target === removeItemModal) {
        removeItemModal.classList.add('hidden');
        currentProductId = null;
    }
});

// Confirm remove item
confirmRemoveBtn.addEventListener('click', function() {
    if (currentProductId) {
        // Show loading state
        confirmRemoveBtn.disabled = true;
        confirmRemoveBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Removing...';
        
        fetch('{{ route("wishlist.remove") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                product_id: currentProductId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide modal and reload page
                removeItemModal.classList.add('hidden');
                location.reload();
            } else {
                // Reset button state and show error
                confirmRemoveBtn.disabled = false;
                confirmRemoveBtn.innerHTML = 'Remove';
                showToast(data.message || 'Failed to remove product from wishlist.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Reset button state and show error
            confirmRemoveBtn.disabled = false;
            confirmRemoveBtn.innerHTML = 'Remove';
            showToast('An error occurred while removing the product from wishlist.', 'error');
        });
    }
});

// Add to cart
function addToCart(productId) {
    fetch('{{ route("api.cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            id: productId,
            quantity: 1,
            price: 0 // Will be set by the controller
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Track Facebook Pixel AddToCart event
            if (typeof fbq !== 'undefined') {
                fbq('track', 'AddToCart', {
                    content_ids: [productId],
                    content_type: 'product'
                });
            }
            
            showToast('Product added to cart successfully!', 'success');
            // Update cart count if needed
            updateCartCount();
        } else {
            showToast(data.message || 'Failed to add product to cart.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while adding the product to cart.', 'error');
    });
}

// Clear wishlist modal functionality
const clearWishlistBtn = document.getElementById('clear-wishlist-btn');
const clearWishlistModal = document.getElementById('clear-wishlist-modal');
const confirmClearBtn = document.getElementById('confirm-clear-wishlist');
const cancelClearBtn = document.getElementById('cancel-clear-wishlist');

// Show modal when clear button is clicked
clearWishlistBtn.addEventListener('click', function() {
    clearWishlistModal.classList.remove('hidden');
});

// Hide modal when cancel button is clicked
cancelClearBtn.addEventListener('click', function() {
    clearWishlistModal.classList.add('hidden');
});

// Hide modal when clicking outside
clearWishlistModal.addEventListener('click', function(e) {
    if (e.target === clearWishlistModal) {
        clearWishlistModal.classList.add('hidden');
    }
});

// Confirm clear wishlist
confirmClearBtn.addEventListener('click', function() {
    // Show loading state
    confirmClearBtn.disabled = true;
    confirmClearBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Clearing...';
    
    fetch('{{ route("wishlist.clear") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal and reload page
            clearWishlistModal.classList.add('hidden');
            location.reload();
        } else {
            // Reset button state and show error
            confirmClearBtn.disabled = false;
            confirmClearBtn.innerHTML = 'Clear All';
            showToast(data.message || 'Failed to clear wishlist.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Reset button state and show error
        confirmClearBtn.disabled = false;
        confirmClearBtn.innerHTML = 'Clear All';
        showToast('An error occurred while clearing the wishlist.', 'error');
    });Z
});

// Update cart count (if cart count element exists)
function updateCartCount() {
    // Trigger cart update event for header
    window.dispatchEvent(new CustomEvent('cartUpdated'));
}
</script>
@endsection
