@extends('layouts.app')

@section('title', 'Collections - Bluprinter')
@section('meta_description', 'Browse our curated collections of custom products')

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

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
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

    .animate-slideDown {
        animation: slideDown 0.6s ease-out forwards;
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    .animate-pulse-slow {
        animation: pulse 2s ease-in-out infinite;
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

    .gradient-text {
        background: linear-gradient(135deg, #005366 0%, #E2150C 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-[#005366] to-[#003d4d] text-white py-16 overflow-hidden relative">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full -translate-x-32 -translate-y-32 animate-float"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full translate-x-48 translate-y-48 animate-pulse-slow"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-slideDown">Collections</h1>
            <p class="text-xl text-gray-200 max-w-2xl mx-auto animate-fadeInUp stagger-1">
                Discover curated collections of custom products for every occasion
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-9">
                <!-- Filter & Sort Bar -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-8 scroll-reveal">
                    <form method="GET" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <div class="relative">
                                <input type="text" name="search" placeholder="Search collections..." 
                                       value="{{ request('search') }}"
                                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent transition-all">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="flex items-center gap-3">
                            <label class="text-sm font-medium text-gray-700">Sort by:</label>
                            <select name="sort" onchange="this.form.submit()"
                                    class="px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent transition-all">
                                <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>Featured</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                                <option value="products" {{ request('sort') == 'products' ? 'selected' : '' }}>Most Products</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Collections Grid -->
                @if($collections->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        @foreach($collections as $collection)
                            <a href="{{ route('collections.show', $collection->slug) }}" class="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 scroll-reveal">
                                <!-- Collection Image -->
                                <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden">
                                    @if($collection->image)
                                        <img src="{{ $collection->image }}" 
                                             alt="{{ $collection->name }}"
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#005366] to-[#003d4d]">
                                            <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    
                                    @if($collection->featured)
                                        <div class="absolute top-2 right-2">
                                            <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold animate-pulse-slow">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                                Featured
                                            </span>
                                        </div>
                                    @endif

                                    <div class="absolute bottom-2 left-2">
                                        <span class="inline-block px-3 py-1 bg-white/90 backdrop-blur text-gray-900 text-sm font-semibold rounded-full">
                                            {{ $collection->active_products_count }} items
                                        </span>
                                    </div>
                                </div>

                                <!-- Collection Info -->
                                <div class="p-4">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2 group-hover:text-[#005366] transition line-clamp-2">
                                        {{ $collection->name }}
                                    </h3>
                                    
                                    @if($collection->description)
                                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $collection->description }}</p>
                                    @endif

                                    @if($collection->shop)
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            {{ $collection->shop->shop_name }}
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8 scroll-reveal">
                        {{ $collections->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center scroll-reveal">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4 animate-float" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Collections Found</h3>
                        <p class="text-gray-600">Try adjusting your search or filters</p>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-3 mt-8 lg:mt-0">
                <!-- Featured Collections -->
                @if($featuredCollections->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 scroll-reveal">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            Featured Collections
                        </h3>
                        <div class="space-y-4">
                            @foreach($featuredCollections as $featured)
                                <a href="{{ route('collections.show', $featured->slug) }}" class="group flex items-center space-x-3 hover:bg-gray-50 p-2 rounded-lg transition">
                                    <div class="relative w-16 h-16 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                        @if($featured->image)
                                            <img src="{{ $featured->image }}" 
                                                 alt="{{ $featured->name }}"
                                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#005366] to-[#003d4d]">
                                                <svg class="w-6 h-6 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold text-gray-900 group-hover:text-[#005366] transition line-clamp-2">
                                            {{ $featured->name }}
                                        </h4>
                                        <p class="text-xs text-gray-500">{{ $featured->active_products_count }} items</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Stats -->
                <div class="bg-gradient-to-br from-[#005366] to-[#003d4d] rounded-xl shadow-sm p-6 text-white scroll-reveal relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -translate-y-16 translate-x-16 animate-pulse-slow"></div>
                    <h3 class="text-lg font-bold mb-4 relative z-10">Collections Stats</h3>
                    <div class="space-y-3 relative z-10">
                        <div class="flex items-center justify-between p-3 bg-white/10 rounded-lg backdrop-blur">
                            <span class="text-sm">Total Collections</span>
                            <span class="text-2xl font-bold">{{ $collections->total() }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-white/10 rounded-lg backdrop-blur">
                            <span class="text-sm">Featured</span>
                            <span class="text-2xl font-bold">{{ $featuredCollections->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

