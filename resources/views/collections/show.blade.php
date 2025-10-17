@extends('layouts.app')

@section('title', $collection->meta_title ?? $collection->name)
@section('meta_description', $collection->meta_description ?? $collection->description)

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

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
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

    .animate-slideInLeft {
        animation: slideInLeft 0.6s ease-out forwards;
    }

    .animate-slideInRight {
        animation: slideInRight 0.6s ease-out forwards;
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    .animate-shimmer {
        background: linear-gradient(to right, transparent 0%, rgba(255,255,255,0.3) 50%, transparent 100%);
        background-size: 1000px 100%;
        animation: shimmer 2s infinite;
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
</style>

<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200 animate-fadeIn">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="{{ route('home') }}" class="text-gray-500 hover:text-[#005366] transition">Home</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <a href="{{ route('collections.index') }}" class="text-gray-500 hover:text-[#005366] transition">Collections</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-gray-900 font-medium">{{ $collection->name }}</span>
            </nav>
        </div>
    </div>

    <!-- Collection Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <!-- Collection Image -->
                <div class="relative aspect-[4/3] rounded-2xl overflow-hidden bg-gray-100 shadow-xl animate-scaleIn">
                    @if($collection->image)
                        <img src="{{ $collection->image }}" 
                             alt="{{ $collection->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#005366] to-[#003d4d]">
                            <svg class="w-24 h-24 text-white opacity-50 animate-float" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Collection Info -->
                <div class="space-y-6">
                    <h1 class="text-4xl font-bold text-gray-900 animate-fadeInUp stagger-1">{{ $collection->name }}</h1>
                    
                    @if($collection->description)
                        <p class="text-lg text-gray-600 animate-fadeInUp stagger-2">{{ $collection->description }}</p>
                    @endif

                    <div class="flex items-center space-x-6 animate-fadeInUp stagger-3">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            <span class="text-gray-700 font-semibold">{{ $products->total() }} Products</span>
                        </div>

                        @if($collection->shop)
                            <a href="{{ route('shops.show', $collection->shop->shop_slug) }}" class="flex items-center space-x-2 text-gray-600 hover:text-[#005366] transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span>{{ $collection->shop->shop_name }}</span>
                            </a>
                        @endif
                    </div>

                    @if($collection->featured)
                        <div class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold animate-fadeInUp stagger-4">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            Featured Collection
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Products -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filter Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-8 scroll-reveal">
            <form method="GET" class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Price Filter -->
                    <div class="flex items-center gap-2">
                        <input type="number" name="min_price" placeholder="Min Price" 
                               value="{{ request('min_price') }}"
                               class="w-32 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                        <span class="text-gray-500">-</span>
                        <input type="number" name="max_price" placeholder="Max Price" 
                               value="{{ request('max_price') }}"
                               class="w-32 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    </div>
                </div>

                <!-- Sort -->
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700">Sort by:</label>
                    <select name="sort" onchange="this.form.submit()"
                            class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                        <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Default</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name: A-Z</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                    </select>
                    
                    @if(request()->hasAny(['min_price', 'max_price', 'sort']))
                        <a href="{{ route('collections.show', $collection->slug) }}" 
                           class="text-sm text-gray-500 hover:text-[#005366] transition">Clear Filters</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Products Grid -->
        @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                @foreach($products as $product)
                    <a href="{{ route('products.show', $product->slug) }}" class="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 scroll-reveal">
                        <!-- Product Image -->
                        <div class="relative aspect-square bg-gray-100 overflow-hidden">
                            @if($product->primary_image)
                                <img src="{{ $product->primary_image }}" 
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Product Info -->
                        <div class="p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-[#005366] transition line-clamp-2">
                                {{ $product->name }}
                            </h3>
                            
                            <div class="flex items-center justify-between mb-2">
                                @if($product->shop)
                                    <span class="text-sm text-gray-500">{{ $product->shop->shop_name }}</span>
                                @endif
                                @if($product->category)
                                    <span class="text-xs text-gray-400">{{ $product->category->name }}</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-bold text-[#005366]">${{ number_format($product->price, 2) }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8 scroll-reveal">
                {{ $products->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center scroll-reveal">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4 animate-float" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Products Found</h3>
                <p class="text-gray-600 mb-4">This collection doesn't have any products yet.</p>
                @if(request()->hasAny(['min_price', 'max_price', 'sort']))
                    <a href="{{ route('collections.show', $collection->slug) }}" 
                       class="inline-flex items-center px-6 py-3 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d] transition">
                        Clear Filters
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Related Collections -->
    @if($relatedCollections->count() > 0)
        <div class="bg-white border-t border-gray-200 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 scroll-reveal">Related Collections</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedCollections as $related)
                        <a href="{{ route('collections.show', $related->slug) }}" class="group scroll-reveal">
                            <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-gray-100 mb-3">
                                @if($related->image)
                                    <img src="{{ $related->image }}" 
                                         alt="{{ $related->name }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#005366] to-[#003d4d]">
                                        <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute bottom-2 left-2">
                                    <span class="inline-block px-2 py-1 bg-white/90 backdrop-blur text-gray-900 text-xs font-semibold rounded-full">
                                        {{ $related->active_products_count }} items
                                    </span>
                                </div>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-[#005366] transition line-clamp-2">
                                {{ $related->name }}
                            </h3>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@endsection

