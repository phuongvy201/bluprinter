@extends('layouts.app')

@section('title', $shop->shop_name . ' - Shop Profile')

@section('content')
@php
    $currentCurrency = currency();
    $currencySymbol = currency_symbol();
@endphp
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
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

<main class="pb-20">
    <!-- Shop Cover Banner -->
    <div class="relative w-full h-[300px] overflow-hidden">
        @if($shop->shop_banner)
            <img alt="Shop Cover Banner" class="w-full h-full object-cover" src="{{ $shop->shop_banner }}">
        @else
            <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
    </div>

    <!-- Shop Profile Card -->
    <div class="max-w-7xl mx-auto px-4 -mt-16 relative z-10">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 md:p-8">
            <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                <!-- Shop Avatar -->
                <div class="relative">
                    <div class="w-32 h-32 rounded-full border-4 border-white dark:border-slate-800 overflow-hidden shadow-lg bg-white">
                        @if($shop->shop_logo)
                            <img alt="{{ $shop->shop_name }} Profile" class="w-full h-full object-cover" src="{{ $shop->shop_logo }}">
                        @else
                            <div class="w-full h-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                                <span class="text-4xl font-bold text-slate-400">{{ substr($shop->shop_name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    @if($shop->verified)
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center border-2 border-white">
                            <span class="material-icons-outlined text-white text-sm">check</span>
                        </div>
                    @endif
                </div>

                <!-- Shop Info -->
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-3xl font-bold mb-2">{{ $shop->shop_name }}</h2>
                    <div class="flex items-center justify-center md:justify-start gap-4 text-slate-500 dark:text-slate-400">
                        <span class="flex items-center gap-1.5 text-sm">
                            <span class="material-icons-outlined text-sm">people</span> 
                            <span data-followers>{{ number_format($stats['followers']) }}</span> Followers
                        </span>
                        <span class="flex items-center gap-1.5 text-sm">
                            <span class="material-icons-outlined text-sm">favorite</span> 
                            {{ number_format($stats['favorited']) }} Favorited
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <button id="followBtn" 
                            onclick="toggleFollow()"
                            class="flex-1 md:flex-none bg-primary hover:bg-rose-700 text-white font-semibold py-3 px-8 rounded-full flex items-center justify-center gap-2 transition-all {{ $isFollowing ? 'bg-gray-200 text-gray-700 hover:bg-gray-300' : '' }}"
                            style="{{ !$isFollowing ? 'background-color: #e11d48;' : '' }}">
                        <span class="material-icons-outlined text-sm">favorite</span>
                        <span id="followText">{{ $isFollowing ? 'Unfollow' : 'Follow' }}</span>
                    </button>
                    <button onclick="openContactModal()"
                            class="flex-1 md:flex-none border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 font-semibold py-3 px-8 rounded-full flex items-center justify-center gap-2 transition-all">
                        <span class="material-icons-outlined text-sm">mail</span>
                        <span>Contact</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop Categories Section -->
    <section class="max-w-7xl mx-auto px-4 mt-12">
        @if($categories->count() > 0)
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <span class="w-1.5 h-6 bg-primary rounded-full"></span>
                Shop Categories
            </h3>
        </div>
        <div class="flex gap-4 overflow-x-auto pb-4 hide-scrollbar">
            @foreach($categories as $category)
            <div class="flex-shrink-0 group cursor-pointer" onclick="filterByCategory('{{ $category->id }}')">
                <div class="w-48 bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-100 dark:border-slate-800 transition-all hover:shadow-md">
                    <div class="relative h-32 w-full bg-slate-50 dark:bg-slate-900 rounded-xl overflow-hidden mb-3">
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
                            $imageUrl = null;
                            if ($firstProduct && count($firstProduct->getEffectiveMedia()) > 0) {
                                $media = $firstProduct->getEffectiveMedia();
                                $imageUrl = is_array($media) && isset($media[0]) ? $media[0] : (is_string($media) ? $media : '');
                            }
                        @endphp
                        @if($imageUrl)
                            <img alt="{{ $category->name }}" class="w-full h-full object-contain p-4 group-hover:scale-110 transition-transform" src="{{ $imageUrl }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="material-icons-outlined text-slate-400 text-4xl">category</span>
                            </div>
                        @endif
                        <span class="absolute top-2 right-2 bg-primary text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                            {{ $category->templates->count() }}
                        </span>
                    </div>
                    <p class="font-semibold text-center group-hover:text-primary transition-colors">{{ $category->name }}</p>
                    <p class="text-xs text-slate-500 text-center">{{ $category->templates->count() }} Items</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        
    </section>

    <!-- All Products Section -->
    <section class="max-w-7xl mx-auto px-4 mt-12">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-xl font-bold flex items-center gap-2">
                <span class="w-1.5 h-6 bg-primary rounded-full"></span>
                All Products
            </h3>
            <div class="flex gap-2">
                <select class="bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-full px-4 py-2 text-sm focus:ring-primary focus:border-primary">
                    <option>Newest first</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Popular</option>
                </select>
            </div>
        </div>
        @if($allProducts->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($allProducts as $product)
            <div class="bg-white dark:bg-slate-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group">
                <!-- Product Image -->
                <div class="relative aspect-square bg-slate-50 dark:bg-slate-900 p-6">
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
                        <img alt="{{ $product->name }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" src="{{ $imageUrl }}">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="material-icons-outlined text-slate-400 text-6xl">image</span>
                        </div>
                    @endif
                    <button class="absolute top-3 right-3 w-8 h-8 bg-white/80 dark:bg-slate-700/80 backdrop-blur rounded-full flex items-center justify-center text-slate-400 hover:text-primary transition-colors">
                        <span class="material-icons-outlined text-sm">favorite_border</span>
                    </button>
                </div>
                <!-- Product Info -->
                <div class="p-4">
                    <h4 class="font-medium text-slate-800 dark:text-slate-200 truncate mb-2">
                        <a href="{{ route('products.show', $product->slug) }}" class="hover:text-primary transition-colors">
                            {{ Str::limit($product->name, 50) }}
                        </a>
                    </h4>
                    <div class="flex items-center justify-between">
                        <p class="text-lg font-bold text-primary">
                            {{ format_price_usd((float) $product->base_price) }}
                        </p>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase">
                            @if($product->template)
                                {{ $product->template->name ?? 'Premium' }}
                            @else
                                Premium
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="mt-12 text-center">
            {{ $allProducts->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <span class="material-icons-outlined text-slate-300 text-6xl mb-4 block">inventory_2</span>
            <p class="text-slate-500 text-lg">This shop has no products yet</p>
        </div>
        @endif
    </section>
</main>

<!-- Contact Modal -->
<div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Contact Shop</h3>
            <button onclick="closeContactModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-300">
                <span class="material-icons-outlined">close</span>
            </button>
        </div>
        
        <form id="contactForm">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Subject</label>
                <input type="text" id="subject" name="subject" required 
                       class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Message</label>
                <textarea id="message" name="message" rows="4" required 
                          class="w-full px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
            </div>
            
            <div class="flex space-x-3">
                <button type="button" onclick="closeContactModal()" 
                        class="flex-1 px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-primary hover:bg-rose-700 text-white rounded-lg transition-colors">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    :root {
        --primary: #e11d48;
    }
    
    .bg-primary {
        background-color: var(--primary);
    }
    
    .text-primary {
        color: var(--primary);
    }
    
    .border-primary {
        border-color: var(--primary);
    }
    
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
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
    
    // Use route helper with shop_slug (Shop model uses shop_slug for route binding)
    const followUrl = '{{ route("shops.follow", $shop->shop_slug ?? $shop->id) }}';
    
    fetch(followUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ action: action })
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            // If not ok, try to parse error response
            return response.json().then(err => {
                throw new Error(err.message || 'Request failed');
            }).catch(() => {
                throw new Error(`Server error: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
         if (data.success) {
             if (action === 'follow') {
                 followBtn.classList.remove('bg-gray-200', 'text-gray-700');
                 followBtn.classList.add('text-white');
                 followBtn.style.backgroundColor = '#e11d48';
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
            if (typeof showNotification === 'function') {
                showNotification(data.message, 'success');
            } else {
                alert(data.message);
            }
        } else {
            if (typeof showNotification === 'function') {
                showNotification(data.message, 'error');
            } else {
                alert(data.message);
            }
        }
    })
    .catch(error => {
        console.error('Follow shop error:', error);
        const errorMessage = error.message || 'An error occurred. Please try again.';
        if (typeof showNotification === 'function') {
            showNotification(errorMessage, 'error');
        } else {
            alert(errorMessage);
        }
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
    showNotification('Loading products...', 'info');
    
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
