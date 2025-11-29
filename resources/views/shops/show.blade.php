@extends('layouts.app')

@section('title', $shop->shop_name . ' - Shop Profile')

@section('content')
@php
    $currentCurrency = currency();
    $currencySymbol = currency_symbol();
@endphp
<script>
// Track Facebook Pixel ViewContent for shop page
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: '{{ addslashes($shop->name) }}',
            content_type: 'product_group'
        });
    }
});
</script>
<!-- Shop Profile Banner -->
<div class="bg-gradient-to-r from-red-50 to-red-100 border-b border-red-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            <!-- Left side - Shop Info -->
            <div class="flex-1 text-center lg:text-left mb-8 lg:mb-0">
                <div class="flex items-center justify-center lg:justify-start mb-4">
                     <img src="{{ asset('images/logo nhỏ.png') }}" alt="Bluprinter" class="h-12 w-auto mr-4">
                    <div>
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">Bluprinter</h1>
                        <p class="text-lg font-medium" style="color: #065264;">Print Your Imagination</p>
                    </div>
                </div>
            </div>
            
            <!-- Right side - Decorative Elements -->
            <div class="flex-1 flex justify-center lg:justify-end">
                <div class="relative">
                    <!-- Shopping bags -->
                    <div class="flex space-x-4">
                        <div class="w-16 h-20 rounded-lg transform rotate-12 shadow-lg" style="background-color: #D8140B;"></div>
                        <div class="w-16 h-20 rounded-lg transform -rotate-12 shadow-lg" style="background-color: #065264;"></div>
                        <div class="w-16 h-20 bg-red-400 rounded-lg transform rotate-6 shadow-lg"></div>
                    </div>
                    
                    <!-- Bee and flowers -->
                    <div class="absolute -top-4 -right-4 w-8 h-8 bg-yellow-300 rounded-full flex items-center justify-center">
                        <span class="text-yellow-800 text-sm">🐝</span>
                    </div>
                    <div class="absolute top-8 -left-2 w-6 h-6 bg-pink-300 rounded-full"></div>
                    <div class="absolute bottom-2 -right-2 w-4 h-4 bg-purple-300 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Shop Profile Information -->
<div class="bg-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <!-- Shop Avatar -->
            <div class="relative inline-block mb-6">
                <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden mx-auto">
                    @if($shop->shop_logo)
                        <img src="{{ $shop->shop_logo }}" alt="{{ $shop->shop_name }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    @endif
                </div>
                @if($shop->verified)
                    <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                @endif
            </div>
            
            <!-- Shop Name -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $shop->shop_name }}</h2>
            
            <!-- Shop Stats -->
            <div class="flex items-center justify-center space-x-8 mb-6">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['followers']) }} Followers</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($stats['favorited']) }} Favorited</span>
                </div>
            </div>
            
             <!-- Action Buttons -->
             <div class="flex items-center justify-center space-x-4 mb-8">
                 <button id="followBtn" 
                         onclick="toggleFollow()"
                         class="flex items-center space-x-2 px-6 py-3 rounded-lg font-semibold transition-all duration-200 {{ $isFollowing ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' : 'text-white hover:opacity-90' }}"
                         style="{{ !$isFollowing ? 'background-color: #D8140B;' : '' }}">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                     </svg>
                     <span id="followText">{{ $isFollowing ? 'Unfollow' : 'Follow' }}</span>
                 </button>
                 
                 <button onclick="openContactModal()"
                         class="flex items-center space-x-2 px-6 py-3 bg-white border-2 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200"
                         style="border-color: #D8140B; color: #D8140B;">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                     </svg>
                     <span>Contact</span>
                 </button>
             </div>
             
        </div>
    </div>
</div>

<!-- Shop All Items / Product Categories -->
<div id="productsContent" class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-8">Shop all items</h3>
        
        <!-- Product Categories Carousel -->
        @if($categories->count() > 0)
        <div class="relative mb-12">
            <!-- Navigation arrows -->
            <button onclick="scrollCategories('left')" class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 w-10 h-10 rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 nav-arrow">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <button onclick="scrollCategories('right')" class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 w-10 h-10 rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 nav-arrow">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            
            <div class="group">
                <div id="categoriesContainer" class="flex space-x-4 overflow-x-auto pb-4 scrollbar-hide scroll-smooth">
                    @foreach($categories as $category)
                    <div class="flex-shrink-0 text-center group cursor-pointer" onclick="filterByCategory('{{ $category->id }}')">
                        <!-- Category Card -->
                        <div class="relative w-32 h-32 bg-white rounded-2xl shadow-lg overflow-hidden category-card">
                            <!-- Gradient overlay -->
                            <div class="absolute inset-0 gradient-overlay opacity-0 group-hover:opacity-100"></div>
                            
                            <!-- Category Image Container -->
                            <div class="relative w-full h-full flex items-center justify-center p-4">
                                @php
                                    $firstProduct = null;
                                    if ($category->templates->isNotEmpty()) {
                                        foreach ($category->templates as $template) {
                                            if ($template->products->isNotEmpty()) {
                                                $firstProduct = $template->products->first();
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                
                                @if($firstProduct && count($firstProduct->getEffectiveMedia()) > 0)
                                     @php
                                         $media = $firstProduct->getEffectiveMedia();
                                         $imageUrl = is_array($media) && isset($media[0]) ? $media[0] : (is_string($media) ? $media : '');
                                     @endphp
                                     @if($imageUrl)
                                     <img src="{{ $imageUrl }}" 
                                          alt="{{ $category->name }}" 
                                          class="w-16 h-16 object-cover rounded-xl shadow-md">
                                     @else
                                     <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center">
                                         <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                         </svg>
                                     </div>
                                     @endif
                                 @else
                                     <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center">
                                         <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                         </svg>
                                     </div>
                                 @endif
                                 
                                 <!-- Product count badge -->
                                 <div class="absolute top-2 right-2 bg-white rounded-full px-2 py-1 shadow-sm">
                                     <span class="text-xs font-semibold" style="color: #D8140B;">{{ $category->templates->count() }}</span>
                                 </div>
                            </div>
                            
                            <!-- Hover effect overlay -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300 rounded-2xl"></div>
                        </div>
                        
                        <!-- Category Name -->
                        <div class="mt-3 px-2">
                            <span class="text-sm font-semibold text-gray-700 group-hover:text-red-600 transition-colors duration-200 block category-name">
                                {{ $category->name }}
                            </span>
                            <span class="text-xs text-gray-500 group-hover:text-gray-600 transition-colors duration-200">
                                {{ $category->templates->count() }} items
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        
        <!-- All Products Grid -->
        @if($allProducts->count() > 0)
        <div class="flex items-center mb-8">
            <div class="w-1 h-8 mr-4" style="background-color: #D8140B;"></div>
            <h4 class="text-xl font-bold text-gray-900">All Products</h4>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">
            @foreach($allProducts as $product)
            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 group overflow-hidden">
                <!-- Product Image -->
                <div class="relative aspect-square overflow-hidden">
                    @php
                        $media = $product->getEffectiveMedia();
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
                             alt="{{ $product->name }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Wishlist Button -->
                    <button class="absolute top-2 left-2 sm:top-3 sm:left-3 p-1.5 sm:p-2 bg-white rounded-full shadow-md hover:shadow-lg transition-all duration-200 opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600 hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </button>

                    <!-- Discount Badge -->
                    @if($product->template && $product->template->base_price > $product->price)
                        @php
                            $discount = round((($product->template->base_price - $product->price) / $product->template->base_price) * 100);
                        @endphp
                        <div class="absolute top-2 right-2 sm:top-3 sm:right-3 text-white px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold" style="background-color: #D8140B;">
                            {{ $discount }}% off
                        </div>
                    @endif
                </div>

                <!-- Product Info -->
                <div class="p-2 sm:p-4">
                    <h3 class="font-semibold text-gray-900 mb-1 sm:mb-2 line-clamp-2 transition-colors text-sm sm:text-base" style="group-hover:color: #005366;">
                        <a href="{{ route('products.show', $product->slug) }}">
                            {{ Str::limit($product->name, 50) }}
                        </a>
                    </h3>
                    
                    <p class="text-xs sm:text-sm text-gray-600 mb-1 sm:mb-2 line-clamp-1">By {{ $product->shop->name ?? 'Unknown Shop' }}</p>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2">
                            @if($product->template && $product->template->base_price > $product->price)
                                <span class="text-xs sm:text-sm text-gray-500 line-through">{{ format_price_usd((float) $product->template->base_price) }}</span>
                                <span class="text-base sm:text-lg font-bold" style="color: #D8140B;">{{ format_price_usd((float) $product->price) }}</span>
                            @else
                                <span class="text-base sm:text-lg font-bold" style="color: #D8140B;">{{ format_price_usd((float) $product->base_price) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Sale End Date -->
                    @if($product->template && $product->template->base_price > $product->price)
                        <div class="mt-1 sm:mt-2 text-[10px] sm:text-xs font-medium" style="color: #D8140B;">
                            Sale ends at {{ now()->addDays(7)->format('F d') }}
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-8">
            {{ $allProducts->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <p class="text-gray-500 text-lg">This shop has no products yet</p>
        </div>
        @endif
    </div>
</div>

<!-- Contact Modal -->
<div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Contact Shop</h3>
                <button onclick="closeContactModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="contactForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                    <input type="text" id="subject" name="subject" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea id="message" name="message" rows="4" required 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeContactModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                     <button type="submit" 
                             class="flex-1 px-4 py-2 text-white rounded-md hover:opacity-90"
                             style="background-color: #D8140B;">
                         Send Message
                     </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Category card animations */
@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(216, 20, 11, 0.4);
    }
    50% {
        box-shadow: 0 0 0 10px rgba(216, 20, 11, 0);
    }
}

.category-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.category-card:hover {
    transform: translateY(-2px) scale(1.02);
    animation: pulse-glow 2s infinite;
}

/* Smooth gradient transitions */
.gradient-overlay {
    background: linear-gradient(135deg, rgba(216, 20, 11, 0.1) 0%, rgba(6, 82, 100, 0.1) 100%);
    transition: opacity 0.3s ease;
}

/* Custom scrollbar for categories */
#categoriesContainer::-webkit-scrollbar {
    height: 4px;
}

#categoriesContainer::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#categoriesContainer::-webkit-scrollbar-thumb {
    background: #D8140B;
    border-radius: 10px;
}

#categoriesContainer::-webkit-scrollbar-thumb:hover {
    background: #b0110a;
}

/* Navigation arrows styling */
.nav-arrow {
    backdrop-filter: blur(10px);
    background: rgba(216, 20, 11, 0.9);
    transition: all 0.3s ease;
}

.nav-arrow:hover {
    background: rgba(216, 20, 11, 1);
    transform: scale(1.1);
}

/* Category name styling */
.category-name {
    background: linear-gradient(135deg, #D8140B, #065264);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 600;
}

/* Responsive improvements */
@media (max-width: 640px) {
    .category-card {
        width: 120px;
        height: 120px;
    }
    
    .nav-arrow {
        width: 8px;
        height: 8px;
    }
}
</style>

<script>
// Follow/Unfollow functionality
function toggleFollow() {
    @guest
        window.location.href = '{{ route("login") }}';
        return;
    @endguest
    
    const followBtn = document.getElementById('followBtn');
    const followText = document.getElementById('followText');
    const isCurrentlyFollowing = followBtn.classList.contains('bg-gray-200');
    
    const action = isCurrentlyFollowing ? 'unfollow' : 'follow';
    
    fetch('{{ route("shops.follow", $shop) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ action: action })
    })
    .then(response => response.json())
    .then(data => {
         if (data.success) {
             if (action === 'follow') {
                 followBtn.classList.remove('bg-gray-200', 'text-gray-700');
                 followBtn.classList.add('text-white');
                 followBtn.style.backgroundColor = '#D8140B';
                 followText.textContent = 'Unfollow';
             } else {
                 followBtn.classList.remove('text-white');
                 followBtn.classList.add('bg-gray-200', 'text-gray-700');
                 followBtn.style.backgroundColor = '';
                 followText.textContent = 'Follow';
             }
            
            // Update followers count
            const followersElement = document.querySelector('[data-followers]');
            if (followersElement) {
                followersElement.textContent = data.followers_count + ' Followers';
            }
            
            // Show success message
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

// Contact modal
function openContactModal() {
    document.getElementById('contactModal').classList.remove('hidden');
}

function closeContactModal() {
    document.getElementById('contactModal').classList.add('hidden');
}

// Contact form submission
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        subject: formData.get('subject'),
        message: formData.get('message')
    };
    
    fetch('{{ route("shops.contact", $shop) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeContactModal();
            this.reset();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
});

// Category scrolling functionality
function scrollCategories(direction) {
    const container = document.getElementById('categoriesContainer');
    const scrollAmount = 200;
    
    if (direction === 'left') {
        container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}

// Category filtering functionality
function filterByCategory(categoryId) {
    // Add loading state
    const productsGrid = document.querySelector('.grid');
    if (productsGrid) {
        productsGrid.style.opacity = '0.5';
        productsGrid.style.pointerEvents = 'none';
    }
    
    // Show loading spinner
    showNotification('Đang tải sản phẩm...', 'info');
    
    // Here you can add AJAX call to filter products by category
    // For now, we'll just scroll to products section
    document.getElementById('productsContent').scrollIntoView({ 
        behavior: 'smooth',
        block: 'start'
    });
    
    // Reset loading state
    setTimeout(() => {
        if (productsGrid) {
            productsGrid.style.opacity = '1';
            productsGrid.style.pointerEvents = 'auto';
        }
    }, 1000);
}

// Show/hide navigation arrows based on scroll position
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('categoriesContainer');
    if (container) {
        container.addEventListener('scroll', function() {
            const leftArrow = document.querySelector('button[onclick="scrollCategories(\'left\')"]');
            const rightArrow = document.querySelector('button[onclick="scrollCategories(\'right\')"]');
            
            if (leftArrow && rightArrow) {
                // Show/hide arrows based on scroll position
                leftArrow.style.opacity = this.scrollLeft > 0 ? '1' : '0';
                rightArrow.style.opacity = this.scrollLeft < (this.scrollWidth - this.clientWidth) ? '1' : '0';
            }
        });
    }
});

// Notification function
function showNotification(message, type) {
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 
                   type === 'error' ? 'bg-red-500' : 
                   type === 'info' ? 'bg-blue-500' : 'bg-gray-500';
    
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 text-white ${bgColor}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endsection
