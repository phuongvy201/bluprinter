@extends('layouts.app')

@section('content')
<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .animate-fadeInUp {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-fadeIn {
        animation: fadeIn 0.6s ease-out forwards;
    }

    .animate-scaleIn {
        animation: scaleIn 0.5s ease-out forwards;
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    .animate-rotate {
        animation: rotate 20s linear infinite;
    }

    .animate-pulse {
        animation: pulse 2s ease-in-out infinite;
    }

    .animate-slideInLeft {
        animation: slideInLeft 0.6s ease-out forwards;
    }

    .animate-slideInRight {
        animation: slideInRight 0.6s ease-out forwards;
    }

    .stagger-1 { animation-delay: 0.1s; opacity: 0; }
    .stagger-2 { animation-delay: 0.2s; opacity: 0; }
    .stagger-3 { animation-delay: 0.3s; opacity: 0; }
    .stagger-4 { animation-delay: 0.4s; opacity: 0; }
    .stagger-5 { animation-delay: 0.5s; opacity: 0; }
    .stagger-6 { animation-delay: 0.6s; opacity: 0; }

    .scroll-reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .scroll-reveal.revealed {
        opacity: 1;
        transform: translateY(0);
    }

    .circular-item {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .circular-item:hover {
        transform: scale(1.05) translateY(-3px);
    }

    .circular-item.active {
        transform: scale(1.1);
        box-shadow: 0 15px 30px rgba(0, 83, 102, 0.2);
    }

    .gradient-text {
        background: linear-gradient(135deg, #005366 0%, #E2150C 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Collections Slider Styles */
    #collections-slider {
        display: flex;
        transition: transform 0.5s ease-in-out;
    }

    #collections-container {
        position: relative;
        overflow: hidden;
    }

/* Mobile horizontal scroll */
@media (max-width: 640px) {
    #collections-container {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
        scroll-behavior: smooth;
    }
    
    #collections-container::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
    
    #collections-slider {
        display: flex;
        flex-wrap: nowrap;
        transition: none;
        gap: 0;
    }
    
    .mobile-scroll-item {
        flex-shrink: 0;
        width: 140px !important; /* Fixed width for mobile */
        padding: 0 12px;
    }
    
    .mobile-scroll-item:first-child {
        padding-left: 0;
    }
    
    .mobile-scroll-item:last-child {
        padding-right: 0;
    }
    
    /* Hide navigation buttons on mobile */
    #prev-collections, #next-collections {
        display: none !important;
    }
}

/* Mobile scrollbar hiding for Recently Viewed */
@media (max-width: 1023px) {
    .mobile-scroll-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .mobile-scroll-hide::-webkit-scrollbar {
        display: none;
    }
}

/* Mobile horizontal scroll for Blog Posts */
@media (max-width: 767px) {
    #blog-posts-container {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
        scroll-behavior: smooth;
    }
    
    #blog-posts-container::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }
    
    #blog-posts-slider {
        display: flex;
        flex-wrap: nowrap;
        gap: 16px;
    }
    
    .blog-scroll-item {
        flex-shrink: 0;
        width: 280px !important; /* Fixed width for mobile */
    }
}

    /* Navigation Button Styles */
    #prev-collections, #next-collections {
        transition: all 0.3s ease;
    }

    #prev-collections:hover, #next-collections:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    #prev-collections:disabled, #next-collections:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        #prev-collections, #next-collections {
            width: 8px;
            height: 8px;
            font-size: 12px;
        }
    }
</style>



<!-- Dynamic Circular Categories Section -->
<div class="py-16 sm:py-20 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center scroll-reveal">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl">
                Latest 
                <span class="gradient-text">Collections</span>
            </h2>
            <p class="mt-4 sm:mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600 px-4">
                Discover our newest collections and trending designs
            </p>
        </div>

        <!-- Collections Grid -->
        <div class="mt-12 sm:mt-16 md:mt-20">
            <div class="relative">
                <div id="collections-container" class="overflow-hidden">
                    <div id="collections-slider" class="flex transition-transform duration-500 ease-in-out">
                        @php
                            $collections = \App\Models\Collection::where('status', 'active')
                                ->where('admin_approved', true)
                                ->orderBy('created_at', 'desc')
                                ->get();
                            $collectionColors = [
                                'from-green-400 to-green-600',
                                'from-pink-400 to-pink-600', 
                                'from-blue-400 to-blue-600',
                                'from-yellow-400 to-yellow-600',
                                'from-red-400 to-red-600',
                                'from-purple-400 to-purple-600'
                            ];
                        @endphp
                        
                        @foreach($collections as $index => $collection)
                            <div class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/6 flex-shrink-0 px-3 mobile-scroll-item">
                                <a href="{{ route('collections.show', $collection->slug) }}" class="circular-item group scroll-reveal block" 
                                   data-collection="{{ $collection->name }}"
                                   style="animation-delay: {{ $index * 0.1 }}s">
                                    <div class="relative w-32 h-32 sm:w-36 sm:h-36 lg:w-40 lg:h-40 mx-auto">
                                        <!-- Circular Image Container -->
                                        <div class="w-full h-full rounded-full overflow-hidden bg-gradient-to-br {{ $collectionColors[$index % 6] }} shadow-lg group-hover:shadow-xl transition-all duration-300">
                                            @if($collection->image)
                                                <img src="{{ $collection->image }}" 
                                                     alt="{{ $collection->name }}"
                                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-white opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Collection Name -->
                                    <h3 class="mt-4 text-sm sm:text-base font-semibold text-gray-900 text-center group-hover:text-[#005366] transition-colors line-clamp-2">
                                        {{ $collection->name }}
                                    </h3>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Navigation Buttons -->
                @if($collections->count() > 6)
                    <button id="prev-collections" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors z-10">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button id="next-collections" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-colors z-10">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    </button>
                @endif
            </div>
        </div>

        <!-- Categories Grid (like the image) -->
        <div class="mt-16 sm:mt-20 md:mt-24">
            <div class="text-center mb-12 scroll-reveal">
                <h3 class="text-3xl font-bold text-gray-900 mb-4">Shop by Category</h3>
                <p class="text-lg text-gray-600">Discover products in your favorite categories</p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-6 sm:gap-8">
                @php
                    $allCategories = \App\Models\Category::whereNull('parent_id')
                        ->orderBy('name')
                        ->limit(6)
                        ->get();
                @endphp
                
                @foreach($allCategories as $index => $category)
                    <a href="{{ route('category.show', $category->slug) }}" class="group scroll-reveal" 
                       style="animation-delay: {{ $index * 0.1 }}s">
                        <div class="relative overflow-hidden rounded-2xl bg-white shadow-lg hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                            @if($category->image)
                                <div class="aspect-[4/3]">
                                    <img src="{{ $category->image }}" 
                                         alt="{{ $category->name }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                </div>
                            @else
                                <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Category Label Overlay -->
                            <div class="absolute inset-0 bg-black/20 flex items-end">
                                <div class="w-full p-4 bg-gradient-to-t from-black/70 to-transparent">
                                    <h4 class="text-white font-bold text-lg text-center">
                                        {{ $category->name }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Category Details Panel -->
        <div id="category-details" class="mt-12 scroll-reveal hidden">
            <div class="bg-gradient-to-r from-[#005366] to-[#E2150C] rounded-2xl p-8 text-white">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <h3 id="category-title" class="text-2xl font-bold mb-4"></h3>
                        <p id="category-description" class="text-lg opacity-90 mb-6"></p>
                        <div class="flex flex-wrap gap-4">
                            <span class="px-4 py-2 bg-white/20 rounded-full text-sm font-semibold">
                                <span id="category-count">0</span> Products
                            </span>
                            <span class="px-4 py-2 bg-white/20 rounded-full text-sm font-semibold">
                                Starting from $<span id="category-price">0</span>
                            </span>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="aspect-square rounded-2xl overflow-hidden bg-white/10 backdrop-blur">
                            <img id="category-image" src="" alt="" class="w-full h-full object-cover">
                </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent rounded-2xl"></div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Arrivals Section -->
<div class="py-16 sm:py-20 md:py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center scroll-reveal">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl">
                New 
                <span class="gradient-text">Arrivals</span>
            </h2>
            <p class="mt-4 sm:mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600 px-4">
                Discover our latest products from Halloween and Christmas collections
            </p>
        </div>

        <!-- New Arrivals Products -->
        <div class="mt-12 sm:mt-16 md:mt-20">
            <div id="new-arrivals-container" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">
                @php
                    $allNewArrivals = \App\Models\Product::with(['shop', 'template.category'])
                        ->where('status', 'active')
                        ->where(function($query) {
                            $query->where('name', 'like', '%Halloween%')
                                  ->orWhere('name', 'like', '%Christmas%')
                                  ->orWhere('name', 'like', '%Giáng Sinh%')
                                  ->orWhere('name', 'like', '%Noel%')
                                  ->orWhere('name', 'like', '%Holiday%')
                                  ->orWhere('name', 'like', '%Xmas%')
                                  ->orWhere('name', 'like', '%Santa%')
                                  ->orWhere('name', 'like', '%Ghost%')
                                  ->orWhere('name', 'like', '%Pumpkin%')
                                  ->orWhere('name', 'like', '%Spooky%')
                                  ->orWhere('name', 'like', '%Trick%')
                                  ->orWhere('name', 'like', '%Treat%')
                                  ->orWhere('name', 'like', '%Witch%')
                                  ->orWhere('name', 'like', '%Candy%')
                                  ->orWhere('name', 'like', '%Costume%')
                                  ->orWhere('name', 'like', '%Festive%')
                                  ->orWhere('name', 'like', '%Winter%')
                                  ->orWhere('name', 'like', '%Snow%')
                                  ->orWhere('name', 'like', '%Gift%')
                                  ->orWhere('name', 'like', '%Present%')
                                  ->orWhere('name', 'like', '%Tree%')
                                  ->orWhere('name', 'like', '%Star%')
                                  ->orWhere('name', 'like', '%Bell%')
                                  ->orWhere('name', 'like', '%Reindeer%')
                                  ->orWhere('name', 'like', '%Elf%')
                                  ->orWhere('description', 'like', '%Halloween%')
                                  ->orWhere('description', 'like', '%Christmas%')
                                  ->orWhere('description', 'like', '%Giáng Sinh%')
                                  ->orWhere('description', 'like', '%Noel%')
                                  ->orWhere('description', 'like', '%Holiday%')
                                  ->orWhere('description', 'like', '%Xmas%')
                                  ->orWhere('description', 'like', '%Santa%')
                                  ->orWhere('description', 'like', '%Ghost%')
                                  ->orWhere('description', 'like', '%Pumpkin%')
                                  ->orWhere('description', 'like', '%Spooky%')
                                  ->orWhere('description', 'like', '%Trick%')
                                  ->orWhere('description', 'like', '%Treat%')
                                  ->orWhere('description', 'like', '%Witch%')
                                  ->orWhere('description', 'like', '%Candy%')
                                  ->orWhere('description', 'like', '%Costume%')
                                  ->orWhere('description', 'like', '%Festive%')
                                  ->orWhere('description', 'like', '%Winter%')
                                  ->orWhere('description', 'like', '%Snow%')
                                  ->orWhere('description', 'like', '%Gift%')
                                  ->orWhere('description', 'like', '%Present%')
                                  ->orWhere('description', 'like', '%Tree%')
                                  ->orWhere('description', 'like', '%Star%')
                                  ->orWhere('description', 'like', '%Bell%')
                                  ->orWhere('description', 'like', '%Reindeer%')
                                  ->orWhere('description', 'like', '%Elf%');
                        })
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    // Chỉ hiển thị sản phẩm có từ khóa Halloween/Christmas, không lấy sản phẩm mới nhất
                    
                    // Lấy 10 sản phẩm đầu tiên để hiển thị ban đầu
                    $newArrivals = $allNewArrivals->take(10);
                @endphp
                
                @if($newArrivals->count() > 0)
                    @foreach($newArrivals as $index => $product)
                        <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 group overflow-hidden scroll-reveal" style="animation-delay: {{ $index * 0.1 }}s">
                            <!-- Product Image -->
                            <div class="relative aspect-square overflow-hidden">
                                @php
                                    $media = $product->getEffectiveMedia();
                                @endphp
                                @if($media && count($media) > 0)
                                    <img src="{{ is_array($media[0]) ? $media[0]['url'] : $media[0] }}" 
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

                                <!-- New Badge -->
                                <div class="absolute top-2 right-2 sm:top-3 sm:right-3 bg-[#E2150C] text-white px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold">
                                    NEW
                                </div>
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
                @else
                    <!-- Empty State for Holiday Products -->
                    <div class="col-span-full text-center py-12">
                        <div class="max-w-md mx-auto">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-100 to-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Holiday Products Yet</h3>
                            <p class="text-gray-600 mb-6">We're working on adding Halloween and Christmas themed products. Check back soon!</p>
                            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d] transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Browse All Products
                            </a>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- View All and Load More Buttons for New Arrivals -->
            @if($newArrivals->count() > 0)
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
                    <a href="{{ route('products.index', ['filter' => 'new']) }}" 
                       class="bg-[#E2150C] hover:bg-[#c0120a] text-white px-8 py-3 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        View All Holiday Products
                    </a>
                    @if($allNewArrivals->count() > 10)
                        <button id="load-more-new-arrivals" 
                                class="bg-[#005366] hover:bg-[#003d4d] text-white px-8 py-3 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                                data-offset="10"
                                data-total="{{ $allNewArrivals->count() }}">
                            Load More
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Best Sellers Section -->
<div class="py-16 sm:py-20 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center scroll-reveal">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl">
                Best 
                <span class="gradient-text">Sellers</span>
            </h2>
            <p class="mt-4 sm:mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600 px-4">
                Our most popular products loved by customers worldwide
            </p>
        </div>

        <!-- Best Sellers Products -->
        <div class="mt-12 sm:mt-16 md:mt-20">
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-6">
                @php
                    $bestSellers = \App\Models\Product::with(['shop', 'template.category'])
                        ->where('status', 'active')
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
                @endphp
                
                @foreach($bestSellers as $index => $product)
                    <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 group overflow-hidden scroll-reveal" style="animation-delay: {{ $index * 0.1 }}s">
                        <!-- Product Image -->
                        <div class="relative aspect-square overflow-hidden">
                            @php
                                $media = $product->getEffectiveMedia();
                            @endphp
                            @if($media && count($media) > 0)
                                <img src="{{ is_array($media[0]) ? $media[0]['url'] : $media[0] }}" 
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

                            <!-- Best Seller Badge -->
                            <div class="absolute top-2 right-2 sm:top-3 sm:right-3 bg-[#005366] text-white px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold">
                                BEST
                            </div>
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
            
            <!-- View All Button for Best Sellers -->
            <div class="text-center mt-8">
                <a href="{{ route('products.index', ['filter' => 'bestsellers']) }}" 
                   class="bg-[#005366] hover:bg-[#003d4d] text-white px-8 py-3 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    View All Best Sellers
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Collections Showcase Section -->
<div class="py-16 sm:py-20 md:py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center scroll-reveal">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl">
                Featured 
                <span class="gradient-text">Collections</span>
            </h2>
            <p class="mt-4 sm:mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600 px-4">
                Explore our curated collections of amazing designs
            </p>
        </div>

        <!-- Collections Grid -->
        <div class="mt-12 sm:mt-16 md:mt-20">
            <div id="featured-collections-container" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @php
                    $allFeaturedCollections = \App\Models\Collection::where('status', 'active')
                        ->where('admin_approved', true)
                        ->where('featured', true)
                        ->orderBy('sort_order')
                        ->orderBy('created_at', 'desc')
                        ->get();
                    
                    // Lấy 4 collections đầu tiên để hiển thị ban đầu
                    $featuredCollections = $allFeaturedCollections->take(4);
                @endphp
                
                @foreach($featuredCollections as $index => $collection)
                    <a href="{{ route('collections.show', $collection->slug) }}" class="group scroll-reveal" 
                       style="animation-delay: {{ $index * 0.1 }}s">
                        <div class="relative overflow-hidden rounded-2xl bg-white shadow-lg hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                            @if($collection->image)
                                <div class="aspect-[4/3]">
                                    <img src="{{ $collection->image }}" 
                                         alt="{{ $collection->name }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                </div>
                            @else
                                <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Collection Info Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
                                <div class="w-full p-6">
                                    <h3 class="text-white font-bold text-xl mb-2">
                                        {{ $collection->name }}
                                    </h3>
                                    <p class="text-white/90 text-sm mb-4 line-clamp-2">
                                        {{ $collection->description }}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-white/80 text-sm">
                                            {{ $collection->products()->count() }} Products
                                        </span>
                                        <span class="text-white font-semibold text-sm">
                                            View Collection →
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            
            <!-- View All and Load More Buttons for Featured Collections -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
                <a href="{{ route('collections.index') }}" 
                   class="bg-[#E2150C] hover:bg-[#c0120a] text-white px-8 py-3 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    View All Collections
                </a>
                @if($allFeaturedCollections->count() > 4)
                    <button id="load-more-collections" 
                            class="bg-[#005366] hover:bg-[#003d4d] text-white px-8 py-3 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
                            data-offset="4"
                            data-total="{{ $allFeaturedCollections->count() }}">
                        Load More
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Posts Section -->
<div class="py-16 sm:py-20 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center scroll-reveal">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl">
                Latest 
                <span class="gradient-text">Blog Posts</span>
            </h2>
            <p class="mt-4 sm:mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600 px-4">
                Stay updated with our latest design tips, trends, and inspiration
            </p>
        </div>

        <!-- Recent Posts Grid -->
        <div class="mt-12 sm:mt-16 md:mt-20">
            <div id="blog-posts-container" class="overflow-hidden">
                <div id="blog-posts-slider" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @php
                        $recentPosts = \App\Models\Post::with(['user'])
                            ->where('status', 'published')
                            ->orderBy('created_at', 'desc')
                            ->limit(6)
                            ->get();
                    @endphp
                    
                    @foreach($recentPosts as $index => $post)
                        <article class="group scroll-reveal blog-scroll-item" style="animation-delay: {{ $index * 0.1 }}s">
                        <a href="{{ route('blog.show', $post->slug) }}" class="block">
                            <div class="relative overflow-hidden rounded-xl bg-white shadow-lg hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                                @if($post->featured_image)
                                    <div class="aspect-[4/3]">
                                        <img src="{{ $post->featured_image }}" 
                                             alt="{{ $post->title }}"
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    </div>
                                @else
                                    <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                        </svg>
                                    </div>
                                @endif
                                
                                <!-- Post Info -->
                                <div class="p-6">
                                    <div class="flex items-center text-sm text-gray-500 mb-3">
                                        <span>{{ $post->created_at->format('M d, Y') }}</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ $post->user->name ?? 'Admin' }}</span>
                                    </div>
                                    
                                    <h3 class="font-bold text-gray-900 text-lg mb-3 line-clamp-2 group-hover:text-[#005366] transition-colors">
                                        {{ $post->title }}
                                    </h3>
                                    
                                    <p class="text-gray-600 text-sm line-clamp-3 mb-4">
                                        {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 120) }}
                                    </p>
                                    
                                    <div class="flex items-center justify-between">
                                        <span class="text-[#005366] font-semibold text-sm group-hover:text-[#E2150C] transition-colors">
                                            Read More →
                                        </span>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                                                Blog
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        </article>
                    @endforeach
                </div>
            </div>
            
            <!-- View All Button for Blog Posts -->
            <div class="text-center mt-8">
                <a href="{{ route('blog.index') }}" 
                   class="bg-[#005366] hover:bg-[#003d4d] text-white px-8 py-3 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    View All Posts
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recently Viewed Section -->
<div class="py-16 sm:py-20 md:py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center scroll-reveal">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl">
                Recently 
                <span class="gradient-text">Viewed</span>
            </h2>
            <p class="mt-4 sm:mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600 px-4">
                Continue exploring products you've shown interest in
            </p>
        </div>

                <!-- Recently Viewed Products -->
                <div class="mt-12 sm:mt-16 md:mt-20">
                    <div class="relative" id="recently-viewed-wrapper">
                        <!-- Navigation Buttons (Desktop only) -->
                        <button id="recentlyViewedPrevBtn" 
                                onclick="scrollRecentlyViewed('prev')"
                                class="hidden lg:block absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-50 transition-all opacity-0 group-hover:opacity-100">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        
                        <button id="recentlyViewedNextBtn" 
                                onclick="scrollRecentlyViewed('next')"
                                class="hidden lg:block absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-50 transition-all">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        
                        <!-- Products Container -->
                        <div id="recentlyViewedContainer" class="overflow-x-auto lg:overflow-hidden mobile-scroll-hide group pb-2" style="scroll-behavior: smooth;">
                            <div id="recently-viewed-container" class="flex gap-3 lg:transition-transform lg:duration-300">
                                <!-- Products will be loaded here by JavaScript -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div id="recently-viewed-empty" class="text-center py-12 hidden">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg mb-2">No products viewed yet</p>
                        <p class="text-gray-400 text-sm">Products you view will appear here</p>
                    </div>
                </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="py-16 sm:py-20 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center scroll-reveal">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl">
                What Our 
                <span class="gradient-text">Customers Say</span>
            </h2>
            <p class="mt-4 sm:mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600 px-4">
                Don't just take our word for it - hear from our satisfied customers
            </p>
        </div>

        <!-- Testimonials Grid -->
        <div class="mt-12 sm:mt-16 md:mt-20">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @php
                    $testimonials = [
                        [
                            'name' => 'Sarah Johnson',
                            'role' => 'Small Business Owner',
                            'content' => 'Bluprinter transformed my business with their amazing custom designs. The quality is outstanding and the customer service is top-notch!',
                            'rating' => 5,
                            'avatar' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=100&h=100&fit=crop&crop=face'
                        ],
                        [
                            'name' => 'Mike Chen',
                            'role' => 'Marketing Director',
                            'content' => 'We\'ve been using Bluprinter for all our promotional materials. Fast delivery, great prices, and the designs always exceed expectations.',
                            'rating' => 5,
                            'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face'
                        ],
                        [
                            'name' => 'Emily Rodriguez',
                            'role' => 'Event Planner',
                            'content' => 'Perfect for our wedding invitations and event materials. The attention to detail and customization options are incredible.',
                            'rating' => 5,
                            'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=100&h=100&fit=crop&crop=face'
                        ]
                    ];
                @endphp
                
                @foreach($testimonials as $index => $testimonial)
                    <div class="scroll-reveal" style="animation-delay: {{ $index * 0.1 }}s">
                        <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                            <!-- Rating -->
                            <div class="flex items-center mb-4">
                                @for($i = 0; $i < $testimonial['rating']; $i++)
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                            </div>
                            
                            <!-- Content -->
                            <p class="text-gray-700 mb-6 italic">
                                "{{ $testimonial['content'] }}"
                            </p>
                            
                            <!-- Author -->
                            <div class="flex items-center">
                                <img src="{{ $testimonial['avatar'] }}" 
                                     alt="{{ $testimonial['name'] }}"
                                     class="w-12 h-12 rounded-full object-cover mr-4">
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $testimonial['name'] }}</h4>
                                    <p class="text-sm text-gray-600">{{ $testimonial['role'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-16 sm:py-20 md:py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center scroll-reveal">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl">
                Why Choose 
                <span class="gradient-text">Bluprinter</span>?
            </h2>
            <p class="mt-4 sm:mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600 px-4">
                We provide professional customization services with cutting-edge technology and exceptional customer support.
            </p>
        </div>

        <div class="mt-12 sm:mt-16 md:mt-20 grid grid-cols-1 gap-6 sm:gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Feature 1 -->
            <div class="text-center group p-6 rounded-2xl hover:shadow-lg transition-all duration-300 scroll-reveal">
                <div class="flex items-center justify-center h-20 w-20 rounded-2xl bg-gradient-to-br from-[#005366] to-[#003d4d] text-white mx-auto shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-110">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-semibold text-gray-900">Premium Quality</h3>
                <p class="mt-3 text-base text-gray-600">
                    Professional-grade materials and state-of-the-art printing technology
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="text-center group p-6 rounded-2xl hover:shadow-lg transition-all duration-300 scroll-reveal">
                <div class="flex items-center justify-center h-20 w-20 rounded-2xl bg-gradient-to-br from-[#E2150C] to-[#c0120a] text-white mx-auto shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-110">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-semibold text-gray-900">Fast Delivery</h3>
                <p class="mt-3 text-base text-gray-600">
                    Quick turnaround times with express shipping options available
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="text-center group p-6 rounded-2xl hover:shadow-lg transition-all duration-300 scroll-reveal">
                <div class="flex items-center justify-center h-20 w-20 rounded-2xl bg-gradient-to-br from-[#005366] to-[#003d4d] text-white mx-auto shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-110">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-semibold text-gray-900">Fair Pricing</h3>
                <p class="mt-3 text-base text-gray-600">
                    Transparent pricing with no hidden fees and competitive rates
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="text-center group p-6 rounded-2xl hover:shadow-lg transition-all duration-300 scroll-reveal">
                <div class="flex items-center justify-center h-20 w-20 rounded-2xl bg-gradient-to-br from-[#E2150C] to-[#c0120a] text-white mx-auto shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-110">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z"></path>
                    </svg>
                </div>
                <h3 class="mt-6 text-xl font-semibold text-gray-900">24/7 Support</h3>
                <p class="mt-3 text-base text-gray-600">
                    Dedicated customer support team available around the clock
                </p>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load recently viewed products
        loadRecentlyViewed();
        
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all scroll-reveal elements
        document.querySelectorAll('.scroll-reveal').forEach(element => {
            observer.observe(element);
        });

        // Collections slider functionality
        const collectionsSlider = document.getElementById('collections-slider');
        const prevCollectionsBtn = document.getElementById('prev-collections');
        const nextCollectionsBtn = document.getElementById('next-collections');
        let currentSlide = 0;
        const itemsPerView = {
            mobile: 2,
            tablet: 3,
            desktop: 6
        };

        function getItemsPerView() {
            if (window.innerWidth < 640) return itemsPerView.mobile;
            if (window.innerWidth < 1024) return itemsPerView.tablet;
            return itemsPerView.desktop;
        }

        function isMobile() {
            return window.innerWidth < 640;
        }

        function updateSlider() {
            // Skip slider logic on mobile - use native scroll
            if (isMobile()) {
                return;
            }

            const itemsPerView = getItemsPerView();
            const totalItems = {{ $collections->count() }};
            const maxSlides = Math.max(0, totalItems - itemsPerView);
            
            // Ensure currentSlide is within bounds
            currentSlide = Math.min(currentSlide, maxSlides);
            currentSlide = Math.max(0, currentSlide);
            
            // Calculate translateX based on item width
            const itemWidth = 100 / itemsPerView;
            const translateX = -(currentSlide * itemWidth);
            collectionsSlider.style.transform = `translateX(${translateX}%)`;
            
            // Update button states
            if (prevCollectionsBtn && nextCollectionsBtn) {
                const canGoPrev = currentSlide > 0;
                const canGoNext = currentSlide < maxSlides;
                
                prevCollectionsBtn.style.opacity = canGoPrev ? '1' : '0.5';
                nextCollectionsBtn.style.opacity = canGoNext ? '1' : '0.5';
                prevCollectionsBtn.disabled = !canGoPrev;
                nextCollectionsBtn.disabled = !canGoNext;
                
                // Hide buttons if no need for navigation
                if (totalItems <= itemsPerView) {
                    prevCollectionsBtn.style.display = 'none';
                    nextCollectionsBtn.style.display = 'none';
                } else {
                    prevCollectionsBtn.style.display = 'flex';
                    nextCollectionsBtn.style.display = 'flex';
                }
            }
        }

        // Only initialize slider buttons for desktop/tablet
        if (prevCollectionsBtn && nextCollectionsBtn && !isMobile()) {
            prevCollectionsBtn.addEventListener('click', () => {
                if (isMobile()) return;
                const itemsPerView = getItemsPerView();
                const totalItems = {{ $collections->count() }};
                const maxSlides = Math.max(0, totalItems - itemsPerView);
                
                if (currentSlide > 0) {
                    currentSlide--;
                    updateSlider();
                }
            });

            nextCollectionsBtn.addEventListener('click', () => {
                if (isMobile()) return;
                const itemsPerView = getItemsPerView();
                const totalItems = {{ $collections->count() }};
                const maxSlides = Math.max(0, totalItems - itemsPerView);
                
                if (currentSlide < maxSlides) {
                    currentSlide++;
                    updateSlider();
                }
            });

            // Handle window resize
            window.addEventListener('resize', () => {
                if (!isMobile()) {
                    updateSlider();
                }
            });

            // Initialize slider
            updateSlider();
            
            // Debug info
            console.log('Collections count:', {{ $collections->count() }});
            console.log('Items per view:', getItemsPerView());
            console.log('Max slides:', Math.max(0, {{ $collections->count() }} - getItemsPerView()));
        }

        // Circular categories interaction
        const circularItems = document.querySelectorAll('.circular-item');
        const categoryDetails = document.getElementById('category-details');
        const categoryTitle = document.getElementById('category-title');
        const categoryDescription = document.getElementById('category-description');
        const categoryCount = document.getElementById('category-count');
        const categoryPrice = document.getElementById('category-price');
        const categoryImage = document.getElementById('category-image');

        // Sample category data
        const categoryData = {
            "Happy Patrick's Day": {
                description: "Celebrate St. Patrick's Day with our festive collection of green-themed products and designs.",
                count: 25,
                price: 19.99,
                image: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=600&h=400&fit=crop&crop=center"
            },
            "Easter's Day": {
                description: "Beautiful Easter-themed products perfect for the spring season and family celebrations.",
                count: 18,
                price: 24.99,
                image: "https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=600&h=400&fit=crop&crop=center"
            },
            "3D Hoodies": {
                description: "Premium 3D printed hoodies with stunning visual effects and comfortable materials.",
                count: 32,
                price: 49.99,
                image: "https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&h=400&fit=crop&crop=center"
            },
            "Calendar 2025": {
                description: "Custom 2025 calendars with your designs, perfect for planning and organization.",
                count: 15,
                price: 12.99,
                image: "https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=600&h=400&fit=crop&crop=center"
            },
            "Baseball Jersey": {
                description: "Professional baseball jerseys with custom team colors and player names.",
                count: 28,
                price: 39.99,
                image: "https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=center"
            },
            "3D Sweater": {
                description: "Cozy 3D sweaters with intricate patterns and premium wool materials.",
                count: 22,
                price: 59.99,
                image: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=600&h=400&fit=crop&crop=center"
            },
            "Hoodie": {
                description: "Comfortable and stylish hoodies perfect for casual wear and outdoor activities.",
                count: 35,
                price: 29.99,
                image: "https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&h=400&fit=crop&crop=center"
            },
            "Premium Posters": {
                description: "High-quality posters with vibrant colors and professional printing techniques.",
                count: 42,
                price: 14.99,
                image: "https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=600&h=400&fit=crop&crop=center"
            }
        };

        // Handle collections and categories interaction
        circularItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // Check if it's a collection or category
                const isCollection = this.dataset.collection;
                const isCategory = this.dataset.category;
                
                if (isCollection) {
                    // For collections, just navigate to the collection page
                    return; // Let the default link behavior handle navigation
                }
                
                if (isCategory) {
                    // For categories, show details panel
                    e.preventDefault(); // Prevent default link behavior
                    
                    // Remove active class from all items
                    circularItems.forEach(i => i.classList.remove('active'));
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    // Get category name
                    const categoryName = this.dataset.category;
                    const data = categoryData[categoryName];
                    
                    if (data) {
                        // Update category details
                        categoryTitle.textContent = categoryName;
                        categoryDescription.textContent = data.description;
                        categoryCount.textContent = data.count;
                        categoryPrice.textContent = data.price;
                        categoryImage.src = data.image;
                        categoryImage.alt = categoryName;
                        
                        // Show category details with animation
                        categoryDetails.classList.remove('hidden');
                        categoryDetails.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });

            // Add hover effects
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05) translateY(-3px)';
            });

            item.addEventListener('mouseleave', function() {
                if (!this.classList.contains('active')) {
                    this.style.transform = 'scale(1) translateY(0)';
                }
            });
        });
    });

    // Recently Viewed Functions
    function loadRecentlyViewed() {
        const recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
        const container = document.getElementById('recently-viewed-container');
        const emptyState = document.getElementById('recently-viewed-empty');
        const wrapper = document.getElementById('recently-viewed-wrapper');
        
        console.log('Loading recently viewed products:', recentlyViewed);
        
        if (!container) {
            console.log('Recently viewed container not found');
            return;
        }
        
        // Filter out current product and limit to 12 products
        const productsToShow = recentlyViewed.slice(0, 12);
        
        console.log('Products to show:', productsToShow.length);
        
        if (productsToShow.length === 0) {
            if (wrapper) wrapper.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
            console.log('No recently viewed products to display');
            return;
        }
        
        if (wrapper) wrapper.classList.remove('hidden');
        emptyState.classList.add('hidden');
        
        // Generate HTML for each product (same style as Related Products)
        container.innerHTML = productsToShow.map(product => `
            <a href="/products/${product.slug}" 
               class="flex-shrink-0 w-52 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 group/item overflow-hidden">
                <!-- Product Image -->
                <div class="relative aspect-square overflow-hidden">
                    ${product.image ? `
                        <img src="${product.image}" 
                             alt="${product.name}" 
                             class="w-full h-full object-cover group-hover/item:scale-105 transition-transform duration-300"
                             onerror="this.parentElement.innerHTML='<div class=\\'w-full h-full bg-gray-200 flex items-center justify-center\\'><svg class=\\'w-8 h-8 text-gray-400\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\\'></path></svg></div>'">
                    ` : `
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                    `}
            </div>

                <!-- Product Info -->
                <div class="p-3">
                    <h4 class="font-semibold text-gray-900 text-sm line-clamp-2 group-hover/item:text-[#005366] transition-colors mb-2 min-h-[40px]" title="${product.name}">
                        ${product.name.length > 40 ? product.name.substring(0, 40) + '...' : product.name}
                    </h4>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-[#E2150C]">$${parseFloat(product.price).toFixed(2)}</span>
                        <div class="flex items-center text-xs text-gray-500">
                            <svg class="w-4 h-4 text-yellow-400 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                            <span class="text-sm">4.5</span>
                </div>
                    </div>
                </div>
            </a>
        `).join('');
        
        // Show/hide navigation buttons based on number of products
        updateRecentlyViewedNavigation(productsToShow.length);
    }

    // Recently Viewed Carousel Navigation
    let recentlyViewedCurrentIndex = 0;

    function scrollRecentlyViewed(direction) {
        const container = document.getElementById('recentlyViewedContainer');
        const track = document.getElementById('recently-viewed-container');
        const prevBtn = document.getElementById('recentlyViewedPrevBtn');
        const nextBtn = document.getElementById('recentlyViewedNextBtn');
        
        if (!track) return;
        
        const itemWidth = 208 + 12; // w-52 (208px) + gap-3 (12px)
        const containerWidth = container.offsetWidth;
        const itemsVisible = Math.floor(containerWidth / itemWidth);
        const totalItems = track.children.length;
        const maxIndex = Math.max(0, totalItems - itemsVisible);
        
        if (direction === 'next') {
            recentlyViewedCurrentIndex = Math.min(recentlyViewedCurrentIndex + itemsVisible, maxIndex);
        } else {
            recentlyViewedCurrentIndex = Math.max(0, recentlyViewedCurrentIndex - itemsVisible);
        }
        
        const translateX = -recentlyViewedCurrentIndex * itemWidth;
        track.style.transform = `translateX(${translateX}px)`;
        
        // Update button states
        if (prevBtn) {
            if (recentlyViewedCurrentIndex === 0) {
                prevBtn.classList.add('opacity-0');
            } else {
                prevBtn.classList.remove('opacity-0');
            }
        }
        
        if (nextBtn) {
            if (recentlyViewedCurrentIndex >= maxIndex) {
                nextBtn.classList.add('opacity-0');
            } else {
                nextBtn.classList.remove('opacity-0');
            }
        }
    }

    function updateRecentlyViewedNavigation(totalProducts) {
        const prevBtn = document.getElementById('recentlyViewedPrevBtn');
        const nextBtn = document.getElementById('recentlyViewedNextBtn');
        
        if (!prevBtn || !nextBtn) return;
        
        // Only show navigation buttons on desktop (lg: 1024px+) if more than what can fit on screen
        const isDesktop = window.innerWidth >= 1024;
        
        // With w-52 (208px) cards, approximately 4 cards fit on a typical 1024px+ screen
        if (isDesktop && totalProducts > 4) {
            prevBtn.classList.remove('hidden');
            prevBtn.classList.add('lg:block');
            nextBtn.classList.remove('hidden');
            nextBtn.classList.add('lg:block');
            
            // Set initial state
            prevBtn.classList.add('opacity-0');
            nextBtn.classList.remove('opacity-0');
            
            // Reset index
            recentlyViewedCurrentIndex = 0;
            
            // Reset transform (only for desktop)
            const track = document.getElementById('recently-viewed-container');
            if (track) {
                track.style.transform = 'translateX(0px)';
            }
        } else {
            // Hide navigation buttons on mobile or if 4 or fewer products
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            
            // Remove transform on mobile
            const track = document.getElementById('recently-viewed-container');
            if (track && !isDesktop) {
                track.style.transform = '';
            }
        }
    }

    // Load More functionality for New Arrivals
    function loadMoreNewArrivals() {
        const button = document.getElementById('load-more-new-arrivals');
        const container = document.getElementById('new-arrivals-container');
        
        if (!button || !container) return;
        
        const offset = parseInt(button.dataset.offset);
        const total = parseInt(button.dataset.total);
        
        // Simulate loading more products (in real app, this would be an AJAX call)
        fetch(`/api/products/new-arrivals?offset=${offset}&limit=10`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.products.length > 0) {
                    // Add new products to container
                    data.products.forEach((product, index) => {
                        const productHtml = `
                            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 group overflow-hidden scroll-reveal" style="animation-delay: ${index * 0.1}s">
                                <!-- Product Image -->
                                <div class="relative aspect-square overflow-hidden">
                                    ${product.image ? `
                                        <img src="${product.image}" 
                                             alt="${product.name}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    ` : `
                                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    `}
                                    
                                    <!-- Wishlist Button -->
                                    <div class="absolute top-2 left-2 sm:top-3 sm:left-3 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                        <button class="w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- New Badge -->
                                    <div class="absolute top-2 right-2 sm:top-3 sm:right-3 bg-[#E2150C] text-white px-1.5 py-0.5 sm:px-2 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold">
                                        NEW
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="p-2 sm:p-4">
                                    <h3 class="font-semibold text-gray-900 mb-1 sm:mb-2 line-clamp-2 group-hover:text-[#005366] transition-colors text-sm sm:text-base">
                                        <a href="/products/${product.slug}">
                                            ${product.name.length > 50 ? product.name.substring(0, 50) + '...' : product.name}
                                        </a>
                                    </h3>
                                    
                                    <p class="text-xs sm:text-sm text-gray-600 mb-1 sm:mb-2 line-clamp-1">By ${product.shop_name || 'Unknown Shop'}</p>
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2">
                                            <span class="text-base sm:text-lg font-bold text-[#E2150C]">$${parseFloat(product.base_price).toFixed(2)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', productHtml);
                    });
                    
                    // Update offset
                    const newOffset = offset + 10;
                    button.dataset.offset = newOffset;
                    
                    // Hide button if no more products
                    if (newOffset >= total) {
                        button.style.display = 'none';
                    }
                    
                    // Trigger scroll reveal animation for new items
                    const newItems = container.querySelectorAll('.scroll-reveal:not(.revealed)');
                    newItems.forEach(item => {
                        observer.observe(item);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading more products:', error);
            });
    }

    // Load More functionality for Featured Collections
    function loadMoreCollections() {
        const button = document.getElementById('load-more-collections');
        const container = document.getElementById('featured-collections-container');
        
        if (!button || !container) return;
        
        const offset = parseInt(button.dataset.offset);
        const total = parseInt(button.dataset.total);
        
        // Simulate loading more collections (in real app, this would be an AJAX call)
        fetch(`/api/collections/featured?offset=${offset}&limit=4`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.collections.length > 0) {
                    // Add new collections to container
                    data.collections.forEach((collection, index) => {
                        const collectionHtml = `
                            <a href="/collections/${collection.slug}" class="group scroll-reveal" 
                               style="animation-delay: ${index * 0.1}s">
                                <div class="relative overflow-hidden rounded-2xl bg-white shadow-lg hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                                    ${collection.image ? `
                                        <div class="aspect-[4/3]">
                                            <img src="${collection.image}" 
                                                 alt="${collection.name}"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        </div>
                                    ` : `
                                        <div class="aspect-[4/3] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                        </div>
                                    `}
                                    
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
                                        <div class="w-full p-6">
                                            <h3 class="text-white font-bold text-xl mb-2">
                                                ${collection.name}
                                            </h3>
                                            <p class="text-white/90 text-sm mb-4 line-clamp-2">
                                                ${collection.description}
                                            </p>
                                            <div class="flex items-center justify-between">
                                                <span class="text-white/80 text-sm">
                                                    ${collection.products_count} Products
                                                </span>
                                                <span class="text-white font-semibold text-sm">
                                                    View Collection →
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        `;
                        container.insertAdjacentHTML('beforeend', collectionHtml);
                    });
                    
                    // Update offset
                    const newOffset = offset + 4;
                    button.dataset.offset = newOffset;
                    
                    // Hide button if no more collections
                    if (newOffset >= total) {
                        button.style.display = 'none';
                    }
                    
                    // Trigger scroll reveal animation for new items
                    const newItems = container.querySelectorAll('.scroll-reveal:not(.revealed)');
                    newItems.forEach(item => {
                        observer.observe(item);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading more collections:', error);
            });
    }

    // Add event listeners for Load More buttons
    document.addEventListener('DOMContentLoaded', function() {
        const loadMoreNewArrivalsBtn = document.getElementById('load-more-new-arrivals');
        const loadMoreCollectionsBtn = document.getElementById('load-more-collections');
        
        if (loadMoreNewArrivalsBtn) {
            loadMoreNewArrivalsBtn.addEventListener('click', loadMoreNewArrivals);
        }
        
        if (loadMoreCollectionsBtn) {
            loadMoreCollectionsBtn.addEventListener('click', loadMoreCollections);
        }
    });
</script>
@endsection