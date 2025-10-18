@extends('layouts.app')

@section('title', 'Search Results' . ($query ? ' for "' . $query . '"' : ''))

@section('content')
<script>
// Track Facebook Pixel Search event
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        @if($query)
        fbq('track', 'Search', {
            search_string: '{{ addslashes($query) }}',
            content_category: 'product'
        });
        @endif
        
        fbq('track', 'ViewContent', {
            content_name: 'Search Results',
            content_type: 'search'
        });
    }
});
</script>
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        @if($query)
                            Search Results for "{{ $query }}"
                        @else
                            Search
                        @endif
                    </h1>
                    @if($query && $totalResults > 0)
                        <p class="text-gray-600 mt-2">
                            Found {{ number_format($totalResults) }} result{{ $totalResults !== 1 ? 's' : '' }}
                        </p>
                    @endif
                </div>
                
                <!-- Search Form -->
                <div class="w-full max-w-md">
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <div class="relative">
                            <input type="text" name="q" placeholder="Search products, collections, shops..."
                                   value="{{ $query }}"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#005366] focus:border-transparent transition-all duration-200 bg-white">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <div class="bg-[#005366] text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Filter Tabs -->
            @if($query)
                <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg w-fit">
                    <a href="{{ route('search', ['q' => $query]) }}" 
                       class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ !$type ? 'bg-white text-[#005366] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        All ({{ $totalResults }})
                    </a>
                    <a href="{{ route('search', ['q' => $query, 'type' => 'products']) }}" 
                       class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $type === 'products' ? 'bg-white text-[#005366] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Products ({{ $counts['products'] ?? 0 }})
                    </a>
                    <a href="{{ route('search', ['q' => $query, 'type' => 'collections']) }}" 
                       class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $type === 'collections' ? 'bg-white text-[#005366] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Collections ({{ $counts['collections'] ?? 0 }})
                    </a>
                    <a href="{{ route('search', ['q' => $query, 'type' => 'shops']) }}" 
                       class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $type === 'shops' ? 'bg-white text-[#005366] shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        Shops ({{ $counts['shops'] ?? 0 }})
                    </a>
                </div>
            @endif
        </div>

        @if($query)
            @if($totalResults > 0)
                <!-- Search Results -->
                <div class="space-y-8">
                    <!-- Products Results -->
                    @if(($type === 'all' || $type === 'products') && $products->count() > 0)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Products
                            </h2>
                            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                                @foreach($products as $product)
                                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden group">
                                        <div class="aspect-square bg-gray-100 overflow-hidden">
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
                                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#005366] to-[#003d4d]">
                                                    <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="p-4">
                                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-[#005366] transition-colors">
                                                <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
                                            </h3>
                                            @if($product->shop)
                                                <p class="text-sm text-gray-500 mb-2">by {{ $product->shop->name }}</p>
                                            @endif
                                            <div class="flex items-center justify-between">
                                                <span class="text-lg font-bold text-[#005366]">${{ number_format($product->base_price, 2) }}</span>
                                                <x-wishlist-button :product="$product" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($counts['products'] > $products->count())
                                <div class="text-center mt-6">
                                    <a href="{{ route('search', ['q' => $query, 'type' => 'products']) }}" 
                                       class="inline-flex items-center px-6 py-3 bg-[#005366] text-white font-semibold rounded-lg hover:bg-[#003d4d] transition-colors">
                                        View All Products ({{ $counts['products'] }})
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Collections Results -->
                    @if(($type === 'all' || $type === 'collections') && $collections->count() > 0)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                Collections
                            </h2>
                            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                                @foreach($collections as $collection)
                                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden group">
                                        <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
                                            @if($collection->image)
                                                <img src="{{ $collection->image }}" 
                                                     alt="{{ $collection->name }}" 
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#E2150C] to-[#c0120a]">
                                                    <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="p-4">
                                            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-[#005366] transition-colors">
                                                <a href="{{ route('collections.show', $collection->slug) }}">{{ $collection->name }}</a>
                                            </h3>
                                            @if($collection->shop)
                                                <p class="text-sm text-gray-500 mb-2">by {{ $collection->shop->name }}</p>
                                            @endif
                                            <p class="text-sm text-gray-600 line-clamp-2">{{ $collection->description }}</p>
                                            <div class="mt-3 flex items-center justify-between">
                                                <span class="text-sm text-gray-500">{{ $collection->active_products_count }} products</span>
                                                <span class="text-xs bg-[#E2150C] text-white px-2 py-1 rounded-full">Collection</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($counts['collections'] > $collections->count())
                                <div class="text-center mt-6">
                                    <a href="{{ route('search', ['q' => $query, 'type' => 'collections']) }}" 
                                       class="inline-flex items-center px-6 py-3 bg-[#E2150C] text-white font-semibold rounded-lg hover:bg-[#c0120a] transition-colors">
                                        View All Collections ({{ $counts['collections'] }})
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Shops Results -->
                    @if(($type === 'all' || $type === 'shops') && $shops->count() > 0)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Shops
                            </h2>
                            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                                @foreach($shops as $shop)
                                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden group">
                                        <div class="p-6">
                                            <div class="flex items-center mb-4">
                                                <div class="w-16 h-16 bg-gradient-to-br from-[#005366] to-[#003d4d] rounded-full flex items-center justify-center text-white font-bold text-xl mr-4">
                                                    {{ strtoupper(substr($shop->shop_name, 0, 1)) }}
                                                </div>
                                                <div class="flex-1">
                                                    <h3 class="font-semibold text-gray-900 group-hover:text-[#005366] transition-colors">
                                                        <a href="{{ route('shops.show', $shop->shop_slug ?? $shop->id) }}">{{ $shop->shop_name }}</a>
                                                    </h3>
                                                    <p class="text-sm text-gray-500">{{ $shop->shop_description ? Str::limit($shop->shop_description, 50) : 'No description available' }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-500">{{ $shop->products_count ?? 0 }} products</span>
                                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Shop</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($counts['shops'] > $shops->count())
                                <div class="text-center mt-6">
                                    <a href="{{ route('search', ['q' => $query, 'type' => 'shops']) }}" 
                                       class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">
                                        View All Shops ({{ $counts['shops'] }})
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @else
                <!-- No Results -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">No results found</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        We couldn't find any results for "{{ $query }}". Try adjusting your search terms or browse our categories.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <a href="{{ route('products.index') }}" class="bg-[#005366] hover:bg-[#003d4d] text-white px-6 py-3 rounded-lg transition-colors">
                            Browse Products
                        </a>
                        <a href="{{ route('collections.index') }}" class="bg-[#E2150C] hover:bg-[#c0120a] text-white px-6 py-3 rounded-lg transition-colors">
                            View Collections
                        </a>
                    </div>
                </div>
            @endif
        @else
            <!-- Empty Search State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 bg-gradient-to-br from-[#005366] to-[#003d4d] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Search Products & Collections</h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    Enter keywords to search for products, collections, or shops. Use the search bar above to get started.
                </p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('products.index') }}" class="bg-[#005366] hover:bg-[#003d4d] text-white px-6 py-3 rounded-lg transition-colors">
                        Browse Products
                    </a>
                    <a href="{{ route('collections.index') }}" class="bg-[#E2150C] hover:bg-[#c0120a] text-white px-6 py-3 rounded-lg transition-colors">
                        View Collections
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
