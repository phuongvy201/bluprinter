@props(['product', 'size' => 'md', 'showText' => false])

@php
    $sizeClasses = [
        'sm' => 'w-4 h-4',
        'md' => 'w-5 h-5',
        'lg' => 'w-6 h-6'
    ];
    
    $buttonClasses = [
        'sm' => 'p-1.5',
        'md' => 'p-2',
        'lg' => 'p-3'
    ];
@endphp

<button 
    data-wishlist-toggle 
    data-product-id="{{ $product->id }}"
    data-product-name="{{ $product->name }}"
    data-product-price="{{ $product->base_price }}"
    data-product-slug="{{ $product->slug }}"
    class="wishlist-btn size-{{ $size }} {{ $buttonClasses[$size] }} {{ $showText ? 'w-full border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 px-6 rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2' : 'rounded-full bg-white shadow-md hover:shadow-lg transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1' }}"
    title="Add to wishlist"
>
    <svg class="{{ $sizeClasses[$size] }} text-gray-400 hover:text-red-500 transition-colors" 
         fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
    </svg>
    
    @if($showText)
        <span class="ml-2 text-sm font-medium">Add to Wishlist</span>
    @endif
</button>

<style>
.wishlist-btn.in-wishlist svg {
    fill: currentColor;
    color: #ef4444;
}

.wishlist-btn.not-in-wishlist svg {
    fill: none;
    color: #9ca3af;
}

.wishlist-btn:hover svg {
    color: #ef4444;
}

/* Size specific styles */
.wishlist-btn.size-sm {
    min-width: 32px;
    min-height: 32px;
}

.wishlist-btn.size-md {
    min-width: 40px;
    min-height: 40px;
}

.wishlist-btn.size-lg {
    min-width: 48px;
    min-height: 48px;
}

/* Full width button styles */
.wishlist-btn.w-full {
    min-width: auto;
    min-height: auto;
}

.wishlist-btn.w-full.in-wishlist {
    background-color: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

.wishlist-btn.w-full.not-in-wishlist {
    background-color: #ffffff;
    border-color: #d1d5db;
    color: #374151;
}

.wishlist-btn.w-full:hover {
    background-color: #f9fafb;
    border-color: #9ca3af;
}
</style>
