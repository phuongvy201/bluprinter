@extends('layouts.app')

@section('title', 'All Products')

@section('content')
<!-- Header Section -->
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                @foreach($breadcrumbs as $index => $breadcrumb)
                    @if($index > 0)
                        <li>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </li>
                    @endif
                    <li>
                        @if($breadcrumb['url'] && $index < count($breadcrumbs) - 1)
                            <a href="{{ $breadcrumb['url'] }}" class="text-gray-500 hover:text-[#005366] transition-colors">
                                {{ $breadcrumb['name'] }}
                            </a>
                        @else
                            <span class="text-gray-900 font-medium">{{ $breadcrumb['name'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <!-- Main Title -->
        <div class="mb-6">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">All Products</h1>
            <p class="text-lg text-gray-600 mb-4">Discover unique and customizable products from our community of creators</p>
            
            <!-- Stats -->
            <div class="flex items-center space-x-6">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm text-gray-600">{{ $products->total() }} unique products</span>
                </div>
                <div class="flex items-center space-x-1">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= 4 ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    @endfor
                    <span class="text-sm text-gray-600 ml-1">4.2</span>
                </div>
            </div>
        </div>

        <!-- Filter and Sort Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="text-sm text-gray-600">
                Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
            </div>
            
            <div class="flex flex-wrap items-center gap-4">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-3">
                    <!-- Category Filter -->
                    <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Shop Filter -->
                    <select name="shop" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                        <option value="">All Shops</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Sort -->
                    <select name="sort" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Most Relevant</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name: A to Z</option>
                    </select>

                    <button type="submit" class="px-4 py-2 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d] transition-colors text-sm font-medium">
                        Apply Filters
                    </button>

                    @if(request()->hasAny(['category', 'shop', 'min_price', 'max_price', 'search', 'sort']))
                        <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
                            Clear All
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Products Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if($products->isEmpty())
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
            <p class="text-gray-600 mb-6">Try adjusting your filters or search terms</p>
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d] transition-colors">
                View All Products
            </a>
        </div>
    @else
        <!-- Products Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">
            @foreach($products as $product)
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
                        <div class="absolute top-2 left-2 sm:top-3 sm:left-3 opacity-0 group-hover:opacity-100 transition-all duration-200">
                            <x-wishlist-button :product="$product" size="sm" />
                        </div>

                        <!-- Discount Badge -->
                        @if($product->template && $product->template->base_price > $product->price)
                            @php
                                $discount = round((($product->template->base_price - $product->price) / $product->template->base_price) * 100);
                            @endphp
                            <div class="absolute top-2 right-2 sm:top-3 sm:right-3 bg-red-500 text-white px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold">
                                {{ $discount }}% off
                            </div>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <div class="p-2 sm:p-4">
                        <h3 class="font-semibold text-gray-900 mb-1 sm:mb-2 line-clamp-2 group-hover:text-[#005366] transition-colors text-sm sm:text-base">
                            <a href="{{ route('products.show', $product->slug) }}">
                                {{ Str::limit($product->name, 50) }}
                            </a>
                        </h3>
                        
                        <p class="text-xs sm:text-sm text-gray-600 mb-1 sm:mb-2 line-clamp-1">By {{ $product->shop->name ?? 'Unknown Shop' }}</p>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2">
                                @if($product->template && $product->template->base_price > $product->price)
                                    <span class="text-xs sm:text-sm text-gray-500 line-through">${{ number_format($product->template->base_price, 2) }}</span>
                                    <span class="text-base sm:text-lg font-bold text-[#E2150C]">${{ number_format($product->price, 2) }}</span>
                                @else
                                    <span class="text-base sm:text-lg font-bold text-[#E2150C]">${{ number_format($product->base_price, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Sale End Date -->
                        @if($product->template && $product->template->base_price > $product->price)
                            <div class="mt-1 sm:mt-2 text-[10px] sm:text-xs text-red-600 font-medium">
                                Sale ends at {{ now()->addDays(7)->format('F d') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            {{ $products->links() }}
        </div>
    @endif
</div>

<!-- Explore Ongoing Events Section -->
<div class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Explore ongoing events</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Unique Customized Gifts -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="aspect-video bg-gradient-to-br from-pink-100 to-pink-200 p-6 flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-pink-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Unique Customized Gifts</h3>
                        <p class="text-gray-600 mt-2">Personalized items for your loved ones</p>
                    </div>
                </div>
            </div>

            <!-- Trending Collection -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="aspect-video bg-gradient-to-br from-blue-100 to-blue-200 p-6 flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Trending Now</h3>
                        <p class="text-gray-600 mt-2">Discover what's popular this week</p>
                    </div>
                </div>
            </div>

            <!-- Seasonal Collection -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="aspect-video bg-gradient-to-br from-orange-100 to-orange-200 p-6 flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Seasonal Collection</h3>
                        <p class="text-gray-600 mt-2">Perfect for every occasion</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Why Choose Us Section -->
<div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose Custom Products from Bluprinter?</h2>
            <div class="max-w-3xl mx-auto">
                <p class="text-lg text-gray-600 leading-relaxed">
                    At Bluprinter, we believe that every product should tell your unique story. Our platform connects you with talented creators who can bring your vision to life with high-quality, personalized items that are perfect for any occasion.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-[#005366] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Personalized Design</h3>
                <p class="text-gray-600">Create unique products that reflect your personality and style</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-[#E2150C] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Premium Quality</h3>
                <p class="text-gray-600">High-quality materials and printing techniques for lasting results</p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-[#005366] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Fast Delivery</h3>
                <p class="text-gray-600">Quick turnaround times with reliable shipping worldwide</p>
            </div>
        </div>

        <div class="text-center mt-8">
            <a href="#" class="inline-flex items-center px-6 py-3 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d] transition-colors font-semibold">
                See More
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection