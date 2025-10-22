@extends('layouts.app')

@section('title', $product->name)

@section('content')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<script>
// Track Facebook Pixel ViewContent for product detail page
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: '{{ addslashes($product->name) }}',
            content_ids: ['{{ $product->id }}'],
            content_type: 'product',
            value: {{ $product->base_price }},
            currency: 'USD'
        });
    }
});
</script>
<!-- Breadcrumb -->
<div class="bg-gray-50 border-b border-gray-200 hidden md:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <nav class="flex" aria-label="Breadcrumb">
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
                            <span class="text-gray-900 font-medium max-w-xs md:max-w-md lg:max-w-lg truncate inline-block" title="{{ $breadcrumb['name'] }}">
                                {{ $breadcrumb['name'] }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
</div>

<!-- Product Details -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Left Column: Product Images & Related Products -->
        <div class="space-y-4">
                @php
                // Combine product and template images FIRST
                    $media = $product->getEffectiveMedia();
                $allImages = [];
                
                // Add product images first
                if($media && count($media) > 0) {
                    foreach($media as $mediaItem) {
                        if (is_string($mediaItem)) {
                            $allImages[] = $mediaItem;
                        } elseif (is_array($mediaItem)) {
                            $mediaUrl = $mediaItem['url'] ?? $mediaItem['path'] ?? reset($mediaItem) ?? null;
                            if ($mediaUrl) {
                                $allImages[] = $mediaUrl;
                            }
                        }
                    }
                }
                
                // Add template images (if different from product images)
                if($product->template && $product->template->media) {
                    $templateMedia = is_array($product->template->media) ? $product->template->media : json_decode($product->template->media, true);
                    $templateMediaUrls = collect($templateMedia)->map(function($item) {
                        if (is_string($item)) {
                            return $item;
                        } elseif (is_array($item)) {
                            return $item['url'] ?? $item['path'] ?? reset($item) ?? null;
                        }
                        return null;
                    })->filter()->toArray();
                    
                    // Only add template images that are not already in product images
                    foreach($templateMediaUrls as $templateUrl) {
                        if(!in_array($templateUrl, $allImages)) {
                            $allImages[] = $templateUrl;
                        }
                    }
                }
            @endphp
            
            <!-- Main Image/Video with Enhanced Effects -->
            <div class="aspect-square bg-white rounded-xl shadow-lg overflow-hidden relative group" id="image-container">
                @if($media && count($media) > 0)
                    @php
                        // Get first media URL safely
                        if (is_string($media[0])) {
                            $firstMediaUrl = $media[0];
                        } elseif (is_array($media[0])) {
                            $firstMediaUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? '';
                        } else {
                            $firstMediaUrl = '';
                        }
                        $isVideo = str_contains($firstMediaUrl, '.mp4') || str_contains($firstMediaUrl, '.mov') || str_contains($firstMediaUrl, '.avi') || str_contains($firstMediaUrl, '.webm');
                    @endphp
                    
                    @if($isVideo)
                        <!-- Video Player -->
                        @php
                            // Get poster image: first image from media array
                            $posterImage = null;
                            foreach($allImages as $mediaItem) {
                                if (!str_contains($mediaItem, '.mp4') && !str_contains($mediaItem, '.mov') && !str_contains($mediaItem, '.avi') && !str_contains($mediaItem, '.webm')) {
                                    $posterImage = $mediaItem;
                                    break;
                                }
                            }
                            // If no image, use template media or generate placeholder
                            if (!$posterImage && $product->template && $product->template->media) {
                                $templateMedia = is_array($product->template->media) ? $product->template->media : json_decode($product->template->media, true);
                                if ($templateMedia && count($templateMedia) > 0) {
                                    foreach($templateMedia as $tmItem) {
                                        // Get URL safely
                                        if (is_string($tmItem)) {
                                            $tmUrl = $tmItem;
                                        } elseif (is_array($tmItem)) {
                                            $tmUrl = $tmItem['url'] ?? $tmItem['path'] ?? reset($tmItem) ?? '';
                                        } else {
                                            $tmUrl = '';
                                        }
                                        if (!str_contains($tmUrl, '.mp4') && !str_contains($tmUrl, '.mov') && !str_contains($tmUrl, '.avi') && !str_contains($tmUrl, '.webm')) {
                                            $posterImage = $tmUrl;
                                            break;
                                        }
                                    }
                                }
                            }
                        @endphp
                        <video id="main-video" 
                               class="w-full h-full object-cover cursor-pointer" 
                               controls 
                               playsinline
                               @if($posterImage)
                               poster="{{ $posterImage }}"
                               @endif>
                            <source src="{{ $firstMediaUrl }}" type="video/mp4">
                            <source src="{{ $firstMediaUrl }}" type="video/webm">
                            Your browser does not support the video tag.
                        </video>
                        
                        <!-- Video Badge -->
                        <div class="absolute top-3 left-3 bg-purple-600 text-white text-xs px-3 py-1 rounded-full font-medium flex items-center space-x-1 pointer-events-none z-10">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
                            </svg>
                            <span>VIDEO</span>
                        </div>
                        
                        <!-- Custom Play Button Overlay (before playing) -->
                        <div id="video-play-overlay" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 transition-opacity cursor-pointer z-20" onclick="playVideoOnClick(event)">
                            <div class="w-20 h-20 bg-white bg-opacity-90 rounded-full flex items-center justify-center shadow-2xl hover:bg-opacity-100 hover:scale-110 transition-all duration-300">
                                <svg class="w-10 h-10 text-purple-600 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                                </svg>
                            </div>
                        </div>
                    @else
                        <!-- Image -->
                        <img src="{{ $firstMediaUrl }}" 
                             alt="{{ $product->name }}" 
                             id="main-image"
                             class="w-full h-full object-cover">
                        
                        <!-- Zoom Overlay (Only for images) -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                                <div class="bg-white bg-opacity-90 rounded-full p-3 shadow-lg zoom-icon">
                                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Media Counter Badge -->
                    @if(!empty($allImages) && count($allImages) > 1)
                        <div class="absolute top-3 right-3 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded-full">
                            <span id="image-counter">1</span> / {{ count($allImages) }}
                        </div>
                    @endif
                    
                    <!-- Loading Spinner -->
                    <div id="image-loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#005366]"></div>
                    </div>
                    
                @else
                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
                
                <!-- Hover Effects -->
                <div class="absolute inset-0 border-2 border-transparent group-hover:border-[#005366] transition-all duration-300 rounded-xl"></div>
            </div>

            
            @if(!empty($allImages) && count($allImages) > 1)
                <!-- Smart Gallery -->
                <div class="space-y-4">
                    <!-- Thumbnail Navigation -->
                    <div class="relative">
                        <div class="flex overflow-x-auto scrollbar-hide space-x-2 pb-2" id="thumbnail-container">
                    @foreach($allImages as $index => $imageUrl)
                                @php
                                    $isThumbVideo = str_contains($imageUrl, '.mp4') || str_contains($imageUrl, '.mov') || str_contains($imageUrl, '.avi') || str_contains($imageUrl, '.webm');
                                @endphp
                                <button onclick="changeMainImage('{{ $imageUrl }}', {{ $index }})" 
                                        class="flex-shrink-0 w-16 h-16 bg-white rounded-lg shadow-sm overflow-hidden border-2 {{ $index === 0 ? 'border-[#005366]' : 'border-gray-200' }} hover:border-[#005366] transition-colors group relative">
                                    @if($isThumbVideo)
                                        <!-- Video Thumbnail -->
                                        <div class="w-full h-full bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
                                            </svg>
                                        </div>
                                        <!-- Video Badge -->
                                        <div class="absolute bottom-0 left-0 right-0 bg-purple-600 bg-opacity-90 text-white text-[8px] text-center py-0.5 font-bold">
                                            VIDEO
                                        </div>
                                    @else
                                        <!-- Image Thumbnail -->
                                        <img src="{{ $imageUrl }}" 
                                             alt="{{ $product->name }} - Media {{ $index + 1 }}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    @endif
                        </button>
                    @endforeach
                        </div>
                        
                        <!-- Navigation Arrows (only show if more than 6 images) -->
                        @if(!empty($allImages) && count($allImages) > 6)
                            <button onclick="scrollThumbnails('left')" 
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-2 border border-gray-200 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button onclick="scrollThumbnails('right')" 
                                    class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white shadow-lg rounded-full p-2 border border-gray-200 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                    
                    <!-- Gallery Actions -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ !empty($allImages) ? count($allImages) : 0 }} {{ !empty($allImages) && count($allImages) === 1 ? 'image' : 'images' }}</span>
                        </div>
                        
                        @if(!empty($allImages) && count($allImages) > 1)
                            <button onclick="openGalleryModal()" 
                                    class="text-sm text-[#005366] hover:underline flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                                <span>View All</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Related Products (Desktop: here, Mobile: below) -->
            @if($relatedProducts->count() > 0)
                <div class="space-y-4 hidden lg:block">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900">Related Products</h3>
                        <a href="{{ route('products.index') }}" class="text-sm text-[#005366] hover:underline">
                            See All Items
                        </a>
                    </div>
                    
                    <!-- Related Products Carousel -->
                    <div class="relative">
                        <!-- Navigation Buttons -->
                        @if($relatedProducts->count() > 3)
                            <button id="relatedPrevBtn" 
                                    onclick="scrollRelatedProducts('prev')"
                                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-50 transition-all opacity-0 group-hover:opacity-100">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            
                            <button id="relatedNextBtn" 
                                    onclick="scrollRelatedProducts('next')"
                                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 z-10 bg-white rounded-full p-2 shadow-lg hover:bg-gray-50 transition-all">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        @endif
                        
                        <!-- Products Container -->
                        <div id="relatedProductsContainer" class="overflow-hidden group">
                            <div id="relatedProductsTrack" class="flex gap-3 transition-transform duration-300">
                                @foreach($relatedProducts->take(12) as $relatedProduct)
                                    <a href="{{ route('products.show', $relatedProduct->slug) }}" 
                                       class="flex-shrink-0 w-40 bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 group/item overflow-hidden border border-gray-200">
                                        <!-- Product Image -->
                                        <div class="relative aspect-square overflow-hidden">
                                            @php
                                                $relatedMedia = $relatedProduct->getEffectiveMedia();
                                                $relatedImageUrl = null;
                                                if ($relatedMedia && count($relatedMedia) > 0) {
                                                    if (is_string($relatedMedia[0])) {
                                                        $relatedImageUrl = $relatedMedia[0];
                                                    } elseif (is_array($relatedMedia[0])) {
                                                        $relatedImageUrl = $relatedMedia[0]['url'] ?? $relatedMedia[0]['path'] ?? reset($relatedMedia[0]) ?? null;
                                                    }
                                                }
                                            @endphp
                                            @if($relatedImageUrl)
                                                <img src="{{ $relatedImageUrl }}" 
                                                     alt="{{ $relatedProduct->name }}" 
                                                     class="w-full h-full object-cover group-hover/item:scale-105 transition-transform duration-300">
                                            @else
                                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Product Info (Compact) -->
                                        <div class="p-2.5">
                                            <h4 class="font-medium text-gray-900 text-xs line-clamp-2 group-hover/item:text-[#005366] transition-colors mb-1.5 h-8 overflow-hidden" title="{{ $relatedProduct->name }}">
                                                {{ Str::limit($relatedProduct->name, 30) }}
                                            </h4>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-bold text-[#E2150C]">${{ number_format($relatedProduct->base_price, 2) }}</span>
                                                <div class="flex items-center text-xs text-gray-500">
                                                    <svg class="w-3 h-3 text-yellow-400 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                    <span class="text-xs">4.5</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Reviews Section (Desktop Only) -->
            <div class="space-y-6 hidden lg:block">
                <!-- Reviews Header -->
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-gray-900">Reviews</h3>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button class="border-b-2 border-[#005366] py-2 px-1 text-sm font-medium text-[#005366]">
                            Reviews for this item
                        </button>
                        <button class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Reviews for this shop
                        </button>
                    </nav>
                </div>

                @php
                    $averageRating = $product->getAverageRating();
                    $totalReviews = $product->getTotalReviews();
                    $ratingBreakdown = $product->getRatingBreakdown();
                @endphp

                <!-- Overall Rating Summary -->
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <!-- Average Rating -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <span class="text-2xl font-bold text-gray-900 ml-2">{{ number_format($averageRating, 1) }} /5.0</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="underline">{{ $totalReviews }} Reviews</span>
                        </div>
                    </div>

                    <!-- Star Rating Distribution -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-2">
                        <!-- Left Column -->
                        <div class="space-y-2">
                            @for($star = 5; $star >= 3; $star--)
                                @php
                                    $count = $ratingBreakdown[$star] ?? 0;
                                    $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                                @endphp
                                <div class="flex items-center space-x-2">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700 ml-1">{{ $star }}</span>
                                    </div>
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[120px]">
                                        <div class="bg-gray-900 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 w-8">({{ $percentage }}%)</span>
                                </div>
                            @endfor
                        </div>
                        
                        <!-- Right Column -->
                        <div class="space-y-2">
                            @for($star = 2; $star >= 1; $star--)
                                @php
                                    $count = $ratingBreakdown[$star] ?? 0;
                                    $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                                @endphp
                                <div class="flex items-center space-x-2">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700 ml-1">{{ $star }}</span>
                                    </div>
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[120px]">
                                        <div class="bg-gray-900 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 w-8">({{ $percentage }}%)</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- Individual Reviews -->
                @if($product->approvedReviews->count() > 0)
                    <div class="space-y-6">
                        @foreach($product->approvedReviews->take(3) as $review)
                            <div class="border-t border-dotted border-gray-300 pt-6">
                                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between space-y-4 lg:space-y-0">
                                    <!-- Review Content -->
                                    <div class="flex-1">
                                        <!-- Rating -->
                                        <div class="flex items-center mb-3">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= floor($review->rating))
                                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                @elseif($i - 0.5 <= $review->rating)
                                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <defs>
                                                            <linearGradient id="half-star-{{ $review->id }}-{{ $i }}">
                                                                <stop offset="50%" stop-color="currentColor"/>
                                                                <stop offset="50%" stop-color="#E5E7EB"/>
                                                            </linearGradient>
                                                        </defs>
                                                        <path fill="url(#half-star-{{ $review->id }}-{{ $i }})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                @endif
                                            @endfor
                                        </div>

                                        <!-- Review Title and Text -->
                                        @if($review->review_text)
                                            @php
                                                $words = explode(' ', $review->review_text);
                                                $title = count($words) > 8 ? implode(' ', array_slice($words, 0, 8)) . '...' : $review->review_text;
                                            @endphp
                                            <h4 class="font-semibold text-gray-900 mb-2">{{ $title }}</h4>
                                            <p class="text-gray-700 text-sm leading-relaxed">{{ $review->review_text }}</p>
                                        @endif
                                    </div>

                                    <!-- Reviewer Info -->
                                    <div class="flex items-center space-x-3 lg:ml-6">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-medium text-sm">
                                                {{ Str::upper(substr($review->display_name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium text-gray-900">{{ $review->display_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $review->created_at->format('D M d Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg mb-2">No reviews yet</p>
                        <p class="text-gray-400 text-sm">Be the first to review this product</p>
                    </div>
                @endif

                <!-- Write Review and Pagination -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 pt-6 border-t border-gray-200">
                    <!-- Write Review Button -->
                    <button class="inline-flex items-center px-4 py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-50 transition-colors">
                        <svg class="w-4 h-4 mr-2 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Write your review
                    </button>

                    <!-- Pagination -->
                    @if($product->approvedReviews->count() > 3)
                        <div class="flex items-center space-x-2">
                            <button class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <span class="text-sm text-gray-600 px-3">1/3</span>
                            <button class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="space-y-6">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>
                
                <!-- Seller Info -->
                <div class="flex items-center space-x-3 mb-4">
                    <span class="text-gray-600">Sold by:</span>
                    <a href="{{ route('shops.show', $product->shop->shop_slug ?? '') }}" class="text-[#005366] hover:underline font-medium">
                        {{ $product->shop->name ?? 'Unknown Shop' }}
                    </a>
                    @if($product->shop && $product->shop->verified)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Verified
                        </span>
                    @endif
                </div>

            </div>

            <!-- Price -->
            <div class="bg-gray-50 rounded-xl p-6">
                <div class="mb-4">
                    <div class="flex items-center space-x-4 mb-2">
                    @if($product->template && $product->template->base_price > $product->price)
                        <span class="text-2xl text-gray-500 line-through">${{ number_format($product->template->base_price, 2) }}</span>
                            <span class="text-4xl font-bold text-[#E2150C]" id="base-price" data-price="{{ $product->price }}">${{ number_format($product->price, 2) }}</span>
                        @php
                            $discount = round((($product->template->base_price - $product->price) / $product->template->base_price) * 100);
                        @endphp
                        <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            {{ $discount }}% OFF
                        </span>
                    @else
                            <span class="text-4xl font-bold text-[#E2150C]" id="base-price" data-price="{{ $product->base_price }}">${{ number_format($product->base_price, 2) }}</span>
                    @endif
                    </div>
                    
                    <!-- Customization Price Display -->
                    <div id="customization-price-display" class="hidden mt-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Customization:</span>
                            <span class="text-[#005366] font-medium" id="customization-price">+$0.00</span>
                        </div>
                        <div class="border-t border-gray-300 mt-2 pt-2 flex items-center justify-between">
                            <span class="text-gray-900 font-semibold">Total:</span>
                            <span class="text-2xl font-bold text-[#005366]" id="total-price">${{ number_format($product->base_price, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                @if($product->template && $product->template->base_price > $product->price)
                    <p class="text-sm text-red-600 font-medium mb-4">
                        Sale ends at {{ now()->addDays(7)->format('F d, Y') }}
                    </p>
                @endif

                <!-- FREE Returns Section -->
                <div class="flex items-center space-x-2 mb-4">
                    <span class="text-sm font-medium text-gray-900">FREE Returns</span>
                    <div class="relative inline-block">
                        <button onclick="toggleReturnsInfo()" class="w-4 h-4 bg-blue-500 text-white rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors">
                            <span class="text-xs font-bold">i</span>
                        </button>
                        
                        <!-- Returns Info Popup -->
                        <div id="returns-info-popup" class="absolute bottom-6 left-1/2 transform -translate-x-1/2 bg-white border border-gray-200 rounded-lg shadow-lg p-4 w-72 z-50 hidden">
                            <div class="relative">
                                <p class="text-sm text-gray-700 mb-2">
                                    Free returns are available for the shipping address you chose. You can return the item for any reason in new and unused condition: no return shipping charges.
                                </p>
                                <a href="{{ route('page.show', 'returns-exchanges-policy') }}" class="text-sm text-red-600 hover:underline">
                                    Read the full returns policy
                                </a>
                                
                                <!-- Arrow pointing down -->
                                <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-white border-r border-b border-gray-200 rotate-45"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customization Options -->
                @if($product->template && $product->template->hasCustomization())
                    <div class="space-y-4 mb-6">
                        <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Personalization Options</h3>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" 
                                       id="enable-customization" 
                                       onchange="toggleCustomization()"
                                       class="h-5 w-5 text-[#005366] focus:ring-[#005366] border-gray-300 rounded">
                                <span class="text-sm font-medium text-gray-700">Add Personalization</span>
                            </label>
                        </div>
                        
                        <div id="customization-container" class="hidden space-y-4">
                        @foreach($product->template->getCustomizationTypes() as $index => $customization)
                            <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="font-medium text-gray-900 text-base">
                                        {{ $customization['label'] ?? $customization['name'] ?? 'Customization ' . ($index + 1) }}
                                    </h4>
                                    <div class="flex items-center space-x-2">
                                        @if(isset($customization['required']) && $customization['required'])
                                            <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full font-medium">Required</span>
                                        @endif
                                        @if(isset($customization['price']) && $customization['price'] > 0)
                                            <span class="text-sm font-medium text-[#E2150C]">+${{ number_format($customization['price'], 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if(isset($customization['description']) && !empty($customization['description']))
                                    <p class="text-sm text-gray-600 mb-3">{{ strip_tags(html_entity_decode($customization['description'], ENT_QUOTES, 'UTF-8')) }}</p>
                                @endif
                                
                                @if(isset($customization['instructions']) && !empty($customization['instructions']))
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                        <div class="flex items-start space-x-2">
                                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-xs text-blue-800">{{ strip_tags(html_entity_decode($customization['instructions'], ENT_QUOTES, 'UTF-8')) }}</p>
                                        </div>
                                    </div>
                                @endif
                                
                                @if(isset($customization['options']) && is_array($customization['options']) && count($customization['options']) > 0)
                                    <div class="space-y-3">
                                        <p class="text-sm text-gray-700 font-medium">Choose an option:</p>
                                        @foreach($customization['options'] as $optionIndex => $option)
                                            <label class="flex items-center space-x-3 cursor-pointer p-3 hover:bg-gray-50 rounded-lg border border-gray-100">
                                                <input type="radio" 
                                                       name="customization_{{ $index }}_{{ $customization['type'] ?? 'option' }}" 
                                                       value="{{ $option['value'] ?? $option['label'] ?? $option }}" 
                                                       data-price="{{ $option['price'] ?? 0 }}"
                                                       onchange="updateCustomizationPrice()"
                                                       {{ (isset($customization['required']) && $customization['required']) ? 'required' : '' }}
                                                       class="customization-input text-[#005366] focus:ring-[#005366] w-4 h-4">
                                                <div class="flex-1">
                                                    <span class="text-sm text-gray-700 font-medium">{{ $option['label'] ?? $option['value'] ?? $option }}</span>
                                                    @if(isset($option['description']) && !empty($option['description']))
                                                        <p class="text-xs text-gray-500 mt-1">{{ strip_tags(html_entity_decode($option['description'], ENT_QUOTES, 'UTF-8')) }}</p>
                                                    @endif
                                                </div>
                                                @if(isset($option['price']) && $option['price'] > 0)
                                                    <span class="text-xs text-[#E2150C] font-medium">+${{ number_format($option['price'], 2) }}</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif(isset($customization['input_type']) && $customization['input_type'] === 'textarea')
                                    <div class="space-y-3">
                                        <textarea name="customization_{{ $index }}_{{ $customization['type'] ?? 'customization' }}"
                                                  placeholder="{{ $customization['placeholder'] ?? 'Enter your text here...' }}" 
                                                  rows="{{ $customization['rows'] ?? 3 }}"
                                                  data-price="{{ $customization['price'] ?? 0 }}"
                                                  data-label="{{ $customization['label'] ?? $customization['type'] ?? 'Customization' }}"
                                                  oninput="updateCustomizationPrice()"
                                                  {{ (isset($customization['required']) && $customization['required']) ? 'required' : '' }}
                                                  class="customization-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent resize-vertical">{{ old('customization_' . $index) }}</textarea>
                                        @if(isset($customization['max_length']))
                                            <p class="text-xs text-gray-500">Maximum {{ $customization['max_length'] }} characters</p>
                                        @endif
                                    </div>
                                @else
                                    <div class="space-y-3">
                                        <input type="{{ $customization['input_type'] ?? 'text' }}" 
                                               name="customization_{{ $index }}_{{ $customization['type'] ?? 'customization' }}"
                                               placeholder="{{ $customization['placeholder'] ?? 'Enter ' . ($customization['type'] ?? 'customization') . '...' }}" 
                                               value="{{ old('customization_' . $index) }}"
                                               data-price="{{ $customization['price'] ?? 0 }}"
                                               data-label="{{ $customization['label'] ?? $customization['type'] ?? 'Customization' }}"
                                               oninput="updateCustomizationPrice()"
                                               {{ (isset($customization['required']) && $customization['required']) ? 'required' : '' }}
                                               class="customization-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                                        @if(isset($customization['max_length']))
                                            <p class="text-xs text-gray-500">Maximum {{ $customization['max_length'] }} characters</p>
                                        @endif
                                        @if(isset($customization['help_text']) && !empty($customization['help_text']))
                                            <p class="text-xs text-gray-500">{{ $customization['help_text'] }}</p>
                                        @endif
                                    </div>
                                @endif
                                
                                @if(isset($customization['file_upload']) && $customization['file_upload'])
                                    <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <label class="block text-sm text-gray-700 font-medium mb-2">
                                            Upload your file:
                                        </label>
                                        <input type="file" 
                                               name="customization_file_{{ $index }}"
                                               accept="{{ $customization['accepted_formats'] ?? 'image/*' }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                                        @if(isset($customization['file_requirements']))
                                            <p class="text-xs text-gray-500 mt-1">{{ $customization['file_requirements'] }}</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        </div>
                    </div>
                @endif

                <!-- Product Options -->
                @if($product->variants()->count() > 0)
                    @php
                        $variants = $product->variants;
                        $selectedVariant = $variants->first();
                        
                        // Extract unique attribute values dynamically
                        $allAttributes = [];
                        foreach($variants as $variant) {
                            if($variant->attributes) {
                                foreach($variant->attributes as $key => $value) {
                                    if(!isset($allAttributes[$key])) {
                                        $allAttributes[$key] = [];
                                    }
                                    if(!in_array($value, $allAttributes[$key])) {
                                        $allAttributes[$key][] = $value;
                                    }
                                }
                            }
                        }
                        
                        // For backward compatibility, check for Size and Color specifically
                        $sizes = collect($allAttributes['Size'] ?? []);
                        $colors = collect($allAttributes['Color'] ?? []);
                    @endphp
                    
                    <div class="space-y-6 mb-6">
                        @foreach($allAttributes as $attributeName => $attributeValues)
                            @if($attributeName === 'Size')
                                <!-- Size Selection -->
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-lg font-semibold text-gray-900">Size</h3>
                                        <button onclick="openSizeGuide()" class="text-sm text-[#005366] hover:underline flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            View Size Guide
                                        </button>
                                    </div>
                                    <div class="relative">
                                        <select id="{{ strtolower($attributeName) }}-selector" onchange="selectAttribute('{{ $attributeName }}', this.value)" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent appearance-none bg-white">
                                            <option value="">Choose a {{ strtolower($attributeName) }}</option>
                                            @foreach($attributeValues as $value)
                                                <option value="{{ $value }}" {{ $loop->first ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            @elseif($attributeName === 'Color' || $attributeName === 'Colour')
                                <!-- Color Selection -->
                                @php
                                    $colorMap = [
                                        // Basic Colors
                                        'black' => '#000000', 'white' => '#ffffff', 'red' => '#dc2626',
                                        'blue' => '#2563eb', 'green' => '#16a34a', 'yellow' => '#eab308',
                                        'purple' => '#9333ea', 'pink' => '#ec4899', 'orange' => '#ea580c',
                                        'brown' => '#a16207', 'gray' => '#6b7280', 'grey' => '#6b7280',
                                        
                                        // Extended Colors
                                        'navy' => '#1e3a8a', 'maroon' => '#991b1b', 'teal' => '#0d9488',
                                        'lime' => '#65a30d', 'cyan' => '#06b6d4', 'indigo' => '#4f46e5',
                                        'violet' => '#8b5cf6', 'rose' => '#f43f5e', 'amber' => '#f59e0b',
                                        'emerald' => '#10b981', 'sky' => '#0ea5e9', 'fuchsia' => '#d946ef',
                                        
                                        // Dark Variants
                                        'dark chocolate' => '#3c2415', 'dark gray' => '#374151', 
                                        'charcoal' => '#374151', 'dark blue' => '#1e40af',
                                        'dark green' => '#166534', 'dark red' => '#991b1b',
                                        
                                        // Light Variants
                                        'light gray' => '#9ca3af', 'light blue' => '#93c5fd',
                                        'light green' => '#86efac', 'light pink' => '#fbb6ce',
                                        'light yellow' => '#fef3c7', 'cream' => '#fef7cd',
                                        
                                        // Special Colors
                                        'gold' => '#fbbf24', 'silver' => '#9ca3af', 'copper' => '#b45309',
                                        'bronze' => '#92400e', 'platinum' => '#6b7280',
                                        
                                        // Pattern Colors
                                        'camo' => '#365314', 'olive' => '#65a30d', 'khaki' => '#a3a3a3',
                                        'beige' => '#f5f5dc', 'tan' => '#d2b48c', 'mint' => '#a7f3d0',
                                        'lavender' => '#e9d5ff', 'coral' => '#fda4af', 'turquoise' => '#5eead4',
                                        
                                        // Additional Colors from UI
                                        'sport grey' => '#9ca3af', 'dark heather' => '#374151',
                                        'royal blue' => '#1d4ed8', 'sand' => '#fbbf24',
                                        'forest green' => '#166534', 'military green' => '#365314',
                                        'ash grey' => '#6b7280', 'natural' => '#fef3c7',
                                        
                                        // Complete color set from user request (case insensitive)
                                        'black' => '#000000', 'white' => '#ffffff',
                                        'light blue' => '#93c5fd', 'charcoal' => '#374151',
                                        'sport grey' => '#9ca3af', 'dark heather' => '#374151',
                                        'navy' => '#1e3a8a', 'maroon' => '#991b1b',
                                        'light pink' => '#fbb6ce', 'red' => '#dc2626',
                                        'royal blue' => '#1d4ed8', 'sand' => '#fbbf24',
                                        'forest green' => '#166534', 'military green' => '#365314',
                                        'ash grey' => '#6b7280', 'purple' => '#9333ea',
                                        'orange' => '#ea580c', 'natural' => '#fef3c7',
                                    ];
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $attributeName }}: <span id="selected-color-name" class="text-[#005366]">{{ $attributeValues[0] ?? '' }}</span>
                                        </h3>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($attributeValues as $color)
                                            @php
                                                $colorCode = $colorMap[strtolower($color)] ?? '#6b7280';
                                            @endphp
                                            <button onclick="selectAttribute('{{ $attributeName }}', '{{ $color }}')" 
                                                    class="color-swatch variant-option w-10 h-10 rounded-full border-2 {{ $loop->first ? 'border-[#005366] ring-2 ring-[#005366] ring-offset-1 scale-105' : 'border-gray-300 hover:border-gray-400 hover:scale-105' }} transition-all duration-200 relative shadow-sm hover:shadow-md group"
                                                    data-attribute="{{ $attributeName }}"
                                                    data-value="{{ $color }}"
                                                    style="background: {{ $colorCode }}; background-image: linear-gradient(45deg, {{ $colorCode }}cc, {{ $colorCode }});"
                                                    title="{{ $color }}">
                                                
                                                <!-- Gradient overlay for special colors -->
                                                @if(in_array(strtolower($color), ['gold', 'silver', 'copper', 'bronze', 'platinum']))
                                                    <div class="absolute inset-0 rounded-full bg-gradient-to-br from-white/20 to-transparent"></div>
                                                @elseif(in_array(strtolower($color), ['white', 'cream', 'light yellow']))
                                                    <div class="absolute inset-0 rounded-full border border-gray-200"></div>
                                                @endif
                                                
                                                <!-- Checkmark for selected color -->
                                                @if($loop->first)
                                                    <svg class="w-4 h-4 text-white absolute inset-0 m-auto drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                                
                                                <!-- Color name tooltip on hover -->
                                                <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                                    {{ $color }}
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <!-- Other Attributes -->
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ $attributeName }}</h3>
                                    <div class="flex flex-wrap gap-3">
                                        @foreach($attributeValues as $value)
                                            <button onclick="selectAttribute('{{ $attributeName }}', '{{ $value }}')" 
                                                    class="attribute-option px-4 py-2 border-2 border-gray-300 rounded-lg hover:border-[#005366] transition-colors {{ $loop->first ? 'border-[#005366] bg-[#005366] text-white' : '' }}"
                                                    data-attribute="{{ $attributeName }}"
                                                    data-value="{{ $value }}">
                                                {{ $value }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <!-- Selected Variant Summary -->
                        <div id="selected-variant-summary" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900">
                                        <span id="selected-variant-name">
                                            @if($selectedVariant->variant_name)
                                                {{ $selectedVariant->variant_name }}
                                            @elseif($selectedVariant->attributes)
                                                @php
                                                    $attrParts = [];
                                                    foreach($selectedVariant->attributes as $key => $value) {
                                                        $attrParts[] = $value;
                                                    }
                                                    echo implode(' - ', $attrParts);
                                                @endphp
                                            @else
                                                Standard
                                            @endif
                                        </span>
                                    </h4>
                                    @if($selectedVariant->quantity !== null)
                                        <p class="text-xs text-gray-500 mt-1" id="selected-variant-stock">
                                            Stock: {{ $selectedVariant->quantity > 0 ? $selectedVariant->quantity . ' available' : 'Out of stock' }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-lg font-bold text-[#E2150C]" id="selected-variant-price">
                                        ${{ number_format($selectedVariant->price ?? $product->base_price, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden data for JavaScript -->
                    <script type="application/json" id="variants-data">
                        {!! $variants->map(function($variant) use ($product) {
                            return [
                                'id' => $variant->id,
                                'attributes' => $variant->attributes ?? [],
                                'size' => $variant->attributes['Size'] ?? null,
                                'color' => $variant->attributes['Color'] ?? null,
                                'colour' => $variant->attributes['Colour'] ?? null,
                                'price' => $variant->price ?? $product->base_price,
                                'quantity' => $variant->quantity,
                                'variant_name' => $variant->variant_name,
                                'media' => $variant->media
                            ];
                        })->values()->toJson() !!}
                    </script>
                @endif

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <button id="add-to-cart-btn" 
                            onclick="addToCart()"
                            class="flex-1 bg-[#005366] hover:bg-[#003d4d] text-white font-bold py-4 px-6 rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="cart-icon" class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        <span id="cart-text">Add to Cart</span>
                        <div id="cart-loading" class="hidden">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                        </div>
                    </button>
                    <button onclick="buyNow()" class="flex-1 bg-[#E2150C] hover:bg-[#c0120a] text-white font-bold py-4 px-6 rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2">
                        <span>Buy Now</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </div>

                <!-- Wishlist Button -->
                <div class="w-full mt-4">
                    <x-wishlist-button :product="$product" size="lg" :showText="true" />
                </div>

                <!-- Guarantee & Delivery Info Section -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mt-6">
                    <!-- Printerval Guarantee -->
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Bluprinter Guarantee</h4>
                            <p class="text-sm text-gray-600">Don't love it? We'll fix it. For free.</p>
                        </div>
                    </div>

                    <!-- Delivery Info -->
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900" id="delivery-location">Deliver to <span id="customer-location">Loading...</span></h4>
                            <p class="text-sm text-gray-600" id="delivery-estimate">Calculating delivery time...</p>
                            <p class="text-sm text-gray-600" id="ready-to-ship">Ready to ship in: 2 business days</p>
                        </div>
                    </div>
                </div>

                <!-- Designer & Policies Section -->
                <div class="space-y-6 mt-6">
                    <!-- Designed by -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-gray-700">Designed by</span>
                            <a href="{{ route('shops.show', $product->shop->shop_slug ?? '') }}" class="text-[#005366] hover:underline font-medium flex items-center space-x-1">
                                <span>{{ $product->shop->name ?? 'Bluprinter Team' }}</span>
                                <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Policies -->
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-1">Policies</h4>
                            <p class="text-sm text-gray-600">
                                Eligible for <a href="{{ route('page.show', 'returns-exchanges-policy') }}" class="text-orange-600 font-medium hover:underline">Refund</a> or <a href="{{ route('page.show', 'returns-exchanges-policy') }}" class="text-orange-600 font-medium hover:underline">Return and Replacement</a> within 30 days from the date of delivery
                            </p>
                        </div>
                    </div>

                    <!-- Need Support -->
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Need Support?</h4>
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('page.show', 'contact-us') }}" class="text-[#005366] hover:underline text-sm">Submit a ticket</a>
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('page.show', 'contact-us') }}" class="text-[#005366] hover:underline text-sm">Report Product</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="space-y-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Product Description</h3>
                    @php
                        $description = $product->description ?? 'No description available for this product.';
                        
                        // Convert HTML to text
                        $description = strip_tags($description);
                        
                        // Decode HTML entities
                        $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
                        
                        // Replace &nbsp; with regular spaces
                        $description = str_replace('&nbsp;', ' ', $description);
                        
                        // Replace multiple hyphens with section breaks
                        $description = preg_replace('/-{3,}/', "\n\n", $description);
                        
                        // Clean up extra spaces and line breaks
                        $description = preg_replace('/\s+/', ' ', $description);
                        $description = preg_replace('/\n\s*\n/', "\n\n", $description);
                        $description = trim($description);
                        
                        // Calculate preview length (approximately 4 lines = ~300 characters)
                        $previewLength = 300;
                        $hasMoreContent = strlen($description) > $previewLength;
                        
                        if ($hasMoreContent) {
                            // Find a good break point (end of sentence or word)
                            $previewText = substr($description, 0, $previewLength);
                            $lastPeriod = strrpos($previewText, '.');
                            $lastSpace = strrpos($previewText, ' ');
                            
                            if ($lastPeriod !== false && $lastPeriod > $previewLength * 0.7) {
                                $previewText = substr($previewText, 0, $lastPeriod + 1);
                            } elseif ($lastSpace !== false) {
                                $previewText = substr($previewText, 0, $lastSpace) . '...';
                            } else {
                                $previewText .= '...';
                            }
                        } else {
                            $previewText = $description;
                        }
                        
                        // Split by double line breaks to create sections for full display
                        $sections = array_filter(array_map('trim', explode("\n\n", $description)));
                    @endphp
                    
                    <!-- Description Preview (Always Visible) -->
                    <div id="description-preview" class="text-gray-700 leading-relaxed whitespace-pre-line">
                        {{ $previewText }}
                </div>

                    <!-- Full Description (Hidden by default) -->
                    <div id="description-full" class="hidden text-gray-700 leading-relaxed space-y-4">
                        @foreach($sections as $index => $section)
                            @if(strpos($section, ':') !== false)
                                @php
                                    list($title, $content) = explode(':', $section, 2);
                                    $title = trim($title);
                                    $content = trim($content);
                                @endphp
                                <div class="border-l-4 border-[#005366] pl-4 py-2">
                                    <h4 class="font-semibold text-gray-900 mb-1">{{ $title }}</h4>
                                    <p class="text-gray-700">{{ $content }}</p>
                                </div>
                            @else
                                <p class="text-gray-700">{{ $section }}</p>
                            @endif
                        @endforeach
                    </div>
                    
                    <!-- Show More/Less Button -->
                    @if($hasMoreContent)
                        <button id="toggle-description" onclick="toggleDescription()" class="mt-4 text-[#005366] hover:underline font-medium flex items-center space-x-2">
                            <span id="description-toggle-text">Show More</span>
                            <svg id="description-toggle-icon" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    @endif
                </div>


            </div>
        </div>
    </div>
</div>

<!-- Related Products (Mobile Only) -->
@if($relatedProducts->count() > 0)
<div class="lg:hidden bg-gray-50 py-8 border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">Related Products</h3>
                <a href="{{ route('products.index') }}" class="text-sm text-[#005366] hover:underline">
                    See All Items
                </a>
            </div>
            
            <!-- Related Products Scroll Container -->
            <div class="overflow-x-auto mobile-scroll-hide" style="scroll-behavior: smooth;">
                <div class="flex gap-3 pb-2">
                        @foreach($relatedProducts->take(12) as $relatedProduct)
                            <a href="{{ route('products.show', $relatedProduct->slug) }}" 
                               class="flex-shrink-0 w-40 bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 group/item overflow-hidden border border-gray-200">
                    <!-- Product Image -->
                    <div class="relative aspect-square overflow-hidden">
                        @php
                            $relatedMedia = $relatedProduct->getEffectiveMedia();
                            $relatedImageUrl = null;
                            if ($relatedMedia && count($relatedMedia) > 0) {
                                if (is_string($relatedMedia[0])) {
                                    $relatedImageUrl = $relatedMedia[0];
                                } elseif (is_array($relatedMedia[0])) {
                                    $relatedImageUrl = $relatedMedia[0]['url'] ?? $relatedMedia[0]['path'] ?? reset($relatedMedia[0]) ?? null;
                                }
                            }
                        @endphp
                        @if($relatedImageUrl)
                            <img src="{{ $relatedImageUrl }}" 
                                 alt="{{ $relatedProduct->name }}" 
                                            class="w-full h-full object-cover group-hover/item:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                                <!-- Product Info (Compact) -->
                                <div class="p-2.5">
                                    <h4 class="font-medium text-gray-900 text-xs line-clamp-2 group-hover/item:text-[#005366] transition-colors mb-1.5 h-8 overflow-hidden" title="{{ $relatedProduct->name }}">
                                        {{ Str::limit($relatedProduct->name, 30) }}
                                    </h4>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-[#E2150C]">${{ number_format($relatedProduct->base_price, 2) }}</span>
                                        <div class="flex items-center text-xs text-gray-500">
                                            <svg class="w-3 h-3 text-yellow-400 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                            <span class="text-xs">4.5</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Reviews Section (Mobile & Tablet Only) -->
<div class="lg:hidden bg-white py-8 border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <!-- Reviews Header -->
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">Reviews</h3>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button class="border-b-2 border-[#005366] py-2 px-1 text-sm font-medium text-[#005366]">
                        Reviews for this item
                    </button>
                    <button class="border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Reviews for this shop
                    </button>
                </nav>
            </div>

            @php
                $averageRating = $product->getAverageRating();
                $totalReviews = $product->getTotalReviews();
                $ratingBreakdown = $product->getRatingBreakdown();
            @endphp

            <!-- Overall Rating Summary -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <!-- Average Rating -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <span class="text-2xl font-bold text-gray-900 ml-2">{{ number_format($averageRating, 1) }} /5.0</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="underline">{{ $totalReviews }} Reviews</span>
                    </div>
                </div>

                <!-- Star Rating Distribution -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-2">
                    <!-- Left Column -->
                    <div class="space-y-2">
                        @for($star = 5; $star >= 3; $star--)
                            @php
                                $count = $ratingBreakdown[$star] ?? 0;
                                $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                            @endphp
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 ml-1">{{ $star }}</span>
                                </div>
                                <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[120px]">
                                    <div class="bg-gray-900 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-8">({{ $percentage }}%)</span>
                            </div>
                        @endfor
                    </div>
                    
                    <!-- Right Column -->
                    <div class="space-y-2">
                        @for($star = 2; $star >= 1; $star--)
                            @php
                                $count = $ratingBreakdown[$star] ?? 0;
                                $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                            @endphp
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 ml-1">{{ $star }}</span>
                                </div>
                                <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[120px]">
                                    <div class="bg-gray-900 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-8">({{ $percentage }}%)</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Individual Reviews -->
            @if($product->approvedReviews->count() > 0)
                <div class="space-y-6">
                    @foreach($product->approvedReviews->take(3) as $review)
                        <div class="border-t border-dotted border-gray-300 pt-6">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between space-y-4 lg:space-y-0">
                                <!-- Review Content -->
                                <div class="flex-1">
                                    <!-- Rating -->
                                    <div class="flex items-center mb-3">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($review->rating))
                                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @elseif($i - 0.5 <= $review->rating)
                                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <defs>
                                                        <linearGradient id="half-star-mobile-{{ $review->id }}-{{ $i }}">
                                                            <stop offset="50%" stop-color="currentColor"/>
                                                            <stop offset="50%" stop-color="#E5E7EB"/>
                                                        </linearGradient>
                                                    </defs>
                                                    <path fill="url(#half-star-mobile-{{ $review->id }}-{{ $i }})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endif
                                        @endfor
                                    </div>

                                    <!-- Review Title and Text -->
                                    @if($review->review_text)
                                        @php
                                            $words = explode(' ', $review->review_text);
                                            $title = count($words) > 8 ? implode(' ', array_slice($words, 0, 8)) . '...' : $review->review_text;
                                        @endphp
                                        <h4 class="font-semibold text-gray-900 mb-2">{{ $title }}</h4>
                                        <p class="text-gray-700 text-sm leading-relaxed">{{ $review->review_text }}</p>
                                    @endif
                                </div>

                                <!-- Reviewer Info -->
                                <div class="flex items-center space-x-3 lg:ml-6">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-medium text-sm">
                                            {{ Str::upper(substr($review->display_name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900">{{ $review->display_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $review->created_at->format('D M d Y') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                    <p class="text-gray-500 text-lg mb-2">No reviews yet</p>
                    <p class="text-gray-400 text-sm">Be the first to review this product</p>
                </div>
            @endif

            <!-- Write Review and Pagination -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 pt-6 border-t border-gray-200">
                <!-- Write Review Button -->
                <button class="inline-flex items-center px-4 py-2 border border-orange-500 text-orange-500 rounded-lg hover:bg-orange-50 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                    Write your review
                </button>

                <!-- Pagination -->
                @if($product->approvedReviews->count() > 3)
                    <div class="flex items-center space-x-2">
                        <button class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <span class="text-sm text-gray-600 px-3">1/3</span>
                        <button class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recently Viewed Products -->
<div class="bg-gray-50 py-8 border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">Recently Viewed</h3>
                <a href="{{ route('products.index') }}" class="text-sm text-[#005366] hover:underline">
                    See All Items
                </a>
            </div>
            
            <!-- Recently Viewed Container -->
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

<!-- Gallery Modal -->
<div id="gallery-modal" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 hidden">
    <div class="relative w-full h-full max-w-7xl mx-auto p-4">
        <!-- Close Button -->
        <button onclick="closeGalleryModal()" 
                class="absolute top-4 right-4 z-10 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-70 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        <!-- Main Media (Image or Video) -->
        <div class="flex items-center justify-center h-full">
            <!-- Image -->
            <img id="modal-main-image" src="" alt="" class="max-w-full max-h-full object-contain">
            
            <!-- Video Player -->
            <video id="modal-main-video" 
                   class="max-w-full max-h-full object-contain hidden" 
                   controls 
                   playsinline
                   controlsList="nodownload">
                <source id="modal-video-source" src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <!-- Media Type Badge -->
        <div id="modal-media-badge" class="absolute top-4 left-1/2 transform -translate-x-1/2 bg-purple-600 text-white text-xs px-3 py-1 rounded-full font-medium items-center space-x-1 hidden">
            <svg class="w-3 h-3 inline" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
            </svg>
            <span class="inline">VIDEO</span>
        </div>
        
        <!-- Navigation -->
        <button onclick="previousImage()" 
                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-3 hover:bg-opacity-70 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button onclick="nextImage()" 
                class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white rounded-full p-3 hover:bg-opacity-70 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
        </button>
        
        <!-- Thumbnail Strip -->
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2">
            <div class="flex space-x-2 bg-black bg-opacity-50 rounded-lg p-2">
                @foreach($allImages as $index => $imageUrl)
                    @php
                        $isModalThumbVideo = str_contains($imageUrl, '.mp4') || str_contains($imageUrl, '.mov') || str_contains($imageUrl, '.avi') || str_contains($imageUrl, '.webm');
                    @endphp
                    <button onclick="selectModalImage('{{ $imageUrl }}', {{ $index }})" 
                            class="w-12 h-12 rounded overflow-hidden border-2 border-transparent hover:border-white transition-colors relative">
                        @if($isModalThumbVideo)
                            <div class="w-full h-full bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
                                </svg>
                            </div>
                        @else
                            <img src="{{ $imageUrl }}" alt="" class="w-full h-full object-cover">
                        @endif
                    </button>
                @endforeach
        </div>
    </div>
        
        <!-- Media Counter -->
        <div class="absolute top-4 left-4 bg-black bg-opacity-50 text-white px-3 py-1 rounded-full text-sm">
            <span id="modal-image-counter">1</span> / {{ !empty($allImages) ? count($allImages) : 0 }}
</div>
    </div>
</div>

<!-- Enhanced Color Picker -->
<script src="{{ asset('js/color-picker.js') }}"></script>

<style>
/* Hide scrollbar for thumbnail container */
.scrollbar-hide {
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar { 
    display: none;  /* Safari and Chrome */
}

/* Mobile scrollbar hiding for Related Products and Recently Viewed */
@media (max-width: 1023px) {
    .mobile-scroll-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .mobile-scroll-hide::-webkit-scrollbar {
        display: none;
    }
}

/* Cart Popup Animations */
.cart-popup-enter {
    animation: cartPopupEnter 0.3s ease-out;
}

@keyframes cartPopupEnter {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.cart-popup-exit {
    animation: cartPopupExit 0.2s ease-in;
}

@keyframes cartPopupExit {
    from {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
    to {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
}

/* Cross-sell product hover effects */
.cross-sell-product {
    transition: all 0.2s ease;
}

.cross-sell-product:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Responsive popup */
@media (max-width: 640px) {
    .cart-popup-content {
        margin: 0.5rem;
        max-height: calc(100vh - 1rem);
        max-width: calc(100vw - 1rem);
    }
    
    .cart-popup-content .grid-cols-2 {
        grid-template-columns: 1fr;
    }
    
    .cart-popup-content .flex.space-x-3 {
        flex-direction: column;
        space-x: 0;
        gap: 0.75rem;
    }
}

/* Smooth scrolling */
#thumbnail-container {
    scroll-behavior: smooth;
}

/* Gallery modal animations */
#gallery-modal {
    transition: opacity 0.3s ease-in-out;
}

#gallery-modal.hidden {
    opacity: 0;
    pointer-events: none;
}

/* Main Image Effects */
#main-image {
    transition: transform 0.3s ease-out, opacity 0.15s ease-in-out;
    cursor: zoom-in;
}

/* Hover Effects - Disabled for zoom effect */
.aspect-square:hover #main-image {
    /* Scale handled by JavaScript for zoom effect */
}

/* Zoom Icon Animation */
.group:hover .zoom-icon {
    animation: zoomPulse 1.5s infinite;
}

@keyframes zoomPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Image Counter Badge */
#image-counter {
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Loading Spinner */
#image-loading {
    backdrop-filter: blur(2px);
}

/* Smooth Image Transitions */
.image-fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

/* Hover Border Effect */
.group:hover .hover-border {
    border-color: #005366;
    box-shadow: 0 0 0 3px rgba(0, 83, 102, 0.1);
}

/* Thumbnail Hover Effects */
#thumbnail-container button:hover img {
    transform: scale(1.1);
    filter: brightness(1.1);
}

/* Gallery Modal Enhancements */
#gallery-modal img {
    transition: all 0.3s ease-in-out;
}

#gallery-modal:hover img {
    transform: scale(1.02);
}

/* Responsive Image Effects */
@media (max-width: 768px) {
    .group:hover #main-image {
        transform: scale(1.02);
    }
}

/* Returns Info Popup */
#returns-info-popup {
    animation: fadeInScale 0.2s ease-out;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: translateX(-50%) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) scale(1);
    }
}

/* Close popup when clicking outside */
.returns-popup-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 40;
}

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
#popupShippingCountry,
#productShippingCountry {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: none !important;
}

/* Video Player Styles */
video#main-video,
video#modal-main-video {
    background-color: #000;
}

video#main-video::-webkit-media-controls-panel {
    background-color: rgba(0, 0, 0, 0.8);
}

video#modal-main-video::-webkit-media-controls-panel {
    background-color: rgba(0, 0, 0, 0.8);
}

/* Video Badge Animation */
.absolute.top-3.left-3 {
    animation: fadeInSlide 0.5s ease-out;
}

@keyframes fadeInSlide {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Video player responsive */
@media (max-width: 768px) {
    video#main-video,
    video#modal-main-video {
        max-height: 100%;
        object-fit: contain;
    }
}

/* Video Play Overlay */
#video-play-overlay {
    transition: opacity 0.3s ease-in-out;
}

#video-play-overlay:hover .w-20 {
    transform: scale(1.1);
}

/* Video Poster */
video[poster] {
    object-fit: cover;
}

/* Play button pulse animation */
@keyframes playPulse {
    0%, 100% { 
        transform: scale(1); 
        box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.4);
    }
    50% { 
        transform: scale(1.05); 
        box-shadow: 0 0 0 15px rgba(147, 51, 234, 0);
    }
}

#video-play-overlay .w-20 {
    animation: playPulse 2s infinite;
}

/* Video container hover */
#image-container:has(video) {
    cursor: pointer;
}

#image-container:has(video):hover #video-play-overlay .w-20 {
    transform: scale(1.15);
}
</style>

<script>
// Global variables for variant selection
let selectedAttributes = {};
let variants = [];

// Gallery variables
let currentImageIndex = 0;
let allImages = [
    @if(!empty($allImages))
        @foreach($allImages as $index => $imageUrl)
            '{{ $imageUrl }}'{{ $index < count($allImages) - 1 ? ',' : '' }}
        @endforeach
    @endif
];

// Helper function for showing alerts with SweetAlert2 or fallback
function showAlert(options) {
    if (typeof Swal !== 'undefined') {
        return Swal.fire(options);
    } else {
        // Fallback to native confirm/alert
        if (options.showCancelButton) {
            const result = confirm(options.text || options.html?.replace(/<[^>]*>/g, '') || '');
            return Promise.resolve({ isConfirmed: result });
        } else {
            alert((options.title ? options.title + '\n\n' : '') + (options.text || options.html?.replace(/<[^>]*>/g, '') || ''));
            return Promise.resolve({ isConfirmed: true });
        }
    }
}

// Video control function
function playVideoOnClick(event) {
    console.log('playVideoOnClick called', event);
    event.preventDefault();
    event.stopPropagation();
    
    const video = document.getElementById('main-video');
    const overlay = document.getElementById('video-play-overlay');
    
    console.log('Video element:', video);
    console.log('Overlay element:', overlay);
    
    if (video && overlay) {
        // Hide overlay immediately
        overlay.style.display = 'none';
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        overlay.style.pointerEvents = 'none';
        console.log('Overlay hidden immediately');
        
        // Play video
        video.play().then(() => {
            console.log('Video playing successfully');
        }).catch((error) => {
            console.log('Video play failed:', error);
            // Show overlay again if play failed
            overlay.style.display = 'flex';
            overlay.style.opacity = '1';
            overlay.style.visibility = 'visible';
            overlay.style.pointerEvents = 'auto';
        });
    }
}

// Generate video thumbnail from first frame
function generateVideoThumbnail(videoUrl, callback) {
    const video = document.createElement('video');
    video.crossOrigin = 'anonymous';
    video.preload = 'metadata';
    
    video.addEventListener('loadedmetadata', function() {
        // Seek to 1 second or 10% of duration, whichever is smaller
        const seekTime = Math.min(1, video.duration * 0.1);
        video.currentTime = seekTime;
    });
    
    video.addEventListener('seeked', function() {
        // Create canvas to capture frame
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        
        // Set canvas size to video size
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert canvas to data URL
        const thumbnailDataUrl = canvas.toDataURL('image/jpeg', 0.8);
        
        // Call callback with thumbnail
        if (callback) {
            callback(thumbnailDataUrl);
        }
    });
    
    video.addEventListener('error', function() {
        console.warn('Could not generate thumbnail for video:', videoUrl);
        if (callback) {
            callback(null);
        }
    });
    
    // Start loading video
    video.src = videoUrl;
}

// Set video thumbnail as poster
function setVideoThumbnail(videoElement, videoUrl) {
    if (!videoElement || !videoUrl) return;
    
    // Check if video already has a poster
    if (videoElement.poster && videoElement.poster.trim() !== '') {
        return; // Already has poster, don't override
    }
    
    generateVideoThumbnail(videoUrl, function(thumbnailDataUrl) {
        if (thumbnailDataUrl && videoElement) {
            videoElement.poster = thumbnailDataUrl;
            console.log('Video thumbnail generated and set as poster');
        }
    });
}

// Generate thumbnails for all videos on page load
function generateVideoThumbnailsOnLoad() {
    // Check if current media is video and needs thumbnail
    const mainVideo = document.getElementById('main-video');
    if (mainVideo && mainVideo.src && !mainVideo.poster) {
        setTimeout(() => {
            setVideoThumbnail(mainVideo, mainVideo.src);
        }, 1000);
    }
    
    // Generate thumbnails for video thumbnails in gallery
    document.querySelectorAll('#thumbnail-container button').forEach((btn, index) => {
        const mediaUrl = allImages[index];
        if (mediaUrl && (mediaUrl.includes('.mp4') || mediaUrl.includes('.mov') || mediaUrl.includes('.avi') || mediaUrl.includes('.webm'))) {
            // Check if thumbnail already shows video content
            const thumbnailImg = btn.querySelector('img');
            if (thumbnailImg) {
                // This is an image thumbnail, generate video thumbnail
                setTimeout(() => {
                    generateVideoThumbnail(mediaUrl, function(thumbnailDataUrl) {
                        if (thumbnailDataUrl) {
                            thumbnailImg.src = thumbnailDataUrl;
                            thumbnailImg.alt = 'Video thumbnail';
                        }
                    });
                }, 500 * (index + 1)); // Stagger the generation
            }
        }
    });
}

// Show overlay when video pauses/ends
function setupVideoControls() {
    const video = document.getElementById('main-video');
    const overlay = document.getElementById('video-play-overlay');
    
    if (video && overlay) {
        // Add click event to video element
        video.addEventListener('click', function(e) {
            e.stopPropagation();
            playVideoOnClick(e);
        });
        
        // Show overlay when paused/ended
        video.addEventListener('pause', () => {
            console.log('Video pause - showing overlay');
            overlay.style.display = 'flex';
            overlay.style.visibility = 'visible';
            overlay.style.pointerEvents = 'auto';
            overlay.style.opacity = '1';
        });
        
        video.addEventListener('ended', () => {
            console.log('Video ended - showing overlay');
            overlay.style.display = 'flex';
            overlay.style.visibility = 'visible';
            overlay.style.pointerEvents = 'auto';
            overlay.style.opacity = '1';
        });
        
        // Hide overlay when playing
        video.addEventListener('play', () => {
            console.log('Video play - hiding overlay');
            overlay.style.display = 'none';
            overlay.style.visibility = 'hidden';
            overlay.style.pointerEvents = 'none';
            overlay.style.opacity = '0';
        });
    }
}

// Initialize variants data when page loads
document.addEventListener('DOMContentLoaded', function() {
    const variantsDataElement = document.getElementById('variants-data');
    if (variantsDataElement) {
        variants = JSON.parse(variantsDataElement.textContent);
        
        // Set initial selections from first variant
        if (variants.length > 0) {
            const firstVariant = variants[0];
            selectedAttributes = { ...firstVariant.attributes };
            updateAllAttributeButtons();
            updateVariantSelection();
        }
    }
    
    // Preload all images for smooth transitions
    preloadImages();
    
    // Initialize image effects
    initializeImageEffects();
    
    // Setup video controls and overlay
    setupVideoControls();
    
    // Add direct click handler for video
    const mainVideo = document.getElementById('main-video');
    if (mainVideo) {
        mainVideo.addEventListener('click', function(e) {
            console.log('Video clicked directly');
            playVideoOnClick(e);
        });
    }
    
    // Generate video thumbnails if needed
    generateVideoThumbnailsOnLoad();
    
    // Save current product to recently viewed
    saveToRecentlyViewed();
    
    // Load and display recently viewed products
    loadRecentlyViewed();
    
    // Detect location and calculate shipping
    detectLocationAndCalculateShipping();
});

// Preload all images
function preloadImages() {
    if (allImages && allImages.length > 0) {
        allImages.forEach(imageUrl => {
            const img = new Image();
            img.src = imageUrl;
        });
    }
}

// Initialize image effects
function initializeImageEffects() {
    const mainImage = document.getElementById('main-image');
    const mainVideo = document.getElementById('main-video');
    const imageContainer = document.getElementById('image-container');
    
    if (mainImage && imageContainer) {
        // Add fade-in animation on load
        mainImage.addEventListener('load', function() {
            this.classList.add('image-fade-in');
        });
        
        // Add zoom effect on mouse move (only for images, not videos)
        imageContainer.addEventListener('mousemove', function(e) {
            // Don't apply zoom if video is visible
            if (mainVideo && !mainVideo.classList.contains('hidden')) {
                return;
            }
            
            const rect = imageContainer.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Calculate percentage position
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            // Apply transform origin and scale
            mainImage.style.transformOrigin = `${xPercent}% ${yPercent}%`;
            mainImage.style.transform = 'scale(2)';
            mainImage.style.cursor = 'zoom-in';
        });
        
        // Reset transform on mouse leave
        imageContainer.addEventListener('mouseleave', function() {
            mainImage.style.transformOrigin = 'center center';
            mainImage.style.transform = 'scale(1)';
            mainImage.style.cursor = 'zoom-in';
        });
        
        // Click to open gallery modal (only for images, videos have their own controls)
        imageContainer.addEventListener('click', function(e) {
            // Don't open modal if clicking on video or video controls
            if (mainVideo && !mainVideo.classList.contains('hidden')) {
                // Check if click is on video element itself or video overlay
                if (e.target === mainVideo || 
                    mainVideo.contains(e.target) || 
                    e.target.closest('#video-play-overlay') ||
                    e.target.closest('video')) {
                    // Play video on click
                    playVideoOnClick(e);
                    return; // Don't open modal
                }
            }
            openGalleryModal();
        });
    }
}

function changeMainImage(mediaUrl, index = null) {
    const mainImage = document.getElementById('main-image');
    const mainVideo = document.getElementById('main-video');
    const imageContainer = document.getElementById('image-container');
    const imageLoading = document.getElementById('image-loading');
    const imageCounter = document.getElementById('image-counter');
    const videoOverlay = document.getElementById('video-play-overlay');
    
    // Check if media is video
    const isVideo = mediaUrl.includes('.mp4') || mediaUrl.includes('.mov') || mediaUrl.includes('.avi') || mediaUrl.includes('.webm');
    
    // Update current image index
    if (index !== null) {
        currentImageIndex = index;
    } else {
        currentImageIndex = allImages.indexOf(mediaUrl);
    }
    
    // Update image counter
    if (imageCounter) {
        imageCounter.textContent = currentImageIndex + 1;
    }
    
    if (isVideo) {
        // Get poster from first available image
        const posterImage = allImages.find(url => 
            !url.includes('.mp4') && !url.includes('.mov') && !url.includes('.avi') && !url.includes('.webm')
        );
        
        // Hide image, show video
        if (mainImage) {
            mainImage.classList.add('hidden');
        }
        if (mainVideo) {
            mainVideo.classList.remove('hidden');
            mainVideo.src = mediaUrl;
            if (posterImage) {
                mainVideo.poster = posterImage;
            } else {
                // Generate thumbnail from video if no poster image
                setTimeout(() => {
                    setVideoThumbnail(mainVideo, mediaUrl);
                }, 500);
            }
            mainVideo.load();
            
            // Show play overlay
            if (videoOverlay) {
                videoOverlay.style.display = 'flex';
                videoOverlay.style.opacity = '1';
            }
        } else {
            // Create video element if doesn't exist
            const videoEl = document.createElement('video');
            videoEl.id = 'main-video';
            videoEl.className = 'w-full h-full object-cover cursor-pointer';
            videoEl.controls = true;
            videoEl.playsinline = true;
            videoEl.addEventListener('click', function(e) {
                e.stopPropagation();
                playVideoOnClick(e);
            });
            if (posterImage) {
                videoEl.poster = posterImage;
            }
            videoEl.innerHTML = `<source src="${mediaUrl}" type="video/mp4">`;
            
            const container = mainImage.parentElement;
            container.insertBefore(videoEl, mainImage);
            mainImage.classList.add('hidden');
            
            // Create overlay if doesn't exist
            if (!videoOverlay) {
                const overlayEl = document.createElement('div');
                overlayEl.id = 'video-play-overlay';
                overlayEl.className = 'absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 transition-opacity cursor-pointer z-20';
                overlayEl.addEventListener('click', function(e) {
                    e.stopPropagation();
                    playVideoOnClick(e);
                });
                overlayEl.innerHTML = `
                    <div class="w-20 h-20 bg-white bg-opacity-90 rounded-full flex items-center justify-center shadow-2xl hover:bg-opacity-100 hover:scale-110 transition-all duration-300">
                        <svg class="w-10 h-10 text-purple-600 ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path>
                        </svg>
                    </div>
                `;
                imageContainer.appendChild(overlayEl);
            }
            
            // Generate thumbnail from video if no poster image
            if (!posterImage) {
                setTimeout(() => {
                    setVideoThumbnail(videoEl, mediaUrl);
                }, 500);
            }
            
            // Setup video event listeners
            setupVideoControls();
        }
    } else {
        // Hide video, show image
        if (mainVideo) {
            mainVideo.classList.add('hidden');
            mainVideo.pause();
        }
        if (videoOverlay) {
            videoOverlay.style.display = 'none';
        }
        if (mainImage) {
            mainImage.classList.remove('hidden');
            
            // Show loading spinner
            if (imageLoading) {
                imageLoading.classList.remove('hidden');
            }
            
            // Create new image element for smooth transition
            const newImage = new Image();
            newImage.onload = function() {
                // Hide loading spinner
                if (imageLoading) {
                    imageLoading.classList.add('hidden');
                }
                
                // Update main image with fade effect
                mainImage.style.opacity = '0';
                setTimeout(() => {
                    mainImage.src = mediaUrl;
                    mainImage.style.opacity = '1';
                }, 150);
            };
            
            newImage.onerror = function() {
                // Hide loading spinner on error
                if (imageLoading) {
                    imageLoading.classList.add('hidden');
                }
                console.error('Failed to load image:', mediaUrl);
            };
            
            // Start loading the new image
            newImage.src = mediaUrl;
        }
    }
    
    // Update active thumbnail
    document.querySelectorAll('#thumbnail-container button').forEach((btn, btnIndex) => {
        if (btnIndex === currentImageIndex) {
            btn.classList.remove('border-gray-200');
            btn.classList.add('border-[#005366]');
        } else {
            btn.classList.remove('border-[#005366]');
            btn.classList.add('border-gray-200');
        }
    });
}

// Gallery Modal Functions
function openGalleryModal() {
    const modal = document.getElementById('gallery-modal');
    const imageCounter = document.getElementById('modal-image-counter');
    
    modal.classList.remove('hidden');
    imageCounter.textContent = currentImageIndex + 1;
    
    // Display current media (image or video)
    updateModalMedia(allImages[currentImageIndex]);
    
    // Update modal thumbnails
    updateModalThumbnails();
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeGalleryModal() {
    const modal = document.getElementById('gallery-modal');
    const modalVideo = document.getElementById('modal-main-video');
    
    // Pause video if playing
    if (modalVideo && !modalVideo.paused) {
        modalVideo.pause();
    }
    
    modal.classList.add('hidden');
    
    // Restore body scroll
    document.body.style.overflow = 'auto';
}

function previousImage() {
    currentImageIndex = currentImageIndex > 0 ? currentImageIndex - 1 : allImages.length - 1;
    updateModalImage();
}

function nextImage() {
    currentImageIndex = currentImageIndex < allImages.length - 1 ? currentImageIndex + 1 : 0;
    updateModalImage();
}

function selectModalImage(imageUrl, index) {
    currentImageIndex = index;
    updateModalImage();
}

function updateModalImage() {
    const imageCounter = document.getElementById('modal-image-counter');
    
    // Update media (image or video)
    updateModalMedia(allImages[currentImageIndex]);
    
    imageCounter.textContent = currentImageIndex + 1;
    
    // Update modal thumbnails
    updateModalThumbnails();
    
    // Update main image and thumbnails
    changeMainImage(allImages[currentImageIndex], currentImageIndex);
}

// New function to handle both image and video in modal
function updateModalMedia(mediaUrl) {
    const modalImage = document.getElementById('modal-main-image');
    const modalVideo = document.getElementById('modal-main-video');
    const modalVideoSource = document.getElementById('modal-video-source');
    const modalMediaBadge = document.getElementById('modal-media-badge');
    
    const isVideo = mediaUrl.includes('.mp4') || mediaUrl.includes('.mov') || mediaUrl.includes('.avi') || mediaUrl.includes('.webm');
    
    if (isVideo) {
        // Get poster from first available image
        const posterImage = allImages.find(url => 
            !url.includes('.mp4') && !url.includes('.mov') && !url.includes('.avi') && !url.includes('.webm')
        );
        
        // Show video, hide image
        if (modalImage) {
            modalImage.classList.add('hidden');
        }
        if (modalVideo) {
            modalVideo.classList.remove('hidden');
            if (modalVideoSource) {
                modalVideoSource.src = mediaUrl;
            }
            if (posterImage) {
                modalVideo.poster = posterImage;
            }
            modalVideo.load();
        }
        if (modalMediaBadge) {
            modalMediaBadge.classList.remove('hidden');
            modalMediaBadge.classList.add('flex');
        }
    } else {
        // Show image, hide video
        if (modalVideo) {
            modalVideo.classList.add('hidden');
            modalVideo.pause();
        }
        if (modalImage) {
            modalImage.classList.remove('hidden');
            modalImage.src = mediaUrl;
        }
        if (modalMediaBadge) {
            modalMediaBadge.classList.add('hidden');
            modalMediaBadge.classList.remove('flex');
        }
    }
}

function updateModalThumbnails() {
    document.querySelectorAll('#gallery-modal .absolute.bottom-4 button').forEach((btn, index) => {
        if (index === currentImageIndex) {
            btn.classList.add('border-white');
            btn.classList.remove('border-transparent');
        } else {
            btn.classList.remove('border-white');
        btn.classList.add('border-transparent');
        }
    });
}

// Thumbnail scrolling for horizontal scroll
function scrollThumbnails(direction) {
    const container = document.getElementById('thumbnail-container');
    const scrollAmount = 200;
    
    if (direction === 'left') {
        container.scrollLeft -= scrollAmount;
    } else {
        container.scrollLeft += scrollAmount;
    }
}

// Keyboard navigation for gallery
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('gallery-modal');
    if (!modal.classList.contains('hidden')) {
        switch(e.key) {
            case 'Escape':
                closeGalleryModal();
                break;
            case 'ArrowLeft':
                previousImage();
                break;
            case 'ArrowRight':
                nextImage();
                break;
        }
    }
});

        function selectAttribute(attributeName, value) {
            selectedAttributes[attributeName] = value;
            updateVariantSelection();
            updateAllAttributeButtons();
            
            // Update selected color name display if it's a color attribute
            if (attributeName === 'Color' || attributeName === 'Colour') {
                const selectedColorName = document.getElementById('selected-color-name');
                if (selectedColorName) {
                    selectedColorName.textContent = value;
                }
            }
        }

        // Legacy functions for backward compatibility
        function selectColor(color) {
            selectAttribute('Color', color);
        }
        
        function selectColour(color) {
            selectAttribute('Colour', color);
        }

        function selectSize(size) {
            selectAttribute('Size', size);
        }

function updateAllAttributeButtons() {
    // Update color swatches
    document.querySelectorAll('.color-swatch').forEach(btn => {
        const attribute = btn.dataset.attribute;
        const value = btn.dataset.value;
        
        if (selectedAttributes[attribute] === value) {
            btn.classList.add('border-[#005366]', 'ring-2', 'ring-[#005366]', 'ring-offset-2');
            btn.classList.remove('border-gray-300', 'hover:border-gray-400');
            // Add checkmark
            if (!btn.querySelector('svg')) {
                btn.innerHTML = `
                    <svg class="w-4 h-4 text-white absolute inset-0 m-auto" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                `;
            }
        } else {
            btn.classList.remove('border-[#005366]', 'ring-2', 'ring-[#005366]', 'ring-offset-2');
            btn.classList.add('border-gray-300', 'hover:border-gray-400');
            // Remove checkmark
            const svg = btn.querySelector('svg');
            if (svg) {
                svg.remove();
            }
        }
    });
    
    // Update attribute buttons
    document.querySelectorAll('.attribute-option').forEach(btn => {
        const attribute = btn.dataset.attribute;
        const value = btn.dataset.value;
        
        if (selectedAttributes[attribute] === value) {
            btn.classList.add('border-[#005366]', 'bg-[#005366]', 'text-white');
            btn.classList.remove('border-gray-300', 'text-gray-700');
        } else {
            btn.classList.remove('border-[#005366]', 'bg-[#005366]', 'text-white');
            btn.classList.add('border-gray-300', 'text-gray-700');
        }
    });
    
    // Update dropdowns
    Object.keys(selectedAttributes).forEach(attribute => {
        const selector = document.getElementById(`${attribute.toLowerCase()}-selector`);
        if (selector) {
            selector.value = selectedAttributes[attribute] || '';
        }
    });
}

// Helper function to convert HTML to text
function htmlToText(html) {
    if (!html) return '';
    
    // Create a temporary div element
    const temp = document.createElement('div');
    temp.innerHTML = html;
    
    // Get text content (this automatically strips HTML tags)
    let text = temp.textContent || temp.innerText || '';
    
    // Decode HTML entities
    text = text.replace(/&nbsp;/g, ' ');
    text = text.replace(/&amp;/g, '&');
    text = text.replace(/&lt;/g, '<');
    text = text.replace(/&gt;/g, '>');
    text = text.replace(/&quot;/g, '"');
    text = text.replace(/&#39;/g, "'");
    
    // Clean up extra spaces
    text = text.replace(/\s+/g, ' ').trim();
    
    return text;
}

// Legacy functions for backward compatibility
function updateColorButtons() {
    updateAllAttributeButtons();
}

function updateSizeButtons() {
    updateAllAttributeButtons();
}

function updateVariantSelection() {
    // Find matching variant based on selected attributes
    const matchingVariant = variants.find(variant => {
        if (!variant.attributes) return false;
        
        // Check if all selected attributes match
        for (const [attribute, value] of Object.entries(selectedAttributes)) {
            if (variant.attributes[attribute] !== value) {
                return false;
            }
        }
        
        // Check if variant has attributes that are not selected (should not match)
        for (const [attribute, value] of Object.entries(variant.attributes)) {
            if (selectedAttributes[attribute] && selectedAttributes[attribute] !== value) {
                return false;
            }
        }
        
        return true;
    });
    
    if (matchingVariant) {
        // Update variant name
        let variantName = '';
        if (matchingVariant.variant_name) {
            variantName = matchingVariant.variant_name;
        } else if (matchingVariant.attributes && Object.keys(matchingVariant.attributes).length > 0) {
            const attrParts = [];
            for (const [key, value] of Object.entries(matchingVariant.attributes)) {
                attrParts.push(value);
            }
            variantName = attrParts.join(' - ');
        } else if (matchingVariant.color && matchingVariant.size) {
            variantName = `${matchingVariant.color} - ${matchingVariant.size}`;
        } else if (matchingVariant.size) {
            variantName = `Size: ${matchingVariant.size}`;
        } else if (matchingVariant.color) {
            variantName = `Color: ${matchingVariant.color}`;
        } else if (matchingVariant.colour) {
            variantName = `Colour: ${matchingVariant.colour}`;
        } else {
            variantName = 'Standard';
        }
        
        document.getElementById('selected-variant-name').textContent = variantName;
        
        // Update price
        document.getElementById('selected-variant-price').textContent = `$${parseFloat(matchingVariant.price).toFixed(2)}`;
        
        // Update stock
        const stockElement = document.getElementById('selected-variant-stock');
        if (stockElement) {
            if (matchingVariant.quantity !== null) {
                stockElement.textContent = matchingVariant.quantity > 0 
                    ? `Stock: ${matchingVariant.quantity} available`
                    : 'Stock: Out of stock';
                stockElement.style.display = 'block';
            } else {
                stockElement.style.display = 'none';
            }
        }
        
        // Update Add to Cart button based on stock
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const cartText = document.getElementById('cart-text');
        if (addToCartBtn && cartText) {
            if (matchingVariant.quantity !== null && matchingVariant.quantity <= 0) {
                addToCartBtn.disabled = true;
                cartText.textContent = 'Out of stock';
                addToCartBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                addToCartBtn.disabled = false;
                cartText.textContent = 'Add to Cart';
                addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
        
        // Update description
        const descElement = document.getElementById('selected-variant-description');
        if (descElement) {
            if (matchingVariant.description) {
                // Convert HTML to text before setting textContent
                descElement.textContent = htmlToText(matchingVariant.description);
                descElement.style.display = 'block';
            } else {
                descElement.style.display = 'none';
            }
        }
        
        // Update attributes
        const attributesElement = document.getElementById('selected-variant-attributes');
        if (attributesElement && matchingVariant.attributes) {
            attributesElement.innerHTML = '';
            Object.entries(matchingVariant.attributes).forEach(([key, value]) => {
                const attrDiv = document.createElement('div');
                attrDiv.className = 'text-xs';
                attrDiv.innerHTML = `
                    <span class="font-medium text-gray-600">${key.charAt(0).toUpperCase() + key.slice(1)}:</span>
                    <span class="text-gray-700">${value}</span>
                `;
                attributesElement.appendChild(attrDiv);
            });
        }
        
        // Update main image if variant has specific media
        if (matchingVariant.media && matchingVariant.media.length > 0) {
            const firstMediaUrl = Array.isArray(matchingVariant.media[0]) 
                ? matchingVariant.media[0].url 
                : matchingVariant.media[0];
            if (firstMediaUrl) {
                document.getElementById('main-image').src = firstMediaUrl;
                
                // Update thumbnail selection
                document.querySelectorAll('[onclick*="changeMainImage"]').forEach(btn => {
                    btn.classList.remove('border-[#005366]');
                    btn.classList.add('border-transparent');
                    
                    const img = btn.querySelector('img');
                    if (img && img.src === firstMediaUrl) {
                        btn.classList.add('border-[#005366]');
                        btn.classList.remove('border-transparent');
                    }
                });
            }
        }
        
        // Update main price display
        const mainPriceElement = document.querySelector('.text-4xl.font-bold.text-\\[\\#E2150C\\]');
        if (mainPriceElement) {
            mainPriceElement.textContent = `$${parseFloat(matchingVariant.price).toFixed(2)}`;
        }
    } else {
        // No matching variant found, enable button and reset text
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const cartText = document.getElementById('cart-text');
        if (addToCartBtn && cartText) {
            addToCartBtn.disabled = false;
            cartText.textContent = 'Add to Cart';
            addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Recently Viewed Functions
function saveToRecentlyViewed() {
    const currentProduct = {
        id: {{ $product->id }},
        slug: '{{ $product->slug }}',
        name: '{{ addslashes($product->name) }}',
        price: {{ $product->base_price }},
        image: '@php
            if ($media && count($media) > 0) {
                if (is_string($media[0])) {
                    echo $media[0];
                } elseif (is_array($media[0])) {
                    echo $media[0]["url"] ?? $media[0]["path"] ?? reset($media[0]) ?? "";
                }
            }
        @endphp',
        shop: '{{ $product->shop->name ?? "Unknown Shop" }}',
        shop_slug: '{{ $product->shop->shop_slug ?? "" }}',
        timestamp: Date.now()
    };
    
    // Get existing recently viewed products
    let recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
    
    // Remove current product if it exists
    recentlyViewed = recentlyViewed.filter(p => p.id !== currentProduct.id);
    
    // Add current product to the beginning
    recentlyViewed.unshift(currentProduct);
    
    // Keep only last 10 products in history (but only show 5)
    recentlyViewed = recentlyViewed.slice(0, 10);
    
    // Save back to localStorage
    localStorage.setItem('recentlyViewed', JSON.stringify(recentlyViewed));
}

function loadRecentlyViewed() {
    const recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
    const container = document.getElementById('recently-viewed-container');
    const emptyState = document.getElementById('recently-viewed-empty');
    const wrapper = document.getElementById('recently-viewed-wrapper');
    
    if (!container) return;
    
    // Filter out current product and limit to 12 products
    const productsToShow = recentlyViewed
        .filter(p => p.id !== {{ $product->id }})
        .slice(0, 12);
    
    if (productsToShow.length === 0) {
        if (wrapper) wrapper.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    if (wrapper) wrapper.classList.remove('hidden');
    emptyState.classList.add('hidden');
    
    // Generate HTML for each product (same style as Related Products)
    container.innerHTML = productsToShow.map(product => `
        <a href="/products/${product.slug}" 
           class="flex-shrink-0 w-40 bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 group/item overflow-hidden border border-gray-200">
            <!-- Product Image -->
            <div class="relative aspect-square overflow-hidden">
                ${product.image ? `
                    <img src="${product.image}" 
                         alt="${product.name}" 
                         class="w-full h-full object-cover group-hover/item:scale-105 transition-transform duration-300"
                         onerror="this.parentElement.innerHTML='<div class=\\'w-full h-full bg-gray-200 flex items-center justify-center\\'><svg class=\\'w-6 h-6 text-gray-400\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\\'></path></svg></div>'">
                ` : `
                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                `}
            </div>

            <!-- Product Info (Compact) -->
            <div class="p-2.5">
                <h4 class="font-medium text-gray-900 text-xs line-clamp-2 group-hover/item:text-[#005366] transition-colors mb-1.5 h-8 overflow-hidden" title="${product.name}">
                    ${product.name.length > 30 ? product.name.substring(0, 30) + '...' : product.name}
                </h4>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-[#E2150C]">$${parseFloat(product.price).toFixed(2)}</span>
                    <div class="flex items-center text-xs text-gray-500">
                        <svg class="w-3 h-3 text-yellow-400 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        <span class="text-xs">4.5</span>
                    </div>
                </div>
            </div>
        </a>
    `).join('');
    
    // Show/hide navigation buttons based on number of products
    updateRecentlyViewedNavigation(productsToShow.length);
}

function formatTimeAgo(timestamp) {
    const now = Date.now();
    const diff = now - timestamp;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return 'Recently';
}

// Related Products Carousel (Desktop)
let relatedCurrentIndex = 0;

function scrollRelatedProducts(direction) {
    const container = document.getElementById('relatedProductsContainer');
    const track = document.getElementById('relatedProductsTrack');
    const prevBtn = document.getElementById('relatedPrevBtn');
    const nextBtn = document.getElementById('relatedNextBtn');
    
    if (!track) return;
    
    const itemWidth = 160 + 12; // w-40 (160px) + gap-3 (12px)
    const containerWidth = container.offsetWidth;
    const itemsVisible = Math.floor(containerWidth / itemWidth);
    const totalItems = track.children.length;
    const maxIndex = Math.max(0, totalItems - itemsVisible);
    
    if (direction === 'next') {
        relatedCurrentIndex = Math.min(relatedCurrentIndex + itemsVisible, maxIndex);
    } else {
        relatedCurrentIndex = Math.max(0, relatedCurrentIndex - itemsVisible);
    }
    
    const translateX = -relatedCurrentIndex * itemWidth;
    track.style.transform = `translateX(${translateX}px)`;
    
    // Update button states
    if (prevBtn) {
        if (relatedCurrentIndex === 0) {
            prevBtn.classList.add('opacity-0');
        } else {
            prevBtn.classList.remove('opacity-0');
        }
    }
    
    if (nextBtn) {
        if (relatedCurrentIndex >= maxIndex) {
            nextBtn.classList.add('opacity-0');
        } else {
            nextBtn.classList.remove('opacity-0');
        }
    }
}

// Clear recently viewed (utility function)
function clearRecentlyViewed() {
    localStorage.removeItem('recentlyViewed');
    loadRecentlyViewed();
}

// Recently Viewed Carousel Navigation
let recentlyViewedCurrentIndex = 0;

function scrollRecentlyViewed(direction) {
    const container = document.getElementById('recentlyViewedContainer');
    const track = document.getElementById('recently-viewed-container');
    const prevBtn = document.getElementById('recentlyViewedPrevBtn');
    const nextBtn = document.getElementById('recentlyViewedNextBtn');
    
    if (!track) return;
    
    const itemWidth = 160 + 12; // w-40 (160px) + gap-3 (12px)
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
    
    if (isDesktop && totalProducts > 5) {
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
        // Hide navigation buttons on mobile or if 5 or fewer products
        prevBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
        
        // Remove transform on mobile
        const track = document.getElementById('recently-viewed-container');
        if (track && !isDesktop) {
            track.style.transform = '';
        }
    }
}

// Returns Info Popup Functions
function toggleReturnsInfo() {
    const popup = document.getElementById('returns-info-popup');
    const backdrop = document.querySelector('.returns-popup-backdrop');
    
    if (popup.classList.contains('hidden')) {
        // Show popup
        popup.classList.remove('hidden');
        
        // Add backdrop
        if (!backdrop) {
            const backdropEl = document.createElement('div');
            backdropEl.className = 'returns-popup-backdrop';
            backdropEl.onclick = closeReturnsInfo;
            document.body.appendChild(backdropEl);
        }
    } else {
        closeReturnsInfo();
    }
}

function closeReturnsInfo() {
    const popup = document.getElementById('returns-info-popup');
    const backdrop = document.querySelector('.returns-popup-backdrop');
    
    popup.classList.add('hidden');
    if (backdrop) {
        backdrop.remove();
    }
}

// Close popup on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReturnsInfo();
    }
});

// Toggle Description Function
function toggleDescription() {
    const descriptionPreview = document.getElementById('description-preview');
    const descriptionFull = document.getElementById('description-full');
    const toggleText = document.getElementById('description-toggle-text');
    const toggleIcon = document.getElementById('description-toggle-icon');
    
    if (descriptionFull.classList.contains('hidden')) {
        // Show full description
        descriptionPreview.classList.add('hidden');
        descriptionFull.classList.remove('hidden');
        toggleText.textContent = 'Show Less';
        toggleIcon.style.transform = 'rotate(180deg)';
    } else {
        // Show preview only
        descriptionPreview.classList.remove('hidden');
        descriptionFull.classList.add('hidden');
        toggleText.textContent = 'Show More';
        toggleIcon.style.transform = 'rotate(0deg)';
    }
}


// IP-based Location Detection and Shipping Calculation
async function detectLocationAndCalculateShipping() {
    try {
        // Get user's IP and location
        const response = await fetch('https://ipapi.co/json/');
        const data = await response.json();
        
        const country = data.country_name || 'Unknown';
        const countryCode = data.country_code || 'US';
        
        // Update location display
        document.getElementById('customer-location').textContent = country;
        
        // Calculate shipping times based on country
        let shippingDays, deliveryEstimate;
        const currentDate = new Date();
        
        if (countryCode === 'VN') {
            // Vietnam: 5-7 days
            shippingDays = '5-7 days';
            deliveryEstimate = `Standard between ${formatDate(addDays(currentDate, 5))} - ${formatDate(addDays(currentDate, 7))}`;
        } else if (countryCode === 'US') {
            // United States: 10-15 days
            shippingDays = '10-15 days';
            deliveryEstimate = `Standard between ${formatDate(addDays(currentDate, 10))} - ${formatDate(addDays(currentDate, 15))}`;
        } else {
            // Other countries: 12-17 days
            shippingDays = '12-17 days';
            deliveryEstimate = `Standard between ${formatDate(addDays(currentDate, 12))} - ${formatDate(addDays(currentDate, 17))}`;
        }
        
        // Update delivery estimate
        document.getElementById('delivery-estimate').textContent = deliveryEstimate;
        
    } catch (error) {
        console.error('Error detecting location:', error);
        // Fallback to default
        document.getElementById('customer-location').textContent = 'your location';
        document.getElementById('delivery-estimate').textContent = 'Standard delivery time will be calculated based on your location';
    }
}

// Helper functions for date calculation
function addDays(date, days) {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

function formatDate(date) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[date.getMonth()]}. ${date.getDate()}`;
}

// Cart Functions
function addToCart() {
    console.log('addToCart function called');
    const btn = document.getElementById('add-to-cart-btn');
    const cartIcon = document.getElementById('cart-icon');
    const cartText = document.getElementById('cart-text');
    const cartLoading = document.getElementById('cart-loading');
    
    // Check if required elements exist
    if (!btn) {
        console.error('Add to cart button not found');
        return;
    }
    
    console.log('Button found, checking variant...');
    
    // Validate required customizations first
    const validation = validateRequiredCustomizations();
    if (!validation.isValid) {
        const message = validation.needToEnableCustomization 
            ? `<div class="text-left">
                    <p class="mb-3 text-gray-600">This product requires personalization. Please enable "Add Personalization" and fill in:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700">
                        ${validation.missingFields.map(field => `<li>${field}</li>`).join('')}
                    </ul>
                </div>`
            : `<div class="text-left">
                    <p class="mb-3 text-gray-600">Please fill in all required personalization information:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700">
                        ${validation.missingFields.map(field => `<li>${field}</li>`).join('')}
                    </ul>
                </div>`;
        
        showAlert({
            icon: 'warning',
            title: 'Missing Information',
            html: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-3 rounded-lg'
            }
        });
        
        // Scroll to customization section and auto-enable if needed
        if (validation.needToEnableCustomization) {
            const enableCheckbox = document.getElementById('enable-customization');
            if (enableCheckbox) {
                enableCheckbox.checked = true;
                toggleCustomization();
            }
        }
        
        const customizationContainer = document.getElementById('customization-container');
        if (customizationContainer) {
            setTimeout(() => {
                customizationContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
        return;
    }
    
    // Get selected variant to check quantity first
    const selectedVariant = getSelectedVariant();
    console.log('Selected variant:', selectedVariant);
    
    // Check if variant is out of stock
    if (selectedVariant && selectedVariant.quantity !== null && selectedVariant.quantity <= 0) {
        showAlert({
            icon: 'error',
            title: 'Out of Stock',
            text: 'This product is currently out of stock. Please choose another product.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-3 rounded-lg'
            }
        });
        return;
    }
    
    console.log('Variant check passed, proceeding...');
    
    // Disable button and show loading
    btn.disabled = true;
    if (cartIcon) cartIcon.classList.add('hidden');
    if (cartLoading) cartLoading.classList.remove('hidden');
    if (cartText) cartText.textContent = 'Adding...';
    
    const variantPrice = selectedVariant && selectedVariant.price ? selectedVariant.price : {{ $product->base_price }};
    
    // Get current product data
    const productData = {
        id: {{ $product->id }},
        name: '{{ addslashes($product->name) }}',
        slug: '{{ $product->slug }}',
        price: variantPrice,
        image: '@php
            if ($media && count($media) > 0) {
                if (is_string($media[0])) {
                    echo $media[0];
                } elseif (is_array($media[0])) {
                    echo $media[0]["url"] ?? $media[0]["path"] ?? reset($media[0]) ?? "";
                }
            }
        @endphp',
        shop: '{{ $product->shop->name ?? "Unknown Shop" }}',
        quantity: 1,
        selectedVariant: selectedVariant,
        customizations: getSelectedCustomizations(),
        addedAt: Date.now()
    };
    
    // Add to localStorage immediately for fast UX
    addToLocalCart(productData);
    
    // Track Facebook Pixel AddToCart event
    if (typeof fbq !== 'undefined') {
        fbq('track', 'AddToCart', {
            content_name: productData.name,
            content_ids: [productData.id],
            content_type: 'product',
            value: productData.price,
            currency: 'USD'
        });
    }
    
    // Try to sync with backend
    syncCartToBackend(productData)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success - sync localStorage with backend
                syncLocalStorageWithBackend();
                showCartSuccess();
            } else {
                console.error('Backend sync failed:', data.message);
                showCartSuccess('Cart saved locally');
            }
        })
        .catch((error) => {
            console.log('Network error - cart saved locally:', error);
            showCartSuccess('Cart saved locally');
        })
        .finally(() => {
            console.log('Finally block executed, showing popup...');
            // Reset button safely
            btn.disabled = false;
            if (cartIcon) cartIcon.classList.remove('hidden');
            if (cartLoading) cartLoading.classList.add('hidden');
            if (cartText) cartText.textContent = 'Add to Cart';
            
            // Show cart popup
            console.log('About to show cart popup with productData:', productData);
            showCartPopup(productData);
        });
}

function addToLocalCart(productData) {
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    // Check if product already exists in cart using same logic as backend
    const existingIndex = cart.findIndex(item => {
        if (item.id !== productData.id) return false;
        
        // Compare variants using attributes
        const variantMatch = compareVariantsLocal(item.selectedVariant, productData.selectedVariant);
        const customizationMatch = compareCustomizationsLocal(item.customizations, productData.customizations);
        
        return variantMatch && customizationMatch;
    });
    
    if (existingIndex !== -1) {
        // Update quantity
        cart[existingIndex].quantity += 1;
    } else {
        // Add new item
        cart.push(productData);
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

function compareVariantsLocal(variant1, variant2) {
    // Compare attributes if both have them
    if (variant1 && variant1.attributes && variant2 && variant2.attributes) {
        // Sort keys for consistent comparison
        const sorted1 = Object.keys(variant1.attributes).sort().reduce((result, key) => {
            result[key] = variant1.attributes[key];
            return result;
        }, {});
        const sorted2 = Object.keys(variant2.attributes).sort().reduce((result, key) => {
            result[key] = variant2.attributes[key];
            return result;
        }, {});
        
        return JSON.stringify(sorted1) === JSON.stringify(sorted2);
    }
    
    // Fallback: compare entire objects
    return JSON.stringify(variant1) === JSON.stringify(variant2);
}

function compareCustomizationsLocal(custom1, custom2) {
    return JSON.stringify(custom1 || {}) === JSON.stringify(custom2 || {});
}

function syncCartToBackend(productData) {
    return fetch('/api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(productData)
    });
}

function getSelectedVariant() {
    const variantsDataElement = document.getElementById('variants-data');
    if (!variantsDataElement) return null;
    
    const variants = JSON.parse(variantsDataElement.textContent);
    
    // Find matching variant based on selected attributes
    const matchingVariant = variants.find(variant => {
        if (!variant.attributes) return false;
        
        // Check if all selected attributes match
        for (const [attribute, value] of Object.entries(selectedAttributes)) {
            if (variant.attributes[attribute] !== value) {
                return false;
            }
        }
        
        return true;
    });
    
    // Return full variant info including price
    const variant = matchingVariant || variants[0] || null;
    return variant ? { 
        id: variant.id,
        attributes: variant.attributes,
        price: variant.price,
        quantity: variant.quantity,
        variant_name: variant.variant_name,
        media: variant.media
    } : null;
}

function getSelectedCustomizations() {
    const customizations = {};
    const inputs = document.querySelectorAll('.customization-input');
    
    inputs.forEach(input => {
        let label = input.name; // fallback to name
        
        // Try to get friendly label from data attribute or nearby element
        if (input.dataset.label) {
            label = input.dataset.label;
        } else {
            // Look for label element
            const labelElement = document.querySelector(`label[for="${input.id}"]`);
            if (labelElement) {
                label = labelElement.textContent.trim();
            } else {
                // Look for nearby text (for custom inputs without proper labels)
                const container = input.closest('.customization-group');
                if (container) {
                    const titleElement = container.querySelector('.customization-title');
                    if (titleElement) {
                        label = titleElement.textContent.trim();
                    }
                }
            }
        }
        
        if (input.type === 'radio' && input.checked) {
            customizations[label] = {
                value: input.value,
                price: parseFloat(input.dataset.price) || 0
            };
        } else if (input.type === 'checkbox' && input.checked) {
            customizations[label] = {
                value: input.value,
                price: parseFloat(input.dataset.price) || 0
            };
        } else if (input.type === 'text' || input.type === 'textarea') {
            if (input.value.trim() !== '') {
                customizations[label] = {
                    value: input.value.trim(),
                    price: parseFloat(input.dataset.price) || 0
                };
            }
        }
    });
    
    return customizations;
}

// Kiểm tra required customizations
function validateRequiredCustomizations() {
    const enableCustomizationCheckbox = document.getElementById('enable-customization');
    const customizationContainer = document.getElementById('customization-container');
    
    // Nếu không có customization container, không cần validate
    if (!customizationContainer) {
        return { isValid: true, missingFields: [] };
    }
    
    const missingFields = [];
    
    // Tìm tất cả các customization boxes
    const customizationBoxes = customizationContainer.querySelectorAll('.border.border-gray-200.rounded-lg.p-4.bg-white');
    
    // Kiểm tra xem có customization nào là required không
    let hasRequiredCustomizations = false;
    
    customizationBoxes.forEach(box => {
        const requiredBadge = box.querySelector('.bg-red-100.text-red-800');
        if (requiredBadge) {
            hasRequiredCustomizations = true;
        }
    });
    
    // Nếu có required customizations nhưng checkbox chưa được check
    if (hasRequiredCustomizations && (!enableCustomizationCheckbox || !enableCustomizationCheckbox.checked)) {
        customizationBoxes.forEach(box => {
            const titleElement = box.querySelector('h4');
            const requiredBadge = box.querySelector('.bg-red-100.text-red-800');
            
            if (requiredBadge && titleElement) {
                const customizationLabel = titleElement.textContent.trim();
                missingFields.push(customizationLabel);
            }
        });
        
        return {
            isValid: false,
            missingFields: missingFields,
            needToEnableCustomization: true
        };
    }
    
    // Nếu checkbox được check, kiểm tra các required fields
    if (enableCustomizationCheckbox && enableCustomizationCheckbox.checked) {
        customizationBoxes.forEach(box => {
            const titleElement = box.querySelector('h4');
            const requiredBadge = box.querySelector('.bg-red-100.text-red-800');
            
            // Chỉ kiểm tra nếu có required badge
            if (requiredBadge && titleElement) {
                const customizationLabel = titleElement.textContent.trim();
                let hasValidInput = false;
                
                // Tìm các input trong box này
                const inputs = box.querySelectorAll('.customization-input');
                
                inputs.forEach(input => {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        if (input.checked) {
                            hasValidInput = true;
                        }
                    } else if (input.type === 'text' || input.type === 'textarea') {
                        if (input.value.trim() !== '') {
                            hasValidInput = true;
                        }
                    }
                });
                
                // Nếu là radio button, kiểm tra xem có input nào trong group được checked không
                if (!hasValidInput && inputs.length > 0) {
                    if (inputs[0].type === 'radio') {
                        const inputName = inputs[0].name;
                        const radioInputs = customizationContainer.querySelectorAll(`input[name="${inputName}"]`);
                        hasValidInput = Array.from(radioInputs).some(radio => radio.checked);
                    }
                }
                
                if (!hasValidInput) {
                    missingFields.push(customizationLabel);
                }
            }
        });
    }
    
    return {
        isValid: missingFields.length === 0,
        missingFields: missingFields,
        needToEnableCustomization: false
    };
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    // Update cart count in header if exists
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = totalItems;
        element.style.display = totalItems > 0 ? 'flex' : 'none';
    });
    
    // Dispatch custom event to update header
    window.dispatchEvent(new CustomEvent('cartUpdated'));
}

function showCartSuccess(message = 'Added to cart successfully!') {
    // Create success notification
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2';
    notification.innerHTML = `
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
        </svg>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function showCartPopup(addedProduct) {
    console.log('showCartPopup called with:', addedProduct);
    
    // Remove any existing popup first
    const existingPopup = document.getElementById('cart-popup-overlay');
    if (existingPopup) {
        existingPopup.remove();
    }
    
    // Create popup overlay
    const overlay = document.createElement('div');
    overlay.id = 'cart-popup-overlay';
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    
    console.log('Created overlay element');
    
    // Create popup content
    const popup = document.createElement('div');
    popup.className = 'bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto cart-popup-content';
    
    // Show loading state
    popup.innerHTML = `
        <div class="flex items-center justify-center p-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#005366]"></div>
        </div>
    `;
    
    overlay.appendChild(popup);
    document.body.appendChild(overlay);
    
    console.log('Overlay appended to body');
    
    // Fetch cart data from backend
    fetch('/api/cart/get', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Cart API Response:', data); // Debug log
        if (data.success) {
            const cartItems = data.cart_items || [];
            console.log('Cart Items count:', cartItems.length);
            if (cartItems.length > 0) {
                console.log('First item:', cartItems[0]);
                console.log('First item product:', cartItems[0].product);
                console.log('First item media:', cartItems[0].product?.media);
            }
            renderCartPopup(popup, cartItems, data.summary || {}, data.shipping_details || null);
        } else {
            console.warn('Backend returned failed status');
            renderCartPopup(popup, [], {}, null);
        }
    })
    .catch(error => {
        console.error('Failed to fetch cart:', error);
        popup.innerHTML = `
            <div class="p-6 text-center">
                <p class="text-red-600 mb-4">Unable to load cart. Please try again.</p>
                <p class="text-sm text-gray-600 mb-4">Error: ${error.message}</p>
                <button onclick="closeCartPopup()" class="bg-[#005366] text-white px-6 py-2 rounded-lg hover:bg-[#003d4d]">
                    Close
                </button>
            </div>
        `;
    });
    
    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeCartPopup();
        }
    });
    
    // Close on escape key
    const handleEscape = (e) => {
        if (e.key === 'Escape') {
            closeCartPopup();
            document.removeEventListener('keydown', handleEscape);
        }
    };
    document.addEventListener('keydown', handleEscape);
}

function renderCartPopup(popup, cartItems, summary, shippingDetails) {
    const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    
    // Calculate subtotal
    const subtotal = parseFloat(summary.subtotal || cartItems.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0));
    
    // Check if order qualifies for free shipping (>= $100)
    const qualifiesForFreeShipping = subtotal >= 100;
    const shippingCost = qualifiesForFreeShipping ? 0 : (summary.shipping || 0);
    const totalPrice = subtotal + shippingCost;
    
    console.log('Cart popup calculations:', {
        subtotal: subtotal,
        qualifiesForFreeShipping: qualifiesForFreeShipping,
        shippingCost: shippingCost,
        totalPrice: totalPrice
    });
    
    popup.innerHTML = `
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 p-6 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Shopping Cart</h2>
            <button onclick="closeCartPopup()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Cart Items -->
        <div class="p-6">
            <div class="space-y-4" id="cart-popup-items">
                ${generateCartPopupItems(cartItems)}
            </div>
        </div>
        
        <!-- Total Section -->
        <div class="border-t border-gray-200 p-6 bg-gray-50">
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal (${totalItems} items)</span>
                    <span class="font-semibold">$${subtotal.toFixed(2)}</span>
                </div>
                ${qualifiesForFreeShipping ? 
                    `<div class="flex justify-between text-gray-600">
                        <span>Shipping</span>
                        <span class="font-semibold text-green-600">FREE</span>
                    </div>
                    <div class="text-xs text-green-600 bg-green-50 p-2 rounded">
                        🎉 You qualify for free shipping on orders $100+!
                    </div>` :
                    `<div class="flex justify-between items-center text-gray-600">
                        <span>Shipping</span>
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <select id="popupShippingCountry" class="text-sm border-2 border-gray-200 rounded-lg px-3 py-2 appearance-none bg-white pr-8 cursor-pointer hover:border-gray-300 focus:border-[#005366] focus:outline-none transition-colors min-w-[80px] [&::-ms-expand]:hidden [&::-webkit-appearance]:none">
                                    <option value="US">🇺🇸 US</option>
                                    <option value="UK">🇺k UK</option>
                                </select>
                                <div class="absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            <span id="popupShippingCost" class="font-semibold text-right min-w-[5rem] text-lg">
                                ${summary.shipping !== undefined ? 
                                    (summary.shipping == 0 ? 
                                        '<span class="text-green-600">FREE</span>' : 
                                        '$' + parseFloat(summary.shipping).toFixed(2)
                                    ) : 'Calculating...'
                                }
                            </span>
                        </div>
                    </div>
                    <div class="text-xs text-blue-600">
                        Add $${(100 - subtotal).toFixed(2)} more for free shipping!
                    </div>`
                }
                <div class="text-xs text-gray-500">
                    <span>Zone: </span>
                    <span id="popupShippingZone">United States</span>
                </div>
            </div>
            <div class="border-t pt-3 flex justify-between items-center text-xl font-bold">
                <span>Total:</span>
                <span class="text-[#005366]">$${parseFloat(totalPrice).toFixed(2)}</span>
            </div>
        </div>
        
        <!-- Checkout Section -->
        <div class="p-6 bg-white">
            <!-- Checkout Buttons -->
            <div class="flex space-x-3 mb-3">
                <button onclick="closeCartPopup(); window.location.href='{{ route('checkout.index') }}'" 
                        class="flex-1 bg-[#005366] hover:bg-[#003d4d] text-white font-bold py-4 px-6 rounded-xl transition-colors">
                    Checkout
                </button>
                <button onclick="closeCartPopup(); window.location.href='{{ route('cart.index') }}'" 
                        class="flex-1 bg-white text-gray-800 font-semibold py-4 px-6 rounded-xl border-2 border-gray-300 hover:border-gray-400 transition-colors">
                    View cart
                </button>
            </div>
            
            <!-- Continue Shopping -->
            <div class="text-center">
                <button onclick="closeCartPopup(); window.location.href='{{ route('products.index') }}'" 
                        class="text-[#005366] hover:underline font-medium">
                    Continue Shopping
                </button>
            </div>
        </div>
        
        <!-- You may also like -->
        <div class="p-6 bg-gray-50 border-t border-gray-200">
            <h3 class="font-bold text-gray-900 mb-4">You may also like</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" id="cross-sell-products">
                ${generateCrossSellProducts()}
            </div>
        </div>
    `;
    
    // Setup shipping calculator after rendering
    setTimeout(() => {
        setupPopupShippingCalculator();
        
        // Set zone name if available
        if (shippingDetails && shippingDetails.zone_name) {
            const zoneElement = document.getElementById('popupShippingZone');
            if (zoneElement) {
                zoneElement.textContent = shippingDetails.zone_name;
            }
        }
        
        // Check if qualifies for free shipping and update display accordingly
        if (!qualifiesForFreeShipping) {
            const popupShippingCost = document.getElementById('popupShippingCost');
            if (popupShippingCost && summary.shipping !== undefined) {
                popupShippingCost.innerHTML = summary.shipping == 0 ? 
                    '<span class="text-green-600">FREE</span>' : 
                    '$' + parseFloat(summary.shipping).toFixed(2);
            }
        }
        
        // Setup event delegation for cart buttons
        setupCartPopupEventDelegation();
    }, 100);
}

// Setup event delegation for cart popup buttons
function setupCartPopupEventDelegation() {
    const cartPopupOverlay = document.getElementById('cart-popup-overlay');
    if (!cartPopupOverlay) return;
    
    // Remove existing listener if any
    cartPopupOverlay.removeEventListener('click', handleCartPopupClick);
    
    // Add event listener
    cartPopupOverlay.addEventListener('click', handleCartPopupClick);
}

// Handle all cart popup clicks
function handleCartPopupClick(e) {
    const target = e.target.closest('button');
    
    // Check if clicking on a button first
    if (target) {
        // Handle remove item button
        if (target.classList.contains('remove-cart-item')) {
            e.preventDefault();
            e.stopPropagation();
            const cartItemId = parseInt(target.dataset.cartItemId);
            if (cartItemId) {
                removeCartItemById(cartItemId);
            }
            return;
        }
        
        // Handle decrease quantity button
        if (target.classList.contains('decrease-quantity')) {
            e.preventDefault();
            e.stopPropagation();
            const cartItemId = parseInt(target.dataset.cartItemId);
            const newQuantity = parseInt(target.dataset.newQuantity);
            if (cartItemId && newQuantity >= 0) {
                updateCartItemQuantity(e, cartItemId, newQuantity);
            }
            return;
        }
        
        // Handle increase quantity button
        if (target.classList.contains('increase-quantity')) {
            e.preventDefault();
            e.stopPropagation();
            const cartItemId = parseInt(target.dataset.cartItemId);
            const newQuantity = parseInt(target.dataset.newQuantity);
            if (cartItemId && newQuantity > 0) {
                updateCartItemQuantity(e, cartItemId, newQuantity);
            }
            return;
        }
        
        // Handle cross-sell add button - stop propagation to parent div
        if (target.classList.contains('cross-sell-add-btn')) {
            e.preventDefault();
            e.stopPropagation();
            return; // Let the cross-sell-product handler take care of it
        }
    }
    
    // Check for cross-sell product click (div click, not button)
    const crossSellProduct = e.target.closest('.cross-sell-product');
    if (crossSellProduct) {
        e.preventDefault();
        const productId = parseInt(crossSellProduct.dataset.productId);
        const productName = crossSellProduct.dataset.productName;
        const productPrice = parseFloat(crossSellProduct.dataset.productPrice);
        const productImage = crossSellProduct.dataset.productImage;
        const productSlug = crossSellProduct.dataset.productSlug;
        const hasVariants = crossSellProduct.dataset.hasVariants === 'true';
        
        handleCrossSellClick(productId, productName, productPrice, productImage, productSlug, hasVariants);
        return;
    }
}


// Calculate item total including customizations (same as Cart model getTotalPrice())
function calculateItemTotal(item) {
    let total = parseFloat(item.price) * item.quantity;
    
    // Add customization prices
    if (item.customizations && typeof item.customizations === 'object') {
        Object.values(item.customizations).forEach(customization => {
            const customPrice = parseFloat(customization.price || 0);
            total += customPrice * item.quantity;
        });
    }
    
    return total;
}

function generateCartPopupItems(cartItems) {
    if (!cartItems || cartItems.length === 0) {
        return '<p class="text-gray-500 text-center py-8">Your cart is empty</p>';
    }
    
    return cartItems.map((item) => {
        const product = item.product || {};
        const shop = product.shop || {};
        
        // Debug log for media
        console.log('Product media:', product.media);
        
        // Handle both array of URLs and array of objects
        let productImage = null;
        if (product.media) {
            if (Array.isArray(product.media) && product.media.length > 0) {
                const firstMedia = product.media[0];
                productImage = typeof firstMedia === 'object' ? (firstMedia.url || firstMedia) : firstMedia;
            } else if (typeof product.media === 'string') {
                // If media is a string (single URL)
                productImage = product.media;
            }
        }
        
        console.log('Final product image:', productImage); // Debug
        
        return `
            <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex gap-4">
            <!-- Product Image -->
                    <div class="flex-shrink-0">
                        ${productImage && productImage !== 'undefined' && productImage !== '' ? `
                            <img src="${productImage}" alt="${product.name || ''}" class="w-20 h-20 object-cover rounded-lg" onerror="this.parentElement.innerHTML='<div class=\\'w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center\\'><svg class=\\'w-8 h-8 text-gray-400\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\\'></path></svg></div>'">
                        ` : `
                            <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        `}
                    </div>
                    
                    <!-- Product Info -->
            <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 text-sm line-clamp-2 mb-1">${product.name || 'Unknown Product'}</h4>
                                ${shop.name ? `
                                    <p class="text-xs text-gray-500">Sold by: <a href="/shops/${shop.shop_slug || ''}" class="text-[#005366] font-medium hover:underline">${shop.name}</a></p>
                                ` : ''}
                            </div>
                            <button class="remove-cart-item ml-2 p-1 text-gray-400 hover:text-red-500 transition-colors" data-cart-item-id="${item.id}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Variants -->
                        ${item.selected_variant && item.selected_variant.attributes ? `
                            <div class="flex flex-wrap gap-1 mb-2">
                                ${Object.entries(item.selected_variant.attributes).map(([key, value]) => `
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        ${key}: ${value}
                                    </span>
                                `).join('')}
                </div>
                        ` : ''}
                
                <!-- Customizations -->
                ${item.customizations && Object.keys(item.customizations).length > 0 ? `
                    <div class="text-xs text-gray-600 mb-2">
                        ${Object.entries(item.customizations).map(([key, custom]) => 
                                    `<div>${key}: ${custom.value}${custom.price > 0 ? ` (+$${parseFloat(custom.price).toFixed(2)})` : ''}</div>`
                                ).join('')}
                    </div>
                ` : ''}
                
                <!-- Price and Quantity -->
                        <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center space-x-2">
                                <button class="decrease-quantity w-7 h-7 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors ${item.quantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''}" 
                                        data-cart-item-id="${item.id}" 
                                        data-new-quantity="${item.quantity - 1}"
                                        ${item.quantity <= 1 ? 'disabled' : ''}>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                                <span class="text-sm font-semibold min-w-[1.5rem] text-center" id="quantity-${item.id}">${item.quantity}</span>
                                <button class="increase-quantity w-7 h-7 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors" 
                                        data-cart-item-id="${item.id}" 
                                        data-new-quantity="${item.quantity + 1}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-[#005366]">$${calculateItemTotal(item).toFixed(2)}</p>
                                ${item.quantity > 1 ? `
                                    <p class="text-xs text-gray-500">$${parseFloat(item.price).toFixed(2)} each</p>
                                ` : ''}
                </div>
            </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function updateCartItemQuantity(e, cartItemId, newQuantity) {
    if (newQuantity < 1) {
        removeCartItemById(cartItemId);
        return;
    }
    
    console.log('Updating cart item:', cartItemId, 'to quantity:', newQuantity);
    
    // Show loading state
    const quantitySpan = document.getElementById(`quantity-${cartItemId}`);
    let originalText = '';
    if (quantitySpan) {
        originalText = quantitySpan.textContent;
        quantitySpan.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#005366] mx-auto"></div>';
    }
    
    fetch(`/api/cart/update/${cartItemId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ quantity: newQuantity })
    })
    .then(response => {
        console.log('Update response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Update response data:', data);
        if (data.success) {
            console.log('Quantity updated successfully');
            // Sync localStorage first
            syncLocalStorageWithBackend();
            // Then refresh popup content
            setTimeout(() => {
                refreshCartPopupContent();
            }, 100);
        } else {
            if (quantitySpan) quantitySpan.textContent = originalText;
            const errorMsg = data.message || 'An error occurred while updating quantity';
            showAlert({
                icon: 'error',
                title: 'Unable to Update',
                text: errorMsg,
                confirmButtonText: 'Close',
                confirmButtonColor: '#005366'
            });
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
        if (quantitySpan) quantitySpan.textContent = originalText;
        const errorMsg = error.message || 'An error occurred';
        showAlert({
            icon: 'error',
            title: 'Error',
            text: errorMsg,
            confirmButtonText: 'Close',
            confirmButtonColor: '#005366'
        });
    });
}

function removeCartItemById(cartItemId) {
    showAlert({
        icon: 'question',
        title: 'Confirm Removal',
        text: 'Are you sure you want to remove this product from your cart?',
        showCancelButton: true,
        confirmButtonText: 'Remove',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#E2150C',
        cancelButtonColor: '#6b7280',
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'px-6 py-3 rounded-lg',
            cancelButton: 'px-6 py-3 rounded-lg'
        }
    }).then((result) => {
        if (!result.isConfirmed) return;
        
        console.log('Removing cart item:', cartItemId);
        
        fetch(`/api/cart/remove/${cartItemId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            console.log('Remove response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Remove response data:', data);
            if (data.success) {
                console.log('Item removed successfully');
                // Sync localStorage
                syncLocalStorageWithBackend();
                // Refresh popup content
                setTimeout(() => {
                    refreshCartPopupContent();
                }, 100);
                // Show notification
                showCartSuccess('Product removed from cart');
            } else {
                const errorMsg = data.message || 'An error occurred while removing the product';
                showAlert({
                    icon: 'error',
                    title: 'Unable to Remove',
                    text: errorMsg,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#005366'
                });
            }
        })
        .catch(error => {
            console.error('Error removing item:', error);
            const errorMsg = error.message || 'An error occurred';
            showAlert({
                icon: 'error',
                title: 'Error',
                text: errorMsg,
                confirmButtonText: 'Close',
                confirmButtonColor: '#005366'
            });
        });
    });
}

function refreshCartPopupContent() {
    // Fetch latest cart data and update popup
    fetch('/api/cart/get', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.cart_items) {
            console.log('Refreshing popup with:', data.cart_items.length, 'items');
            
            // Update items container
            const cartItemsContainer = document.getElementById('cart-popup-items');
            if (cartItemsContainer) {
                cartItemsContainer.innerHTML = generateCartPopupItems(data.cart_items);
            }
            
            // Update totals
            const totalItems = data.cart_items.reduce((sum, item) => sum + item.quantity, 0);
            const summary = data.summary || {};
            
            // Update subtotal
            const subtotalElements = document.querySelectorAll('#cart-popup-overlay .space-y-2 .flex.justify-between');
            if (subtotalElements[0]) {
                const subtotalSpan = subtotalElements[0].querySelector('span:last-child');
                const subtotalLabel = subtotalElements[0].querySelector('span:first-child');
                if (subtotalSpan) subtotalSpan.textContent = `$${parseFloat(summary.subtotal || 0).toFixed(2)}`;
                if (subtotalLabel) subtotalLabel.textContent = `Subtotal (${totalItems} items)`;
            }
            
            // Update shipping with freeship logic
            const subtotal = parseFloat(summary.subtotal || 0);
            const qualifiesForFreeShipping = subtotal >= 100;
            const actualShipping = qualifiesForFreeShipping ? 0 : (summary.shipping || 0);
            
            console.log('Refresh popup freeship check:', {
                subtotal: subtotal,
                qualifiesForFreeShipping: qualifiesForFreeShipping,
                originalShipping: summary.shipping,
                actualShipping: actualShipping
            });
            
            // Update shipping display
            const popupShippingCost = document.getElementById('popupShippingCost');
            if (popupShippingCost) {
                if (qualifiesForFreeShipping) {
                    popupShippingCost.innerHTML = '<span class="text-green-600">FREE</span>';
                } else {
                    popupShippingCost.innerHTML = actualShipping === 0 ? 
                        '<span class="text-green-600">FREE</span>' : 
                        '$' + parseFloat(actualShipping).toFixed(2);
                }
            }
            
            // Also update the shipping span in subtotal elements if it exists
            if (subtotalElements[1]) {
                const shippingSpan = subtotalElements[1].querySelector('span:last-child');
                if (shippingSpan) {
                    if (qualifiesForFreeShipping) {
                        shippingSpan.className = 'text-green-600 font-semibold';
                        shippingSpan.innerHTML = '<span class="text-green-600">FREE</span>';
                    } else {
                        shippingSpan.className = 'font-semibold';
                        shippingSpan.textContent = `$${parseFloat(actualShipping).toFixed(2)}`;
                    }
                }
            }
            
            // Update shipping zone if available
            if (data.shipping_details && data.shipping_details.zone_name) {
                const zoneElement = document.getElementById('popupShippingZone');
                if (zoneElement) {
                    zoneElement.textContent = data.shipping_details.zone_name;
                }
            }
            
            // Update freeship messages
            const freeshipMessageContainer = document.querySelector('#cart-popup-overlay .text-xs.text-green-600.bg-green-50');
            const progressMessageContainer = document.querySelector('#cart-popup-overlay .text-xs.text-blue-600');
            
            if (qualifiesForFreeShipping) {
                // Show freeship success message
                if (!freeshipMessageContainer) {
                    const shippingRow = document.querySelector('#cart-popup-overlay .flex.justify-between.items-center.text-gray-600');
                    if (shippingRow && shippingRow.parentNode) {
                        const successMsg = document.createElement('div');
                        successMsg.className = 'text-xs text-green-600 bg-green-50 p-2 rounded';
                        successMsg.textContent = '🎉 You qualify for free shipping on orders $100+!';
                        shippingRow.parentNode.insertBefore(successMsg, shippingRow.nextSibling);
                    }
                }
                
                // Remove progress message if exists
                if (progressMessageContainer) {
                    progressMessageContainer.remove();
                }
            } else {
                // Remove freeship success message if exists
                if (freeshipMessageContainer) {
                    freeshipMessageContainer.remove();
                }
                
                // Update or add progress message
                const remainingAmount = (100 - subtotal).toFixed(2);
                if (progressMessageContainer) {
                    progressMessageContainer.textContent = `Add $${remainingAmount} more for free shipping!`;
                } else {
                    const shippingRow = document.querySelector('#cart-popup-overlay .flex.justify-between.items-center.text-gray-600');
                    if (shippingRow && shippingRow.parentNode) {
                        const progressMsg = document.createElement('div');
                        progressMsg.className = 'text-xs text-blue-600';
                        progressMsg.textContent = `Add $${remainingAmount} more for free shipping!`;
                        shippingRow.parentNode.insertBefore(progressMsg, shippingRow.nextSibling);
                    }
                }
            }
            
            // Update total with correct shipping cost (considering freeship)
            const totalElement = document.querySelector('#cart-popup-overlay .border-t.pt-3 span:last-child');
            if (totalElement) {
                const newTotal = subtotal + actualShipping;
                totalElement.textContent = `$${newTotal.toFixed(2)}`;
                console.log('Updated total with freeship:', newTotal);
            }
            
            // Update header cart count
            updateCartCount();
            
            // Re-setup shipping calculator and event delegation after refresh
            setTimeout(() => {
                setupPopupShippingCalculator();
                setupCartPopupEventDelegation();
            }, 100);
            
            // If cart is empty, close popup
            if (data.cart_items.length === 0) {
                closeCartPopup();
                showCartSuccess('Cart is empty');
            }
        }
    })
    .catch(error => {
        console.error('Failed to refresh popup:', error);
        // Don't close popup, just show error in items area
        const cartItemsContainer = document.getElementById('cart-popup-items');
        if (cartItemsContainer) {
            cartItemsContainer.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-red-600">Unable to update cart</p>
                    <p class="text-sm text-gray-500">${error.message}</p>
                    <button onclick="refreshCartPopupContent()" class="mt-2 text-[#005366] hover:underline">Try again</button>
        </div>
            `;
        }
    });
}

function closeCartPopup() {
    const overlay = document.getElementById('cart-popup-overlay');
    if (overlay) {
        const popup = overlay.querySelector('.cart-popup-content');
        if (popup) {
            popup.classList.add('cart-popup-exit');
            setTimeout(() => {
                overlay.remove();
            }, 200);
        } else {
            overlay.remove();
        }
    }
}

@php
    $crossSellData = $relatedProducts->map(function($product) {
        $media = $product->getEffectiveMedia();
        
        // Get image URL safely
        $imageUrl = null;
        if ($media && count($media) > 0) {
            if (is_string($media[0])) {
                $imageUrl = $media[0];
            } elseif (is_array($media[0])) {
                $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? null;
            }
        }
        
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->base_price,
            'image' => $imageUrl,
            'has_variants' => $product->variants()->count() > 0,
        ];
    })->toArray();
@endphp

function generateCrossSellProducts() {
    // Get related products from the page
    const relatedProducts = @json($crossSellData ?? []);
    
    if (!relatedProducts || relatedProducts.length === 0) {
        return '<p class="col-span-2 sm:col-span-4 text-center text-gray-500 py-4">No recommendations available</p>';
    }
    
    // Take first 4 related products for cross-sell
    const crossSellProducts = relatedProducts.slice(0, 4);
    
    return crossSellProducts.map(product => `
        <div class="border border-gray-200 rounded-lg p-3 hover:border-[#005366] transition-colors cursor-pointer cross-sell-product relative" 
             data-product-id="${product.id}"
             data-product-name="${product.name}"
             data-product-price="${product.price}"
             data-product-image="${product.image || ''}"
             data-product-slug="${product.slug}"
             data-has-variants="${product.has_variants}">
            ${product.image && product.image !== 'undefined' && product.image !== '' ? `
                <div class="relative">
                    <img src="${product.image}" 
                     alt="${product.name}" 
                         class="w-full h-24 object-cover rounded-md mb-2"
                         onerror="this.parentElement.innerHTML='<div class=\\'w-full h-24 bg-gray-200 rounded-md mb-2 flex items-center justify-center\\'><svg class=\\'w-8 h-8 text-gray-400\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\\'></path></svg></div>'">
                    ${product.has_variants ? `
                        <div class="absolute top-1 left-1 bg-blue-500 text-white text-[10px] px-1.5 py-0.5 rounded-full font-medium flex items-center space-x-0.5">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                            </svg>
                            <span>Options</span>
                        </div>
                    ` : ''}
                </div>
            ` : `
                <div class="w-full h-24 bg-gray-200 rounded-md mb-2 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            `}
            <h4 class="font-medium text-gray-900 text-xs line-clamp-2 mb-1">${product.name}</h4>
            <div class="flex items-center justify-between">
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-[#005366]">$${product.price}</span>
                    ${product.originalPrice && product.originalPrice > product.price ? `
                        <span class="text-xs text-gray-500 line-through">$${product.originalPrice}</span>
                    ` : ''}
                </div>
                <button class="cross-sell-add-btn bg-[#005366] text-white text-xs px-3 py-1 rounded hover:bg-[#003d4d] transition-colors flex items-center space-x-1">
                    ${product.has_variants ? `
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>Select</span>
                    ` : `
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add</span>
                    `}
                </button>
            </div>
        </div>
    `).join('');
}

// Handle cross-sell product click
function handleCrossSellClick(productId, productName, productPrice, productImage, productSlug, hasVariants) {
    if (hasVariants) {
        // If product has variants, redirect to product page to select
        window.location.href = `/products/${productSlug}`;
    } else {
        // If no variants, add directly to cart
        addCrossSellToCart(productId, productName, productPrice, productImage);
    }
}

function addCrossSellToCart(productId, productName, productPrice, productImage) {
    const crossSellProduct = {
        id: productId,
        name: productName,
        price: productPrice,
        image: productImage && productImage !== 'undefined' && productImage !== '' ? productImage : null,
        quantity: 1,
        addedAt: Date.now()
    };
    
    // Add to localStorage
    addToLocalCart(crossSellProduct);
    
    // Sync with backend
    fetch('/api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            id: productId,
            quantity: 1,
            price: productPrice,
            selectedVariant: null,
            customizations: null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            syncLocalStorageWithBackend();
            // Refresh popup content
            refreshCartPopupContent();
            showCartSuccess('Product added to cart successfully!');
        }
    })
    .catch(error => {
        console.error('Failed to add cross-sell product:', error);
        showAlert({
            icon: 'error',
            title: 'Unable to Add',
            text: 'An error occurred while adding the product to cart',
            confirmButtonText: 'Close',
            confirmButtonColor: '#005366'
        });
    });
}


// Legacy functions for backward compatibility - redirecting to new functions
function updateCartQuantity(index, newQuantity) {
    console.warn('Legacy updateCartQuantity called with index:', index);
    // This should not be used anymore as we're using backend IDs
    // Refresh the popup to get correct IDs
    refreshCartPopupContent();
}

function removeCartItem(index) {
    console.warn('Legacy removeCartItem called with index:', index);
    // This should not be used anymore as we're using backend IDs
    // Refresh the popup to get correct IDs
    refreshCartPopupContent();
}

function syncLocalStorageWithBackend() {
    // Fetch current cart from backend
    fetch('/api/cart/get', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.cart_items) {
            // Convert backend cart items to localStorage format with full product info
            const backendCart = data.cart_items.map(item => {
                const product = item.product || {};
                const shop = product.shop || {};
                
                // Get image from media
                let productImage = null;
                if (product.media) {
                    if (Array.isArray(product.media) && product.media.length > 0) {
                        const firstMedia = product.media[0];
                        productImage = typeof firstMedia === 'object' ? (firstMedia.url || firstMedia) : firstMedia;
                    } else if (typeof product.media === 'string') {
                        productImage = product.media;
                    }
                }
                
                return {
                    cart_item_id: item.id, // Backend cart item ID
                id: item.product_id,
                    name: product.name || 'Unknown Product',
                    slug: product.slug || '',
                price: parseFloat(item.price),
                    image: productImage,
                    shop: shop.name || 'Unknown Shop',
                    shop_slug: shop.shop_slug || '',
                quantity: item.quantity,
                selectedVariant: item.selected_variant,
                customizations: item.customizations,
                addedAt: Date.now()
                };
            });
            
            // Update localStorage to match backend
            localStorage.setItem('cart', JSON.stringify(backendCart));
            
            // Update header count
            updateCartCount();
            
            console.log('LocalStorage synced with backend:', backendCart);
        }
    })
    .catch(error => {
        console.error('Failed to sync with backend:', error);
    });
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    // Try to sync localStorage with backend on page load
    syncLocalStorageWithBackend();
});

// Customization Functions
function toggleCustomization() {
    const checkbox = document.getElementById('enable-customization');
    const container = document.getElementById('customization-container');
    const priceDisplay = document.getElementById('customization-price-display');
    
    if (checkbox.checked) {
        container.classList.remove('hidden');
        updateCustomizationPrice();
    } else {
        container.classList.add('hidden');
        priceDisplay.classList.add('hidden');
        
        // Clear all customization inputs
        const inputs = container.querySelectorAll('.customization-input');
        inputs.forEach(input => {
            if (input.type === 'radio' || input.type === 'checkbox') {
                input.checked = false;
            } else {
                input.value = '';
            }
        });
        
        updateCustomizationPrice();
    }
}

function updateCustomizationPrice() {
    const checkbox = document.getElementById('enable-customization');
    
    // If customization is not enabled, don't calculate
    if (!checkbox || !checkbox.checked) {
        return;
    }
    
    let customizationTotal = 0;
    
    // Get all customization inputs
    const inputs = document.querySelectorAll('.customization-input');
    
    inputs.forEach(input => {
        if (input.type === 'radio') {
            // For radio buttons, only count if checked
            if (input.checked) {
                const price = parseFloat(input.dataset.price) || 0;
                customizationTotal += price;
            }
        } else if (input.type === 'checkbox') {
            // For checkboxes, only count if checked
            if (input.checked) {
                const price = parseFloat(input.dataset.price) || 0;
                customizationTotal += price;
            }
        } else {
            // For text/textarea inputs, count if has value
            if (input.value.trim() !== '') {
                const price = parseFloat(input.dataset.price) || 0;
                customizationTotal += price;
            }
        }
    });
    
    // Update display
    const basePriceElement = document.getElementById('base-price');
    const basePrice = parseFloat(basePriceElement.dataset.price) || 0;
    const totalPrice = basePrice + customizationTotal;
    
    const customizationPriceElement = document.getElementById('customization-price');
    const totalPriceElement = document.getElementById('total-price');
    const priceDisplay = document.getElementById('customization-price-display');
    
    if (customizationTotal > 0) {
        priceDisplay.classList.remove('hidden');
        customizationPriceElement.textContent = '+$' + customizationTotal.toFixed(2);
        totalPriceElement.textContent = '$' + totalPrice.toFixed(2);
    } else {
        priceDisplay.classList.add('hidden');
    }
}

// Size Guide Modal Functions
let currentGender = 'male';
let currentUnit = 'cm';

// Size data for different product types and genders
const sizeData = {
    'baseball-jackets': {
        male: {
            'LENGTH': { 'S': 66, 'M': 69, 'L': 71, 'XL': 74, '2XL': 76, '3XL': 79, '4XL': 81, '5XL': 83 },
            'BUST': { 'S': 102, 'M': 112, 'L': 122, 'XL': 132, '2XL': 142, '3XL': 152, '4XL': 158, '5XL': 164 },
            'SLEEVE': { 'S': 62, 'M': 63, 'L': 65, 'XL': 66, '2XL': 67, '3XL': 68, '4XL': 69, '5XL': 70 }
        },
        female: {
            'LENGTH': { 'S': 60, 'M': 63, 'L': 66, 'XL': 69, '2XL': 71, '3XL': 74, '4XL': 76, '5XL': 78 },
            'BUST': { 'S': 86, 'M': 91, 'L': 97, 'XL': 102, '2XL': 107, '3XL': 112, '4XL': 117, '5XL': 122 },
            'SLEEVE': { 'S': 58, 'M': 59, 'L': 60, 'XL': 61, '2XL': 62, '3XL': 63, '4XL': 64, '5XL': 65 }
        },
        youth: {
            'LENGTH': { 'S': 50, 'M': 53, 'L': 56, 'XL': 59, '2XL': 62, '3XL': 65, '4XL': 68, '5XL': 71 },
            'BUST': { 'S': 66, 'M': 71, 'L': 76, 'XL': 81, '2XL': 86, '3XL': 91, '4XL': 96, '5XL': 102 },
            'SLEEVE': { 'S': 45, 'M': 47, 'L': 49, 'XL': 51, '2XL': 53, '3XL': 55, '4XL': 57, '5XL': 59 }
        },
        unisex: {
            'LENGTH': { 'S': 66, 'M': 69, 'L': 71, 'XL': 74, '2XL': 76, '3XL': 79, '4XL': 81, '5XL': 83 },
            'BUST': { 'S': 102, 'M': 112, 'L': 122, 'XL': 132, '2XL': 142, '3XL': 152, '4XL': 158, '5XL': 164 },
            'SLEEVE': { 'S': 62, 'M': 63, 'L': 65, 'XL': 66, '2XL': 67, '3XL': 68, '4XL': 69, '5XL': 70 }
        },
        kids: {
            'LENGTH': { 'S': 40, 'M': 43, 'L': 46, 'XL': 49, '2XL': 52, '3XL': 55, '4XL': 58, '5XL': 61 },
            'BUST': { 'S': 56, 'M': 61, 'L': 66, 'XL': 71, '2XL': 76, '3XL': 81, '4XL': 86, '5XL': 91 },
            'SLEEVE': { 'S': 35, 'M': 37, 'L': 39, 'XL': 41, '2XL': 43, '3XL': 45, '4XL': 47, '5XL': 49 }
        }
    },
    't-shirts': {
        male: {
            'LENGTH': { 'S': 70, 'M': 72, 'L': 74, 'XL': 76, '2XL': 78, '3XL': 80, '4XL': 82, '5XL': 84 },
            'BUST': { 'S': 96, 'M': 101, 'L': 106, 'XL': 111, '2XL': 116, '3XL': 121, '4XL': 126, '5XL': 131 },
            'SLEEVE': { 'S': 20, 'M': 21, 'L': 22, 'XL': 23, '2XL': 24, '3XL': 25, '4XL': 26, '5XL': 27 }
        },
        female: {
            'LENGTH': { 'S': 64, 'M': 66, 'L': 68, 'XL': 70, '2XL': 72, '3XL': 74, '4XL': 76, '5XL': 78 },
            'BUST': { 'S': 86, 'M': 91, 'L': 97, 'XL': 102, '2XL': 107, '3XL': 112, '4XL': 117, '5XL': 122 },
            'SLEEVE': { 'S': 18, 'M': 19, 'L': 20, 'XL': 21, '2XL': 22, '3XL': 23, '4XL': 24, '5XL': 25 }
        },
        youth: {
            'LENGTH': { 'S': 50, 'M': 53, 'L': 56, 'XL': 59, '2XL': 62, '3XL': 65, '4XL': 68, '5XL': 71 },
            'BUST': { 'S': 66, 'M': 71, 'L': 76, 'XL': 81, '2XL': 86, '3XL': 91, '4XL': 96, '5XL': 102 },
            'SLEEVE': { 'S': 15, 'M': 16, 'L': 17, 'XL': 18, '2XL': 19, '3XL': 20, '4XL': 21, '5XL': 22 }
        },
        unisex: {
            'LENGTH': { 'S': 70, 'M': 72, 'L': 74, 'XL': 76, '2XL': 78, '3XL': 80, '4XL': 82, '5XL': 84 },
            'BUST': { 'S': 96, 'M': 101, 'L': 106, 'XL': 111, '2XL': 116, '3XL': 121, '4XL': 126, '5XL': 131 },
            'SLEEVE': { 'S': 20, 'M': 21, 'L': 22, 'XL': 23, '2XL': 24, '3XL': 25, '4XL': 26, '5XL': 27 }
        },
        kids: {
            'LENGTH': { 'S': 40, 'M': 43, 'L': 46, 'XL': 49, '2XL': 52, '3XL': 55, '4XL': 58, '5XL': 61 },
            'BUST': { 'S': 56, 'M': 61, 'L': 66, 'XL': 71, '2XL': 76, '3XL': 81, '4XL': 86, '5XL': 91 },
            'SLEEVE': { 'S': 15, 'M': 16, 'L': 17, 'XL': 18, '2XL': 19, '3XL': 20, '4XL': 21, '5XL': 22 }
        }
    },
    'hoodies': {
        male: {
            'LENGTH': { 'S': 68, 'M': 70, 'L': 72, 'XL': 74, '2XL': 76, '3XL': 78, '4XL': 80, '5XL': 82 },
            'BUST': { 'S': 104, 'M': 109, 'L': 114, 'XL': 119, '2XL': 124, '3XL': 129, '4XL': 134, '5XL': 139 },
            'SLEEVE': { 'S': 64, 'M': 65, 'L': 66, 'XL': 67, '2XL': 68, '3XL': 69, '4XL': 70, '5XL': 71 }
        },
        female: {
            'LENGTH': { 'S': 62, 'M': 64, 'L': 66, 'XL': 68, '2XL': 70, '3XL': 72, '4XL': 74, '5XL': 76 },
            'BUST': { 'S': 88, 'M': 93, 'L': 98, 'XL': 103, '2XL': 108, '3XL': 113, '4XL': 118, '5XL': 123 },
            'SLEEVE': { 'S': 60, 'M': 61, 'L': 62, 'XL': 63, '2XL': 64, '3XL': 65, '4XL': 66, '5XL': 67 }
        },
        youth: {
            'LENGTH': { 'S': 48, 'M': 51, 'L': 54, 'XL': 57, '2XL': 60, '3XL': 63, '4XL': 66, '5XL': 69 },
            'BUST': { 'S': 68, 'M': 73, 'L': 78, 'XL': 83, '2XL': 88, '3XL': 93, '4XL': 98, '5XL': 103 },
            'SLEEVE': { 'S': 47, 'M': 49, 'L': 51, 'XL': 53, '2XL': 55, '3XL': 57, '4XL': 59, '5XL': 61 }
        },
        unisex: {
            'LENGTH': { 'S': 68, 'M': 70, 'L': 72, 'XL': 74, '2XL': 76, '3XL': 78, '4XL': 80, '5XL': 82 },
            'BUST': { 'S': 104, 'M': 109, 'L': 114, 'XL': 119, '2XL': 124, '3XL': 129, '4XL': 134, '5XL': 139 },
            'SLEEVE': { 'S': 64, 'M': 65, 'L': 66, 'XL': 67, '2XL': 68, '3XL': 69, '4XL': 70, '5XL': 71 }
        },
        kids: {
            'LENGTH': { 'S': 38, 'M': 41, 'L': 44, 'XL': 47, '2XL': 50, '3XL': 53, '4XL': 56, '5XL': 59 },
            'BUST': { 'S': 58, 'M': 63, 'L': 68, 'XL': 73, '2XL': 78, '3XL': 83, '4XL': 88, '5XL': 93 },
            'SLEEVE': { 'S': 37, 'M': 39, 'L': 41, 'XL': 43, '2XL': 45, '3XL': 47, '4XL': 49, '5XL': 51 }
        }
    }
};

function openSizeGuide() {
    const modal = document.getElementById('size-guide-modal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    updateSizeTable();
}

function closeSizeGuide() {
    const modal = document.getElementById('size-guide-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function selectGender(gender) {
    currentGender = gender;
    
    // Update button styles
    document.querySelectorAll('[id^="gender-"]').forEach(btn => {
        btn.classList.remove('bg-[#005366]', 'text-white');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    
    document.getElementById(`gender-${gender}`).classList.remove('bg-gray-100', 'text-gray-700');
    document.getElementById(`gender-${gender}`).classList.add('bg-[#005366]', 'text-white');
    
    updateSizeTable();
}

function selectUnit(unit) {
    currentUnit = unit;
    
    // Update button styles
    document.querySelectorAll('[id^="unit-"]').forEach(btn => {
        btn.classList.remove('bg-[#005366]', 'text-white');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    
    document.getElementById(`unit-${unit}`).classList.remove('bg-gray-100', 'text-gray-700');
    document.getElementById(`unit-${unit}`).classList.add('bg-[#005366]', 'text-white');
    
    updateSizeTable();
}

function updateSizeTable() {
    const productType = document.getElementById('product-type-selector').value;
    const tableBody = document.getElementById('size-table-body');
    
    if (!sizeData[productType] || !sizeData[productType][currentGender]) {
        tableBody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-gray-500">No size data available for this selection</td></tr>';
        return;
    }
    
    const measurements = sizeData[productType][currentGender];
    const sizes = ['S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'];
    
    let tableHTML = '';
    
    Object.entries(measurements).forEach(([measurement, values]) => {
        tableHTML += '<tr class="border-b border-gray-100">';
        tableHTML += `<td class="py-2 font-medium text-gray-700">${measurement}:</td>`;
        
        sizes.forEach(size => {
            let value = values[size] || '-';
            if (value !== '-' && currentUnit === 'inches') {
                // Convert cm to inches (1 cm = 0.393701 inches)
                value = Math.round(value * 0.393701);
            }
            tableHTML += `<td class="text-center py-2 text-gray-600">${value}</td>`;
        });
        
        tableHTML += '</tr>';
    });
    
    tableBody.innerHTML = tableHTML;
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSizeGuide();
    }
});

// Close modal on overlay click
document.getElementById('size-guide-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSizeGuide();
    }
});
</script>
<!-- Size Guide Modal -->
<div id="size-guide-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Size & Fit Info</h2>
            <button onclick="closeSizeGuide()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Content -->
        <div class="p-6">
            <!-- Introduction Text -->
            <p class="text-gray-700 mb-6">
                If you're in between sizes, order a size up as our items can shrink up to half a size in the wash.
            </p>
            
            <!-- Gender/Age Selection Tabs -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button onclick="selectGender('male')" id="gender-male" class="px-4 py-2 rounded-lg font-medium transition-colors bg-[#005366] text-white">
                    Male
                </button>
                <button onclick="selectGender('female')" id="gender-female" class="px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Female
                </button>
                <button onclick="selectGender('youth')" id="gender-youth" class="px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Youth
                </button>
                <button onclick="selectGender('unisex')" id="gender-unisex" class="px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Unisex
                </button>
                <button onclick="selectGender('kids')" id="gender-kids" class="px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700 hover:bg-gray-200">
                    Kids
                </button>
            </div>
            
            <!-- Product Type Dropdown -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Product Type</label>
                <select id="product-type-selector" onchange="updateSizeTable()" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366] focus:border-transparent">
                    <option value="baseball-jackets">Baseball Jackets</option>
                    <option value="t-shirts">T-Shirts</option>
                    <option value="hoodies">Hoodies</option>
                    <option value="tank-tops">Tank Tops</option>
                    <option value="long-sleeve">Long Sleeve</option>
                </select>
            </div>
            
            <!-- Size Table -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Measurements</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-2 font-medium text-gray-700">Size</th>
                                <th class="text-center py-2 font-medium text-gray-700">S</th>
                                <th class="text-center py-2 font-medium text-gray-700">M</th>
                                <th class="text-center py-2 font-medium text-gray-700">L</th>
                                <th class="text-center py-2 font-medium text-gray-700">XL</th>
                                <th class="text-center py-2 font-medium text-gray-700">2XL</th>
                                <th class="text-center py-2 font-medium text-gray-700">3XL</th>
                                <th class="text-center py-2 font-medium text-gray-700">4XL</th>
                                <th class="text-center py-2 font-medium text-gray-700">5XL</th>
                            </tr>
                        </thead>
                        <tbody id="size-table-body">
                            <!-- Table content will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Unit Selection -->
            <div class="flex items-center justify-end space-x-2">
                <button onclick="selectUnit('cm')" id="unit-cm" class="px-4 py-2 rounded-lg font-medium transition-colors bg-[#005366] text-white">
                    cm
                </button>
                <button onclick="selectUnit('inches')" id="unit-inches" class="px-4 py-2 rounded-lg font-medium transition-colors bg-gray-100 text-gray-700 hover:bg-gray-200">
                    inches
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Cart Popup Shipping Calculator
function setupPopupShippingCalculator() {
    const popupShippingCountry = document.getElementById('popupShippingCountry');
    const popupShippingCost = document.getElementById('popupShippingCost');
    const popupShippingZone = document.getElementById('popupShippingZone');
    
    console.log('Setting up popup shipping calculator...', {
        country: popupShippingCountry,
        cost: popupShippingCost,
        zone: popupShippingZone
    });
    
    if (popupShippingCountry) {
        // Remove existing event listeners to avoid duplicates
        popupShippingCountry.removeEventListener('change', handlePopupCountryChange);
        
        // Add new event listener
        popupShippingCountry.addEventListener('change', handlePopupCountryChange);
    }
}

// Separate function for handling country change to avoid duplicate listeners
async function handlePopupCountryChange() {
    const country = this.value;
    const popupShippingCost = document.getElementById('popupShippingCost');
    const popupShippingZone = document.getElementById('popupShippingZone');
    
    console.log('Country changed to:', country);
    
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
        console.log('Shipping calculation response:', data);
        
        if (data.success && data.shipping) {
            const shipping = parseFloat(data.shipping.total_shipping || 0);
            console.log('New shipping cost:', shipping);
            
            // Get current subtotal to check for freeship qualification
            const subtotalElements = document.querySelectorAll('#cart-popup-overlay .space-y-2 .flex.justify-between');
            let subtotal = 0;
            
            subtotalElements.forEach(element => {
                const text = element.textContent;
                if (text.includes('items')) {
                    const subtotalSpan = element.querySelector('span:last-child');
                    if (subtotalSpan) {
                        subtotal = parseFloat(subtotalSpan.textContent.replace('$', '').replace(',', '')) || 0;
                    }
                }
            });
            
            const qualifiesForFreeShipping = subtotal >= 100;
            const displayShipping = qualifiesForFreeShipping ? 0 : shipping;
            
            console.log('Freeship check:', {
                subtotal: subtotal,
                qualifiesForFreeShipping: qualifiesForFreeShipping,
                originalShipping: shipping,
                displayShipping: displayShipping
            });
            
            // Update shipping cost display
            if (popupShippingCost) {
                if (qualifiesForFreeShipping) {
                    popupShippingCost.innerHTML = '<span class="text-green-600">FREE</span>';
                } else {
                    popupShippingCost.innerHTML = displayShipping === 0 ? 
                        '<span class="text-green-600">FREE</span>' : 
                        '$' + displayShipping.toFixed(2);
                }
                console.log('Updated shipping cost element');
            }
            
            // Update zone display
            if (popupShippingZone) {
                popupShippingZone.textContent = data.shipping.zone_name || country;
                console.log('Updated zone to:', data.shipping.zone_name);
            }
            
            // Update total in popup using the actual shipping cost (considering freeship)
            updatePopupTotal(displayShipping);
            
            // Show success feedback
            console.log('Shipping updated successfully:', {
                country: country,
                shipping: shipping,
                zone: data.shipping.zone_name
            });
        } else {
            console.error('Shipping calculation failed:', data);
            // Show error to user
            if (popupShippingCost) {
                popupShippingCost.innerHTML = '<span class="text-red-600">Error</span>';
            }
        }
    } catch (error) {
        console.error('Popup shipping calculation error:', error);
        // Show error to user
        if (popupShippingCost) {
            popupShippingCost.innerHTML = '<span class="text-red-600">Error</span>';
        }
    }
}

function updatePopupTotal(newShipping) {
    // Get current subtotal from popup - look for the subtotal line specifically
    const subtotalElements = document.querySelectorAll('#cart-popup-overlay .space-y-2 .flex.justify-between');
    let subtotal = 0;
    
    // Find the subtotal element (should be the first one with "items" text)
    subtotalElements.forEach(element => {
        const text = element.textContent;
        if (text.includes('items')) {
            const subtotalSpan = element.querySelector('span:last-child');
            if (subtotalSpan) {
                subtotal = parseFloat(subtotalSpan.textContent.replace('$', '').replace(',', '')) || 0;
                console.log('Found subtotal:', subtotal);
            }
        }
    });
    
    // Check if order qualifies for free shipping (>= $100)
    const qualifiesForFreeShipping = subtotal >= 100;
    const actualShipping = qualifiesForFreeShipping ? 0 : newShipping;
    const newTotal = subtotal + actualShipping;
    
    console.log('Calculating new total with freeship logic:', {
        subtotal: subtotal,
        qualifiesForFreeShipping: qualifiesForFreeShipping,
        originalShipping: newShipping,
        actualShipping: actualShipping,
        newTotal: newTotal
    });
    
    // Update shipping cost display to show FREE if qualified
    const popupShippingCost = document.getElementById('popupShippingCost');
    if (popupShippingCost) {
        if (qualifiesForFreeShipping) {
            popupShippingCost.innerHTML = '<span class="text-green-600">FREE</span>';
        } else {
            popupShippingCost.innerHTML = actualShipping === 0 ? 
                '<span class="text-green-600">FREE</span>' : 
                '$' + actualShipping.toFixed(2);
        }
    }
    
    // Update total display
    const totalElement = document.querySelector('#cart-popup-overlay .border-t.pt-3 span:last-child');
    if (totalElement) {
        totalElement.textContent = '$' + newTotal.toFixed(2);
        console.log('Updated total to:', '$' + newTotal.toFixed(2));
    } else {
        console.error('Total element not found');
    }
}

// Note: setupPopupShippingCalculator is called automatically in renderCartPopup

// Buy Now Function - Add to cart and go to checkout
function buyNow() {
    // Validate required customizations first
    const validation = validateRequiredCustomizations();
    if (!validation.isValid) {
        const message = validation.needToEnableCustomization 
            ? `<div class="text-left">
                    <p class="mb-3 text-gray-600">This product requires personalization. Please enable "Add Personalization" and fill in:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700">
                        ${validation.missingFields.map(field => `<li>${field}</li>`).join('')}
                    </ul>
                </div>`
            : `<div class="text-left">
                    <p class="mb-3 text-gray-600">Please fill in all required personalization information:</p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700">
                        ${validation.missingFields.map(field => `<li>${field}</li>`).join('')}
                    </ul>
                </div>`;
        
        showAlert({
            icon: 'warning',
            title: 'Missing Information',
            html: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-3 rounded-lg'
            }
        });
        
        // Scroll to customization section and auto-enable if needed
        if (validation.needToEnableCustomization) {
            const enableCheckbox = document.getElementById('enable-customization');
            if (enableCheckbox) {
                enableCheckbox.checked = true;
                toggleCustomization();
            }
        }
        
        const customizationContainer = document.getElementById('customization-container');
        if (customizationContainer) {
            setTimeout(() => {
                customizationContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
        return;
    }
    
    // Get selected variant and customizations
    const selectedVariant = getSelectedVariant();
    
    // Check if variant is out of stock
    if (selectedVariant && selectedVariant.quantity !== null && selectedVariant.quantity <= 0) {
        showAlert({
            icon: 'error',
            title: 'Out of Stock',
            text: 'This product is currently out of stock. Please choose another product.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#005366',
            customClass: {
                popup: 'rounded-xl',
                confirmButton: 'px-6 py-3 rounded-lg'
            }
        });
        return;
    }
    
    const variantPrice = selectedVariant && selectedVariant.price ? selectedVariant.price : {{ $product->base_price }};
    
    // Get product data
    const productData = {
        id: {{ $product->id }},
        name: '{{ addslashes($product->name) }}',
        slug: '{{ $product->slug }}',
        price: variantPrice,
        image: '@php
            if ($media && count($media) > 0) {
                if (is_string($media[0])) {
                    echo $media[0];
                } elseif (is_array($media[0])) {
                    echo $media[0]["url"] ?? $media[0]["path"] ?? reset($media[0]) ?? "";
                }
            }
        @endphp',
        shop: '{{ $product->shop->name ?? "Unknown Shop" }}',
        quantity: 1,
        selectedVariant: selectedVariant,
        customizations: getSelectedCustomizations(),
        addedAt: Date.now()
    };
    
    // Add to localStorage
    addToLocalCart(productData);
    
    // Track Facebook Pixel AddToCart
    if (typeof fbq !== 'undefined') {
        fbq('track', 'AddToCart', {
            content_name: productData.name,
            content_ids: [productData.id],
            content_type: 'product',
            value: productData.price,
            currency: 'USD'
        });
    }
    
    // Sync with backend
    syncCartToBackend(productData)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                syncLocalStorageWithBackend();
            }
        })
        .catch(error => {
            console.log('Backend sync failed, proceeding anyway:', error);
        })
        .finally(() => {
            // Track InitiateCheckout
            if (typeof fbq !== 'undefined') {
                fbq('track', 'InitiateCheckout', {
                    content_ids: [productData.id],
                    content_type: 'product',
                    value: productData.price,
                    currency: 'USD',
                    num_items: 1
                });
                
                console.log('✅ Facebook Pixel: Buy Now - AddToCart & InitiateCheckout tracked');
            }
            
            // Redirect to checkout
            window.location.href = '{{ route("checkout.index") }}';
        });
}
</script>

@endsection
