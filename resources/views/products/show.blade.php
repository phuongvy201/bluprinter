@extends('layouts.app')

@section('title', $product->name)

@section('content')
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
                        $mediaUrl = is_array($mediaItem) ? $mediaItem['url'] : $mediaItem;
                        $allImages[] = $mediaUrl;
                    }
                }
                
                // Add template images (if different from product images)
                if($product->template && $product->template->media) {
                    $templateMedia = is_array($product->template->media) ? $product->template->media : json_decode($product->template->media, true);
                    $templateMediaUrls = collect($templateMedia)->map(function($item) {
                        return is_array($item) ? $item['url'] : $item;
                    })->toArray();
                    
                    // Only add template images that are not already in product images
                    foreach($templateMediaUrls as $templateUrl) {
                        if(!in_array($templateUrl, $allImages)) {
                            $allImages[] = $templateUrl;
                        }
                    }
                }
            @endphp
            
            <!-- Main Image with Enhanced Effects -->
            <div class="aspect-square bg-white rounded-xl shadow-lg overflow-hidden relative group" id="image-container">
                @if($media && count($media) > 0)
                    <img src="{{ is_array($media[0]) ? $media[0]['url'] : $media[0] }}" 
                         alt="{{ $product->name }}" 
                         id="main-image"
                         class="w-full h-full object-cover">
                    
                    <!-- Zoom Overlay -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                        <div class="opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-75 group-hover:scale-100">
                            <div class="bg-white bg-opacity-90 rounded-full p-3 shadow-lg zoom-icon">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Counter Badge -->
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
                                <button onclick="changeMainImage('{{ $imageUrl }}', {{ $index }})" 
                                        class="flex-shrink-0 w-16 h-16 bg-white rounded-lg shadow-sm overflow-hidden border-2 {{ $index === 0 ? 'border-[#005366]' : 'border-gray-200' }} hover:border-[#005366] transition-colors group">
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $product->name }} - Image {{ $index + 1 }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
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
                                            @endphp
                                            @if($relatedMedia && count($relatedMedia) > 0)
                                                <img src="{{ is_array($relatedMedia[0]) ? $relatedMedia[0]['url'] : $relatedMedia[0] }}" 
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
                                <a href="#" class="text-sm text-red-600 hover:underline">
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
                                    <p class="text-sm text-gray-600 mb-3">{{ $customization['description'] }}</p>
                                @endif
                                
                                @if(isset($customization['instructions']) && !empty($customization['instructions']))
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                        <div class="flex items-start space-x-2">
                                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-xs text-blue-800">{{ $customization['instructions'] }}</p>
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
                                                        <p class="text-xs text-gray-500 mt-1">{{ $option['description'] }}</p>
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
                                        <a href="#" class="text-sm text-[#005366] hover:underline flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            View Size Guide
                                        </a>
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
                    <button id="add-to-cart-btn" onclick="addToCart()" class="flex-1 bg-[#005366] hover:bg-[#003d4d] text-white font-bold py-4 px-6 rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg id="cart-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                        </svg>
                        <span id="cart-text">Add to Cart</span>
                        <div id="cart-loading" class="hidden">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                        </div>
                    </button>
                    <button class="flex-1 bg-[#E2150C] hover:bg-[#c0120a] text-white font-bold py-4 px-6 rounded-xl transition-colors duration-200">
                        Buy Now
                    </button>
                </div>

                <!-- Wishlist Button -->
                <button class="w-full mt-4 border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-3 px-6 rounded-xl transition-colors duration-200 flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span>Add to Wishlist</span>
                </button>

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
                        <button class="bg-[#005366] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#003d4d] transition-colors flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                            <span>Customize</span>
                        </button>
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
                                Eligible for <span class="text-orange-600 font-medium">Refund</span> or <span class="text-orange-600 font-medium">Return and Replacement</span> within 30 days from the date of delivery
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
                                <a href="#" class="text-[#005366] hover:underline text-sm">Submit a ticket</a>
                                <span class="text-gray-300">|</span>
                                <a href="#" class="text-[#005366] hover:underline text-sm">Report Product</a>
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
                        // Replace &nbsp; with regular spaces
                        $description = str_replace('&nbsp;', ' ', $description);
                        // Replace multiple hyphens with section breaks
                        $description = preg_replace('/-{3,}/', "\n\n", $description);
                        // Clean up extra spaces
                        $description = preg_replace('/\s+/', ' ', $description);
                        
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
                        @endphp
                        @if($relatedMedia && count($relatedMedia) > 0)
                            <img src="{{ is_array($relatedMedia[0]) ? $relatedMedia[0]['url'] : $relatedMedia[0] }}" 
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

<!-- Recently Viewed Products -->
<div class="bg-white py-16 border-t border-gray-200 relative z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Recently Viewed</h2>
            <div class="flex items-center space-x-2 text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm">Recently viewed products</span>
            </div>
        </div>
        
        <!-- Recently Viewed Container with Navigation -->
        <div class="relative z-50" id="recently-viewed-wrapper">
            <!-- Navigation Buttons -->
            <button id="recentlyViewedPrevBtn" 
                    onclick="scrollRecentlyViewed('prev')"
                    class="hidden absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-60 bg-white rounded-full p-3 shadow-xl hover:bg-gray-50 transition-all border border-gray-200 hover:border-[#005366] opacity-0 disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            
            <button id="recentlyViewedNextBtn" 
                    onclick="scrollRecentlyViewed('next')"
                    class="hidden absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-60 bg-white rounded-full p-3 shadow-xl hover:bg-gray-50 transition-all border border-gray-200 hover:border-[#005366] disabled:opacity-30 disabled:cursor-not-allowed">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            
            <!-- Products Container -->
            <div id="recentlyViewedContainer" class="overflow-hidden group relative z-50">
                <div id="recently-viewed-container" class="flex gap-3 lg:gap-6 transition-transform duration-300 overflow-hidden relative z-50">
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
        
        <!-- Main Image -->
        <div class="flex items-center justify-center h-full">
            <img id="modal-main-image" src="" alt="" class="max-w-full max-h-full object-contain">
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
                    <button onclick="selectModalImage('{{ $imageUrl }}', {{ $index }})" 
                            class="w-12 h-12 rounded overflow-hidden border-2 border-transparent hover:border-white transition-colors">
                        <img src="{{ $imageUrl }}" alt="" class="w-full h-full object-cover">
                    </button>
                @endforeach
        </div>
    </div>
        
        <!-- Image Counter -->
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

/* Hide scrollbar for Recently Viewed on all devices */
#recently-viewed-container {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
#recently-viewed-container::-webkit-scrollbar {
    display: none;
}

/* Recently Viewed Navigation Buttons */
#recentlyViewedPrevBtn,
#recentlyViewedNextBtn {
    transition: all 0.3s ease;
}

#recentlyViewedPrevBtn:not(.opacity-0),
#recentlyViewedNextBtn:not(.opacity-0) {
    opacity: 1 !important;
}

/* Ensure Recently Viewed section has proper layering */
#recently-viewed-wrapper {
    position: relative;
    z-index: 50;
}

#recentlyViewedContainer {
    position: relative;
    z-index: 50;
}

#recently-viewed-container {
    position: relative;
    z-index: 50;
}

#recently-viewed-container a {
    position: relative;
    z-index: 50;
}

#recentlyViewedPrevBtn:hover:not(:disabled),
#recentlyViewedNextBtn:not(:disabled) {
    transform: translateY(-50%) scale(1.1);
}

/* Force Recently Viewed section to be above everything */
.bg-white.py-16.border-t.border-gray-200 {
    position: relative;
    z-index: 40;
    isolation: isolate;
}

/* Ensure no other elements can overlap */
* {
    position: relative;
    z-index: auto;
}

#recently-viewed-wrapper *,
#recentlyViewedContainer *,
#recently-viewed-container * {
    position: relative;
    z-index: inherit;
}

#recentlyViewedPrevBtn:hover:not(:disabled) {
    transform: translate(-1rem, -50%) scale(1.1);
}

#recentlyViewedNextBtn:hover:not(:disabled) {
    transform: translate(1rem, -50%) scale(1.1);
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
    const imageContainer = document.getElementById('image-container');
    
    if (mainImage && imageContainer) {
        // Add fade-in animation on load
        mainImage.addEventListener('load', function() {
            this.classList.add('image-fade-in');
        });
        
        // Add zoom effect on mouse move
        imageContainer.addEventListener('mousemove', function(e) {
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
        
        // Click to open gallery modal
        imageContainer.addEventListener('click', function() {
            openGalleryModal();
        });
    }
}

function changeMainImage(imageUrl, index = null) {
    const mainImage = document.getElementById('main-image');
    const imageLoading = document.getElementById('image-loading');
    const imageCounter = document.getElementById('image-counter');
    
    // Show loading spinner
    if (imageLoading) {
        imageLoading.classList.remove('hidden');
    }
    
    // Update current image index
    if (index !== null) {
        currentImageIndex = index;
    } else {
        currentImageIndex = allImages.indexOf(imageUrl);
    }
    
    // Update image counter
    if (imageCounter) {
        imageCounter.textContent = currentImageIndex + 1;
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
            mainImage.src = imageUrl;
            mainImage.style.opacity = '1';
        }, 150);
    };
    
    newImage.onerror = function() {
        // Hide loading spinner on error
        if (imageLoading) {
            imageLoading.classList.add('hidden');
        }
        console.error('Failed to load image:', imageUrl);
    };
    
    // Start loading the new image
    newImage.src = imageUrl;
    
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
    const modalImage = document.getElementById('modal-main-image');
    const imageCounter = document.getElementById('modal-image-counter');
    
    modal.classList.remove('hidden');
    modalImage.src = allImages[currentImageIndex];
    imageCounter.textContent = currentImageIndex + 1;
    
    // Update modal thumbnails
    updateModalThumbnails();
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeGalleryModal() {
    const modal = document.getElementById('gallery-modal');
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
    const modalImage = document.getElementById('modal-main-image');
    const imageCounter = document.getElementById('modal-image-counter');
    
    modalImage.src = allImages[currentImageIndex];
    imageCounter.textContent = currentImageIndex + 1;
    
    // Update modal thumbnails
    updateModalThumbnails();
    
    // Update main image and thumbnails
    changeMainImage(allImages[currentImageIndex], currentImageIndex);
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

// Keyboard navigation for Recently Viewed carousel
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('gallery-modal');
    const recentlyViewedWrapper = document.getElementById('recently-viewed-wrapper');
    
    // Only if gallery is not open and recently viewed section is visible
    if (modal.classList.contains('hidden') && recentlyViewedWrapper && !recentlyViewedWrapper.classList.contains('hidden')) {
        // Check if user is in viewport of recently viewed section
        const rect = recentlyViewedWrapper.getBoundingClientRect();
        const isInViewport = rect.top < window.innerHeight && rect.bottom > 0;
        
        if (isInViewport) {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                scrollRecentlyViewed('prev');
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                scrollRecentlyViewed('next');
            }
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
        
        // Update description
        const descElement = document.getElementById('selected-variant-description');
        if (descElement) {
            if (matchingVariant.description) {
                descElement.textContent = matchingVariant.description;
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
    }
}

// Recently Viewed Functions
function saveToRecentlyViewed() {
    const currentProduct = {
        id: {{ $product->id }},
        slug: '{{ $product->slug }}',
        name: '{{ addslashes($product->name) }}',
        price: {{ $product->base_price }},
        image: '{{ $media && count($media) > 0 ? (is_array($media[0]) ? $media[0]["url"] : $media[0]) : "" }}',
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
    
    // Keep only last 10 products
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
    
    // Filter out current product and limit to 20 products
    const productsToShow = recentlyViewed
        .filter(p => p.id !== {{ $product->id }})
        .slice(0, 20);
    
    if (productsToShow.length === 0) {
        if (wrapper) wrapper.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    if (wrapper) wrapper.classList.remove('hidden');
    emptyState.classList.add('hidden');
    
    // Check if mobile
    const isMobile = window.innerWidth < 1024; // lg breakpoint
    
    // Calculate item width for consistent sizing
    const containerEl = document.getElementById('recentlyViewedContainer');
    const containerWidth = containerEl ? containerEl.offsetWidth : 1200;
    const gap = isMobile ? 12 : 24; // gap-3 = 12px, gap-6 = 24px
    const itemsPerView = 5;
    const totalGaps = (itemsPerView - 1) * gap;
    const itemWidth = Math.floor((containerWidth - totalGaps) / itemsPerView);
    
    console.log('Recently Viewed - Container width:', containerWidth, 'Item width:', itemWidth, 'Gap:', gap);
    
    // Generate HTML for each product
    container.innerHTML = productsToShow.map(product => `
        <a href="/products/${product.slug}" class="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 group overflow-hidden block flex-shrink-0" style="width: ${itemWidth}px;">
            <!-- Product Image -->
            <div class="relative aspect-square overflow-hidden bg-gray-100">
                ${product.image ? `
                    <img src="${product.image}" 
                         alt="${product.name}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         onerror="this.parentElement.innerHTML='<div class=\\'w-full h-full bg-gray-200 flex items-center justify-center\\'><svg class=\\'w-12 h-12 text-gray-400\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\\'></path></svg></div>'">
                ` : `
                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                `}
                
                <!-- Recently Viewed Badge -->
                <div class="absolute top-2 right-2 bg-[#005366] text-white text-xs px-2 py-1 rounded-full flex items-center space-x-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>Viewed</span>
                </div>
            </div>

            <!-- Product Info -->
            <div class="${isMobile ? 'p-2.5' : 'p-4'}">
                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-[#005366] transition-colors ${isMobile ? 'text-xs h-8 overflow-hidden' : 'text-sm'}" title="${product.name}">
                    ${isMobile ? (product.name.length > 30 ? product.name.substring(0, 30) + '...' : product.name) : (product.name.length > 45 ? product.name.substring(0, 45) + '...' : product.name)}
                </h3>
                
                ${!isMobile ? `<p class="text-xs text-gray-600 mb-2">By <a href="/shops/${product.shop_slug}" class="hover:text-[#005366] transition-colors">${product.shop}</a></p>` : ''}
                
                <div class="flex items-center justify-between">
                    <span class="${isMobile ? 'text-xs' : 'text-lg'} font-bold text-[#E2150C]">$${parseFloat(product.price).toFixed(2)}</span>
                    <span class="text-xs text-gray-500">${formatTimeAgo(product.timestamp)}</span>
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
    
    if (!track || !container) return;
    
    const totalItems = track.children.length;
    if (totalItems === 0) return;
    
    const itemsVisible = 5;
    // Calculate how many "pages" we can scroll through
    // If we have 6 items, we can scroll 1 time (0->1)
    // If we have 10 items, we can scroll 5 times (0->1->2->3->4->5)
    const maxIndex = Math.max(0, totalItems - itemsVisible);
    
    // Get first item to calculate exact width
    const firstItem = track.children[0];
    if (!firstItem) return;
    
    const itemWidth = firstItem.offsetWidth;
    const computedStyle = window.getComputedStyle(track);
    const gap = parseFloat(computedStyle.gap) || 24;
    
    console.log('Scroll - Total items:', totalItems, 'Items visible:', itemsVisible, 'Max index:', maxIndex);
    console.log('Scroll - Item width:', itemWidth, 'Gap:', gap, 'Current index:', recentlyViewedCurrentIndex);
    
    // Update index based on direction
    if (direction === 'next') {
        recentlyViewedCurrentIndex = Math.min(recentlyViewedCurrentIndex + 1, maxIndex);
    } else {
        recentlyViewedCurrentIndex = Math.max(0, recentlyViewedCurrentIndex - 1);
    }
    
    // Apply transform (item width + gap for each item)
    const translateX = -recentlyViewedCurrentIndex * (itemWidth + gap);
    track.style.transform = `translateX(${translateX}px)`;
    
    console.log('New index:', recentlyViewedCurrentIndex, 'TranslateX:', translateX, 'Max allowed:', maxIndex);
    
    // Update button states
    if (prevBtn) {
        if (recentlyViewedCurrentIndex === 0) {
            prevBtn.classList.add('opacity-0');
            prevBtn.classList.remove('opacity-100');
            prevBtn.disabled = true;
        } else {
            prevBtn.classList.remove('opacity-0');
            prevBtn.classList.add('opacity-100');
            prevBtn.disabled = false;
        }
    }
    
    if (nextBtn) {
        if (recentlyViewedCurrentIndex >= maxIndex) {
            nextBtn.classList.add('opacity-0');
            nextBtn.classList.remove('opacity-100');
            nextBtn.disabled = true;
        } else {
            nextBtn.classList.remove('opacity-0');
            nextBtn.classList.add('opacity-100');
            nextBtn.disabled = false;
        }
    }
}

function updateRecentlyViewedNavigation(totalProducts) {
    const prevBtn = document.getElementById('recentlyViewedPrevBtn');
    const nextBtn = document.getElementById('recentlyViewedNextBtn');
    const container = document.getElementById('recentlyViewedContainer');
    
    if (!prevBtn || !nextBtn || !container) return;
    
    console.log('Update navigation - Total products:', totalProducts);
    
    // Show navigation buttons if more than 5 products
    if (totalProducts > 5) {
        prevBtn.classList.remove('hidden');
        nextBtn.classList.remove('hidden');
        
        // Set initial state
        prevBtn.classList.add('opacity-0');
        prevBtn.classList.remove('opacity-100');
        prevBtn.disabled = true;
        
        nextBtn.classList.remove('opacity-0');
        nextBtn.classList.add('opacity-100');
        nextBtn.disabled = false;
        
        // Reset index
        recentlyViewedCurrentIndex = 0;
        
        // Reset transform
        const track = document.getElementById('recently-viewed-container');
        if (track) {
            track.style.transform = 'translateX(0px)';
        }
        
        console.log('Navigation buttons shown - Products > 5');
    } else {
        // Hide navigation buttons if 5 or fewer products
        prevBtn.classList.add('hidden');
        nextBtn.classList.add('hidden');
        
        // Reset index and transform
        recentlyViewedCurrentIndex = 0;
        const track = document.getElementById('recently-viewed-container');
        if (track) {
            track.style.transform = 'translateX(0px)';
        }
        
        console.log('Navigation buttons hidden - Products <= 5');
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
    const btn = document.getElementById('add-to-cart-btn');
    const cartIcon = document.getElementById('cart-icon');
    const cartText = document.getElementById('cart-text');
    const cartLoading = document.getElementById('cart-loading');
    
    // Disable button and show loading
    btn.disabled = true;
    cartIcon.classList.add('hidden');
    cartLoading.classList.remove('hidden');
    cartText.textContent = 'Adding...';
    
    // Get current product data
    const productData = {
        id: {{ $product->id }},
        name: '{{ addslashes($product->name) }}',
        slug: '{{ $product->slug }}',
        price: {{ $product->base_price }},
        image: '{{ $media && count($media) > 0 ? (is_array($media[0]) ? $media[0]["url"] : $media[0]) : "" }}',
        shop: '{{ $product->shop->name ?? "Unknown Shop" }}',
        quantity: 1,
        selectedVariant: getSelectedVariant(),
        customizations: getSelectedCustomizations(),
        addedAt: Date.now()
    };
    
    // Add to localStorage immediately for fast UX
    addToLocalCart(productData);
    
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
            // Reset button
            btn.disabled = false;
            cartIcon.classList.remove('hidden');
            cartLoading.classList.add('hidden');
            cartText.textContent = 'Add to Cart';
            
            // Show cart popup
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
    
    // Return only the attributes, not the full variant object
    const variant = matchingVariant || variants[0] || null;
    return variant ? { 
        id: variant.id,
        attributes: variant.attributes 
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
    // Create popup overlay
    const overlay = document.createElement('div');
    overlay.id = 'cart-popup-overlay';
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    
    // Create popup content
    const popup = document.createElement('div');
    popup.className = 'bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden cart-popup-content';
    
    // Show loading state
    popup.innerHTML = `
        <div class="flex items-center justify-center p-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#005366]"></div>
        </div>
    `;
    
    overlay.appendChild(popup);
    document.body.appendChild(overlay);
    
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
            renderCartPopup(popup, cartItems, data.summary || {});
        } else {
            console.warn('Backend returned failed status');
            renderCartPopup(popup, [], {});
        }
    })
    .catch(error => {
        console.error('Failed to fetch cart:', error);
        popup.innerHTML = `
            <div class="p-6 text-center">
                <p class="text-red-600 mb-4">Không thể tải giỏ hàng. Vui lòng thử lại.</p>
                <p class="text-sm text-gray-600 mb-4">Lỗi: ${error.message}</p>
                <button onclick="closeCartPopup()" class="bg-[#005366] text-white px-6 py-2 rounded-lg hover:bg-[#003d4d]">
                    Đóng
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

function renderCartPopup(popup, cartItems, summary) {
    const totalItems = cartItems.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = parseFloat(summary.total || cartItems.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0));
    
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
        <div class="p-6 max-h-96 overflow-y-auto">
            <div class="space-y-4" id="cart-popup-items">
                ${generateCartPopupItems(cartItems)}
            </div>
        </div>
        
        <!-- Total Section -->
        <div class="border-t border-gray-200 p-6 bg-gray-50">
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal (${totalItems} items)</span>
                    <span class="font-semibold">$${parseFloat(summary.subtotal || 0).toFixed(2)}</span>
                </div>
                ${summary.shipping !== undefined ? `
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping</span>
                        ${summary.shipping == 0 ? 
                            '<span class="text-green-600 font-semibold">FREE</span>' : 
                            '<span class="font-semibold">$' + parseFloat(summary.shipping).toFixed(2) + '</span>'
                        }
                    </div>
                ` : ''}
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
                <button onclick="closeCartPopup(); window.location.href='{{ route('cart.index') }}'" 
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
            <div class="grid grid-cols-2 gap-4" id="cross-sell-products">
                ${generateCrossSellProducts()}
            </div>
        </div>
    `;
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
                            <button onclick="removeCartItemById(${item.id})" class="ml-2 p-1 text-gray-400 hover:text-red-500 transition-colors">
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
                                <button onclick="updateCartItemQuantity(event, ${item.id}, ${item.quantity - 1})" 
                                        class="w-7 h-7 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors ${item.quantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                                        ${item.quantity <= 1 ? 'disabled' : ''}>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                                <span class="text-sm font-semibold min-w-[1.5rem] text-center" id="quantity-${item.id}">${item.quantity}</span>
                                <button onclick="updateCartItemQuantity(event, ${item.id}, ${item.quantity + 1})" 
                                        class="w-7 h-7 rounded-lg border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-[#005366]">$${(parseFloat(item.price) * item.quantity).toFixed(2)}</p>
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
            alert('Không thể cập nhật số lượng: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error updating quantity:', error);
        if (quantitySpan) quantitySpan.textContent = originalText;
        alert('Lỗi: ' + error.message);
    });
}

function removeCartItemById(cartItemId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
    
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
            showCartSuccess('Đã xóa sản phẩm khỏi giỏ hàng');
        } else {
            alert('Không thể xóa sản phẩm: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error removing item:', error);
        alert('Lỗi: ' + error.message);
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
            
            // Update shipping
            if (subtotalElements[1] && summary.shipping !== undefined) {
                const shippingSpan = subtotalElements[1].querySelector('span:last-child');
                if (shippingSpan) {
                    if (summary.shipping == 0) {
                        shippingSpan.className = 'text-green-600 font-semibold';
                        shippingSpan.textContent = 'FREE';
                    } else {
                        shippingSpan.className = 'font-semibold';
                        shippingSpan.textContent = `$${parseFloat(summary.shipping).toFixed(2)}`;
                    }
                }
            }
            
            // Update total
            const totalElement = document.querySelector('#cart-popup-overlay .border-t.pt-3 span:last-child');
            if (totalElement) {
                totalElement.textContent = `$${parseFloat(summary.total || 0).toFixed(2)}`;
            }
            
            // Update header cart count
            updateCartCount();
            
            // If cart is empty, close popup
            if (data.cart_items.length === 0) {
                closeCartPopup();
                showCartSuccess('Giỏ hàng đã trống');
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
                    <p class="text-red-600">Không thể cập nhật giỏ hàng</p>
                    <p class="text-sm text-gray-500">${error.message}</p>
                    <button onclick="refreshCartPopupContent()" class="mt-2 text-[#005366] hover:underline">Thử lại</button>
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
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->base_price,
            'image' => $media && count($media) > 0 ? (is_array($media[0]) ? $media[0]['url'] : $media[0]) : null,
        ];
    })->toArray();
@endphp

function generateCrossSellProducts() {
    // Get related products from the page
    const relatedProducts = @json($crossSellData ?? []);
    
    if (!relatedProducts || relatedProducts.length === 0) {
        return '<p class="col-span-2 text-center text-gray-500 py-4">No recommendations available</p>';
    }
    
    // Take first 2 related products for cross-sell
    const crossSellProducts = relatedProducts.slice(0, 2);
    
    return crossSellProducts.map(product => `
        <div class="border border-gray-200 rounded-lg p-3 hover:border-[#005366] transition-colors cursor-pointer cross-sell-product" 
             onclick="addCrossSellToCart(${product.id}, '${product.name}', ${product.price}, '${product.image || ''}')">
            ${product.image && product.image !== 'undefined' && product.image !== '' ? `
                <img src="${product.image}" 
                 alt="${product.name}" 
                     class="w-full h-24 object-cover rounded-md mb-2"
                     onerror="this.parentElement.innerHTML='<div class=\\'w-full h-24 bg-gray-200 rounded-md mb-2 flex items-center justify-center\\'><svg class=\\'w-8 h-8 text-gray-400\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\\'></path></svg></div>'">
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
                <button class="bg-[#005366] text-white text-xs px-3 py-1 rounded hover:bg-[#003d4d] transition-colors">
                    Add
                </button>
            </div>
        </div>
    `).join('');
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
            showCartSuccess('Đã thêm vào giỏ hàng');
        }
    })
    .catch(error => {
        console.error('Failed to add cross-sell product:', error);
        alert('Không thể thêm sản phẩm');
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
</script>
@endsection
