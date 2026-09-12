@php
    $header = \App\Http\Controllers\Admin\HeaderSettingsController::resolved();
    $domain = \App\Services\CurrencyService::getCurrentDomain();
    $currency = \App\Services\CurrencyService::getCurrencyForDomain($domain);
    $currencyRate = \App\Services\CurrencyService::getCurrencyRateForDomain($domain) ?? 1.0;
    $freeShippingThreshold = 100;
    $convertedThreshold = $currency !== 'USD'
        ? \App\Services\CurrencyService::convertFromUSDWithRate($freeShippingThreshold, $currency, $currencyRate)
        : $freeShippingThreshold;
    $formattedThreshold = \App\Services\CurrencyService::formatPrice($convertedThreshold, $currency, $domain);

    $productCategories = \App\Models\Category::whereNull('parent_id')
        ->orderBy('name')
        ->limit((int) ($header['categories_limit'] ?? 12))
        ->get();

    $headerCollections = \App\Models\Collection::query()
        ->global()
        ->active()
        ->approved()
        ->hasDisplayableProducts()
        ->orderByDesc('featured')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->limit((int) ($header['collections_limit'] ?? 12))
        ->get(['id', 'name', 'slug']);

    $postCategories = \App\Models\PostCategory::orderBy('sort_order')->orderBy('name')->get();
    $slides = collect($header['announcement_slides'] ?? [])->filter(fn ($s) => !empty($s['primary']))->values();
    $studio = $header['creator_studio'];
    $userMenu = $header['user_menu'];
    $trendingSearches = $header['trending_searches'] ?? array_slice($header['search_placeholders'] ?? [], 0, 5);
    $trendingTopics = \App\Models\Collection::global()
        ->where('status', 'active')
        ->where('admin_approved', true)
        ->orderByDesc('featured')
        ->orderBy('sort_order')
        ->limit(6)
        ->get();
    $topicProductTags = [
        ['label' => 'T-shirts', 'tone' => 'bg-orange-50 text-orange-600'],
        ['label' => 'Mugs', 'tone' => 'bg-sky-50 text-sky-700'],
        ['label' => 'Hoodies', 'tone' => 'bg-rose-50 text-rose-600'],
        ['label' => 'Tote bags', 'tone' => 'bg-emerald-50 text-emerald-700'],
    ];
    $brandLogo = asset('images/logo-header.png');
@endphp

<header class="site-header bg-white sticky top-0 z-50 shadow-sm" id="site-header" data-header-root>
    {{-- 1. Top announcement bar (slider) --}}
    <div class="header-announce bg-[#2b7bc0] text-white">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-10 sm:h-11 gap-3">
                <div class="relative min-w-0 flex-1 overflow-hidden h-5">
                    @forelse ($slides as $index => $slide)
                        <div class="announcement-slide absolute inset-0 flex items-center gap-2 sm:gap-3 transition-all duration-500 {{ $index === 0 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2 pointer-events-none' }}"
                             data-slide-index="{{ $index }}">
                            <span class="truncate font-bold">{{ $slide['primary'] }}</span>
                            @if (!empty($slide['show_stars']))
                                <span class="hidden sm:inline text-white/60">|</span>
                                <span class="hidden sm:flex items-center gap-0.5 text-yellow-300 shrink-0" aria-hidden="true">
                                    @for ($i = 0; $i < 5; $i++)
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </span>
                            @endif
                            @if (!empty($slide['secondary']))
                                <span class="hidden md:inline text-white/60">|</span>
                                <span class="hidden md:inline whitespace-nowrap font-medium">{{ $slide['secondary'] }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="flex items-center gap-2">
                            <span class="font-bold">3,500,000+ Happy Customers</span>
                            <span class="hidden md:inline text-white/60">|</span>
                            <span class="hidden md:inline font-medium">Since 2021</span>
                        </div>
                    @endforelse
                </div>
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <a href="{{ url($header['promo_url'] ?? '/promo-code') }}" class="font-bold tracking-wide text-[#c8e600] hover:text-[#d8f020] transition uppercase text-sm sm:text-base">
                        {{ $header['promo_label'] }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Main header bar --}}
    <div class="header-main-bar bg-white border-b border-gray-100 relative">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Mobile: list+search | logo | user+cart --}}
            <div class="lg:hidden">
                <div class="header-main-inner grid grid-cols-[1fr_auto_1fr] items-center py-3 gap-2">
                    <div class="flex items-center justify-start gap-0.5">
                        <button id="mobile-menu-btn" type="button" class="p-2 text-gray-700 hover:text-[#005366] transition" aria-label="Menu" aria-expanded="false" aria-controls="mobile-drawer">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <button id="mobile-search-btn" type="button" class="p-2 text-gray-700 hover:text-[#f26522] transition" aria-label="Search" aria-expanded="false" aria-controls="mobile-search-overlay">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>

                    <a href="{{ route('home') }}" class="header-brand-link flex items-center justify-center min-w-0 px-1" aria-label="Bluprinter home">
                        <img src="{{ $brandLogo }}"
                             alt="Bluprinter"
                             class="header-brand-logo"
                             width="200"
                             height="54">
                    </a>

                    <div class="flex items-center justify-end gap-0.5">
                        <button type="button" class="header-panel-btn p-2 text-gray-700 hover:text-[#005366] transition" data-panel="user" aria-label="Account" aria-expanded="false">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </button>
                        <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-700 hover:text-[#f26522] transition" aria-label="Cart">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span id="mobile-cart-count" class="cart-count absolute top-0.5 right-0.5 bg-[#e2150c] text-white text-[10px] rounded-full min-w-[16px] h-4 px-1 flex items-center justify-center font-semibold" style="display: none;">0</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Desktop --}}
            <div class="header-main-inner hidden lg:flex items-center gap-4 xl:gap-6 py-3">
                <a href="{{ route('home') }}" class="header-brand-link flex items-center shrink-0 group" aria-label="Bluprinter home">
                    <img src="{{ $brandLogo }}"
                         alt="Bluprinter"
                         class="header-brand-logo group-hover:opacity-90 transition-opacity"
                         width="240"
                         height="65">
                </a>

                {{-- Categories toggle --}}
                <div class="relative shrink-0" data-panel-wrap="categories">
                    <button type="button"
                            class="header-panel-btn header-categories-btn flex items-center gap-2 text-gray-900 font-normal hover:text-[#f26522] transition py-2"
                            data-panel="categories"
                            aria-expanded="false">
                        <svg class="w-6 h-6 panel-icon-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg class="w-6 h-6 panel-icon-close hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Categories</span>
                    </button>

                    <div class="header-panel absolute left-0 top-full pt-2 w-[320px] z-50 hidden" data-panel-content="categories">
                        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 py-2">
                            @forelse ($productCategories as $category)
                                <a href="{{ route('category.show', $category->slug) }}"
                                   class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition">
                                    <div class="w-11 h-11 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                                        @if ($category->image)
                                            <img src="{{ $category->image }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#005366] to-[#003d4d]">
                                                <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="header-cat-item font-medium text-gray-900">{{ $category->name }}</span>
                                </a>
                            @empty
                                <p class="px-4 py-6 text-base text-gray-500">No categories yet.</p>
                            @endforelse
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <a href="{{ route('products.index') }}" class="block px-4 py-3 text-base font-medium text-[#005366] hover:text-[#f26522]">
                                    View All Categories →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 max-w-3xl mx-2 xl:mx-6">
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <input type="text" name="q" id="search-input" value="{{ request('q') }}"
                               placeholder="{{ $header['search_placeholders'][0] ?? 'Search' }}"
                               class="search-input w-full pl-11 pr-4 py-3.5 text-base text-gray-900 bg-white">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <div id="search-suggestions" class="hidden absolute top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-2xl border border-gray-200 max-h-96 overflow-y-auto z-50">
                            <div id="suggestions-content" class="p-2"></div>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-1 xl:gap-2 shrink-0">
                    {{-- User --}}
                    <div class="relative" data-panel-wrap="user">
                        <button type="button" class="header-panel-btn p-2.5 text-gray-800 hover:text-[#005366] transition" data-panel="user" aria-label="Account" aria-expanded="false">
                            @auth
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="" class="w-7 h-7 rounded-full object-cover">
                                @else
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                @endif
                            @else
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            @endauth
                        </button>

                        <div class="header-panel absolute right-0 top-full pt-3 w-[340px] z-50 hidden" data-panel-content="user">
                            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                                <div class="absolute -top-2 right-6 w-4 h-4 bg-white border-l border-t border-gray-100 rotate-45"></div>
                                <div class="flex items-start gap-3 p-4 border-b border-gray-100">
                                    <div class="w-12 h-12 rounded-full bg-[#2b7bc0] text-white flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8V22h19.2v-2.8c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-0.5">
                                        @auth
                                            <p class="header-menu-title font-bold text-gray-900">{{ $userMenu['auth_title'] }}</p>
                                            <p class="header-menu-sub text-gray-500 truncate">{{ $userMenu['auth_subtitle'] }}</p>
                                        @else
                                            <p class="header-menu-title font-bold text-gray-900">{{ $userMenu['guest_title'] }}</p>
                                            <p class="header-menu-sub text-gray-500">{{ $userMenu['guest_subtitle'] }}</p>
                                        @endauth
                                    </div>
                                    <button type="button" class="header-panel-close p-1 text-gray-400 hover:text-gray-700" data-close-panel aria-label="Close">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="py-2">
                                    @auth
                                        @if(auth()->user()->hasAnyRole(['admin', 'seller', 'ad-partner']))
                                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                                <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/></svg>
                                                </span>
                                                <span class="header-menu-item flex-1 font-medium text-gray-800">Dashboard</span>
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('customer.orders.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                            <span class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            </span>
                                            <span class="header-menu-item flex-1 font-medium text-gray-800">My Orders</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="{{ route('studio.history') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                            <span class="w-10 h-10 rounded-xl bg-orange-50 text-[#f26522] flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6z"/></svg>
                                            </span>
                                            <span class="header-menu-item flex-1 font-medium text-gray-800">My designs</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                            <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            </span>
                                            <span class="header-menu-item flex-1 font-medium text-gray-800">Wishlist</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="{{ route('orders.track') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                            <span class="w-10 h-10 rounded-xl bg-orange-50 text-[#f26522] flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </span>
                                            <span class="header-menu-item flex-1 font-medium text-gray-800">Order Tracking</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="{{ route('customer.profile.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                            <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            </span>
                                            <span class="header-menu-item flex-1 font-medium text-gray-800">Profile</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-50 transition text-left">
                                                <span class="w-10 h-10 rounded-xl bg-red-50 text-[#e2150c] flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                                </span>
                                                <span class="header-menu-item flex-1 font-medium text-[#e2150c]">Logout</span>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                            <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                                            </span>
                                            <span class="header-menu-item flex-1 font-medium text-gray-800">Login</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                            <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                            </span>
                                            <span class="header-menu-item flex-1 font-medium text-gray-800">Wishlist</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="{{ route('studio.history') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                            <span class="w-10 h-10 rounded-xl bg-orange-50 text-[#f26522] flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6z"/></svg>
                                            </span>
                                            <span class="header-menu-item flex-1 font-medium text-gray-800">My designs</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                        <a href="{{ route('orders.track') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                                            <span class="w-10 h-10 rounded-xl bg-orange-50 text-[#f26522] flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </span>
                                            <span class="header-menu-item flex-1 font-medium text-gray-800">Order Tracking</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Gene AI / Creator Studio --}}
                    <div class="relative" data-panel-wrap="studio">
                        <button type="button"
                                class="header-panel-btn p-1.5 rounded-full bg-[#e2150c] text-white hover:bg-[#c0120a] transition shadow-sm"
                                data-panel="studio"
                                aria-label="Creator Studio"
                                aria-expanded="false"
                                title="{{ $studio['title'] }}">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9.5 3.2l1.15 3.1 3.15 1.15-3.15 1.15-1.15 3.1-1.15-3.1L5.2 7.45l3.15-1.15L9.5 3.2zM17.4 9.2l.85 2.2 2.25.85-2.25.85-.85 2.2-.85-2.2-2.25-.85 2.25-.85.85-2.2zM13.8 14.6l.7 1.85 1.9.7-1.9.7-.7 1.85-.7-1.85-1.9-.7 1.9-.7.7-1.85z"/>
                            </svg>
                        </button>

                        <div class="header-panel absolute right-0 top-full pt-3 w-[min(720px,92vw)] z-50 hidden" data-panel-content="studio">
                            <div class="relative bg-white rounded-3xl shadow-2xl border border-gray-100 p-5">
                                <div class="absolute -top-2 right-8 w-4 h-4 bg-white border-l border-t border-gray-100 rotate-45"></div>
                                <div class="flex items-start gap-3 mb-5">
                                    <div class="w-12 h-12 rounded-full bg-[#e2150c] text-white flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9.5 3.2l1.15 3.1 3.15 1.15-3.15 1.15-1.15 3.1-1.15-3.1L5.2 7.45l3.15-1.15L9.5 3.2zM17.4 9.2l.85 2.2 2.25.85-2.25.85-.85 2.2-.85-2.2-2.25-.85 2.25-.85.85-2.2zM13.8 14.6l.7 1.85 1.9.7-1.9.7-.7 1.85-.7-1.85-1.9-.7 1.9-.7.7-1.85z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="header-studio-title font-bold text-gray-900">{{ $studio['title'] }}</h3>
                                        <p class="header-studio-sub text-gray-500">{{ $studio['subtitle'] }}</p>
                                    </div>
                                    <button type="button" class="header-panel-close p-1 text-gray-400 hover:text-gray-700" data-close-panel aria-label="Close">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @foreach ($studio['cards'] as $cardIndex => $card)
                                        <a href="{{ url($card['url'] ?? '/products') }}"
                                           class="rounded-2xl p-4 text-center hover:scale-[1.02] transition-transform"
                                           style="background: {{ $card['bg'] ?? '#f5f5f5' }}">
                                            <div class="h-28 mb-3 flex items-center justify-center overflow-hidden rounded-xl">
                                                @if (!empty($card['image']))
                                                    <img src="{{ $card['image'] }}" alt="{{ $card['title'] }}" class="max-h-full object-contain">
                                                @else
                                                    @if ($cardIndex === 0)
                                                        <svg class="w-20 h-20 text-[#2b7bc0]" viewBox="0 0 80 80" fill="none"><rect x="18" y="10" width="34" height="50" rx="4" stroke="currentColor" stroke-width="2"/><circle cx="52" cy="48" r="14" fill="#fff" stroke="currentColor" stroke-width="2"/><path d="M46 48h12M52 42v12" stroke="currentColor" stroke-width="2"/><path d="M24 22h14M24 30h10" stroke="currentColor" stroke-width="2"/></svg>
                                                    @elseif ($cardIndex === 1)
                                                        <svg class="w-20 h-20 text-[#e2150c]" viewBox="0 0 80 80" fill="none"><path d="M28 20h24l6 12v28a4 4 0 01-4 4H26a4 4 0 01-4-4V32l6-12z" stroke="currentColor" stroke-width="2"/><circle cx="40" cy="18" r="8" fill="#e2150c" stroke="#fff" stroke-width="2"/><text x="40" y="21" text-anchor="middle" fill="#fff" font-size="8" font-weight="700">AI</text><path d="M34 42c4-6 8-6 12 0" stroke="currentColor" stroke-width="2"/></svg>
                                                    @else
                                                        <svg class="w-20 h-20 text-purple-500" viewBox="0 0 80 80" fill="none"><circle cx="40" cy="28" r="10" stroke="currentColor" stroke-width="2"/><path d="M22 62c4-14 12-20 18-20s14 6 18 20" stroke="currentColor" stroke-width="2"/><rect x="28" y="34" width="24" height="30" rx="2" stroke="currentColor" stroke-width="2" stroke-dasharray="3 2"/></svg>
                                                    @endif
                                                @endif
                                            </div>
                                            <p class="header-card-title font-bold text-gray-900">{{ $card['title'] }}</p>
                                            <p class="header-card-desc text-gray-500 mt-1">{{ $card['description'] }}</p>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('cart.index') }}" class="relative p-2.5 text-gray-800 hover:text-[#f26522] transition" aria-label="Cart">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span id="desktop-cart-count" class="cart-count absolute top-1 right-1 bg-[#e2150c] text-white text-[10px] rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center font-semibold" style="display: none;">0</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Sub navigation (desktop) --}}
    <nav class="header-subnav hidden lg:block bg-[#f7f7f7] border-b border-gray-200">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Hidden SVG gradient for accent nav icons --}}
            <svg width="0" height="0" class="absolute" aria-hidden="true">
                <defs>
                    <linearGradient id="nav-accent-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#f26522"/>
                        <stop offset="55%" stop-color="#e2150c"/>
                        <stop offset="100%" stop-color="#ff8a3d"/>
                    </linearGradient>
                </defs>
            </svg>

            <div class="flex items-center justify-center flex-wrap gap-x-6 gap-y-2 xl:gap-x-8 py-3">
                @foreach ($header['nav_links'] as $link)
                    @php
                        $isProducts = ($link['dropdown'] ?? null) === 'products';
                        $isCollections = ($link['dropdown'] ?? null) === 'collections';
                        $isBlog = ($link['dropdown'] ?? null) === 'blog';
                    @endphp
                    @if ($isProducts)
                        <div class="header-subnav-item">
                            <a href="{{ url($link['url']) }}" class="nav-link-sub inline-flex items-center gap-1 {{ !empty($link['accent']) ? 'is-accent' : '' }} {{ request()->routeIs('products.*') ? 'text-[#005366]' : '' }}">
                                {{ $link['label'] }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="header-subnav-dropdown">
                                <div class="header-subnav-dropdown__panel">
                                    @foreach($productCategories as $category)
                                        <a href="{{ route('category.show', $category->slug) }}" class="header-subnav-dropdown__link">{{ $category->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif ($isCollections)
                        <div class="header-subnav-item">
                            <a href="{{ route('collections.index') }}" class="nav-link-sub inline-flex items-center gap-1 {{ !empty($link['accent']) ? 'is-accent' : '' }} {{ request()->routeIs('collections.*') ? 'text-[#005366]' : '' }}">
                                {{ $link['label'] }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="header-subnav-dropdown">
                                <div class="header-subnav-dropdown__panel header-subnav-dropdown__panel--scroll">
                                    @forelse($headerCollections as $collection)
                                        <a href="{{ route('collections.show', $collection->slug) }}" class="header-subnav-dropdown__link">{{ $collection->name }}</a>
                                    @empty
                                        <p class="px-3 py-2.5 text-base text-gray-500">No collections yet.</p>
                                    @endforelse
                                    <div class="border-t border-gray-100 mt-1 pt-1">
                                        <a href="{{ route('collections.index') }}" class="header-subnav-dropdown__link header-subnav-dropdown__link--strong">
                                            View all collections →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif ($isBlog)
                        <div class="header-subnav-item header-subnav-item--end">
                            <a href="{{ url($link['url']) }}" class="nav-link-sub inline-flex items-center gap-1 {{ !empty($link['accent']) ? 'is-accent' : '' }} {{ request()->routeIs('blog.*') ? 'text-[#005366]' : '' }}">
                                {{ $link['label'] }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="header-subnav-dropdown">
                                <div class="header-subnav-dropdown__panel">
                                    <a href="{{ route('blog.index') }}" class="header-subnav-dropdown__link header-subnav-dropdown__link--strong">All Posts</a>
                                    @foreach($postCategories->take(8) as $postCategory)
                                        <a href="{{ route('blog.category', $postCategory->slug) }}" class="header-subnav-dropdown__link">{{ $postCategory->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ url($link['url']) }}" class="nav-link-sub inline-flex items-center gap-1.5 {{ !empty($link['accent']) ? 'is-accent' : '' }}">
                            @if (($link['icon'] ?? null) === 'create')
                                <svg class="w-4 h-4 shrink-0" style="stroke: #f26522;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            @elseif (($link['icon'] ?? null) === 'pin')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @endif
                            {{ $link['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>

            {{-- Mobile sub-nav links live in the category drawer --}}
        </div>
    </nav>

    {{-- Mobile category drawer --}}
    <div id="mobile-drawer-backdrop" class="header-mobile-backdrop fixed inset-0 bg-black/45 z-[90] hidden lg:hidden" aria-hidden="true"></div>
    <aside id="mobile-drawer" class="header-mobile-drawer fixed inset-y-0 left-0 z-[91] w-[min(86vw,320px)] bg-white shadow-2xl -translate-x-full transition-transform duration-300 ease-out lg:hidden" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Categories">
        <div class="flex items-center gap-3 px-4 py-4 border-b border-gray-100">
            <img src="{{ $brandLogo }}"
                 alt="Bluprinter"
                 class="header-brand-logo header-brand-logo--drawer"
                 width="200"
                 height="54">
        </div>
        <div class="overflow-y-auto h-[calc(100%-72px)] py-2">
            @forelse ($productCategories as $category)
                <a href="{{ route('category.show', $category->slug) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition">
                    <div class="w-11 h-11 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                        @if ($category->image)
                            <img src="{{ $category->image }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#005366] to-[#003d4d]">
                                <svg class="w-5 h-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <span class="header-cat-item font-medium text-gray-900">{{ $category->name }}</span>
                </a>
            @empty
                <p class="px-4 py-6 text-base text-gray-500">No categories yet.</p>
            @endforelse
            <div class="border-t border-gray-100 mt-2 pt-2 px-4 pb-4 space-y-1">
                @foreach ($header['nav_links'] as $link)
                    <a href="{{ url($link['url']) }}" class="block py-2.5 text-base {{ !empty($link['accent']) ? 'nav-link-sub is-accent' : 'text-gray-800' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
                <p class="pt-2 text-sm text-gray-500">Free shipping on orders over {{ $formattedThreshold }}</p>
            </div>
        </div>
    </aside>
    <button type="button" id="mobile-drawer-close" class="header-mobile-drawer-close fixed top-4 right-4 z-[92] w-10 h-10 rounded-full bg-white/95 text-gray-800 shadow-md items-center justify-center hidden lg:hidden" aria-label="Close menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Mobile search overlay --}}
    <div id="mobile-search-overlay" class="fixed inset-0 z-[90] bg-white hidden lg:hidden flex flex-col" role="dialog" aria-modal="true" aria-label="Search" aria-hidden="true">
        <div class="px-4 pt-4 pb-3 border-b border-gray-100">
            <form action="{{ route('search') }}" method="GET" class="flex items-center gap-3">
                <div class="relative flex-1 min-w-0">
                    <input type="text" name="q" id="mobile-search-input" value="{{ request('q') }}"
                           placeholder="Search designs and products"
                           class="search-input w-full pl-10 pr-4 py-3 text-base text-gray-900 bg-white">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                <button type="button" id="mobile-search-close" class="p-2 text-gray-400 hover:text-gray-700 shrink-0" aria-label="Close search">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-5">
            <h3 class="text-lg font-bold text-gray-900 mb-3">Trending searches</h3>
            <div class="flex flex-wrap gap-x-4 gap-y-3 mb-8">
                @foreach ($trendingSearches as $term)
                    <a href="{{ route('search', ['q' => $term]) }}" class="inline-flex items-center gap-2 text-base text-gray-800 hover:text-[#f26522] transition">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <span>{{ $term }}</span>
                    </a>
                @endforeach
            </div>

            <h3 class="text-lg font-bold text-gray-900 mb-3">Trending Topics</h3>
            <div class="flex gap-4 overflow-x-auto pb-4 -mx-1 px-1" style="scrollbar-width: none;">
                @forelse ($trendingTopics as $topic)
                    <div class="w-56 shrink-0">
                        <a href="{{ route('collections.show', $topic->slug) }}" class="block rounded-xl overflow-hidden bg-gray-100 aspect-[4/3] mb-2">
                            @if ($topic->image)
                                <img src="{{ $topic->image }}" alt="{{ $topic->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#005366] to-[#003d4d] text-white font-bold text-center p-4">
                                    {{ $topic->name }}
                                </div>
                            @endif
                        </a>
                        <p class="font-bold text-gray-900 mb-2">{{ $topic->name }}</p>
                        <div class="flex flex-col gap-2">
                            @foreach ($topicProductTags as $tag)
                                <a href="{{ route('search', ['q' => $topic->name . ' ' . $tag['label']]) }}"
                                   class="inline-flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm font-medium {{ $tag['tone'] }}">
                                    <span>{{ $tag['label'] }}</span>
                                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-base text-gray-500">No trending topics yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Gen AI floating action (mobile) --}}
    <button type="button"
            id="gen-ai-fab"
            class="header-panel-btn gen-ai-fab lg:hidden fixed bottom-6 right-4 z-40 w-14 h-14 rounded-full bg-[#e2150c] text-white shadow-lg hover:bg-[#c0120a] transition flex items-center justify-center"
            data-panel="studio"
            aria-label="{{ $studio['title'] }}"
            aria-expanded="false"
            title="{{ $studio['title'] }}">
        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9.5 3.2l1.15 3.1 3.15 1.15-3.15 1.15-1.15 3.1-1.15-3.1L5.2 7.45l3.15-1.15L9.5 3.2zM17.4 9.2l.85 2.2 2.25.85-2.25.85-.85 2.2-.85-2.2-2.25-.85 2.25-.85.85-2.2zM13.8 14.6l.7 1.85 1.9.7-1.9.7-.7 1.85-.7-1.85-1.9-.7 1.9-.7.7-1.85z"/>
        </svg>
    </button>

    <div id="header-panel-backdrop" class="fixed inset-0 bg-black/40 z-[79] hidden lg:hidden" aria-hidden="true"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.getElementById('site-header');
            const panels = ['categories', 'user', 'studio'];
            const panelEls = {};
            const panelBackdrop = document.getElementById('header-panel-backdrop');
            const isMobile = () => window.innerWidth < 1024;

            // Move panels to body so they work even when parent is display:none (mobile)
            panels.forEach(function (name) {
                const el = root.querySelector('[data-panel-content="' + name + '"]');
                if (!el) return;
                document.body.appendChild(el);
                el.classList.add('header-panel-portal');
                panelEls[name] = el;
            });

            function positionPanel(name, btn) {
                const el = panelEls[name];
                if (!el || !btn) return;
                const rect = btn.getBoundingClientRect();
                const gap = 8;
                el.style.position = 'fixed';
                el.style.zIndex = '80';
                el.style.paddingTop = '0';
                el.classList.remove('header-panel--sheet');

                if (isMobile() && (name === 'user' || name === 'studio')) {
                    el.classList.add('header-panel--sheet');
                    el.style.top = 'auto';
                    el.style.bottom = '0';
                    el.style.left = '0';
                    el.style.right = '0';
                    el.style.width = '100%';
                    el.style.maxHeight = '85vh';
                    el.style.overflowY = 'auto';
                    return;
                }

                el.style.bottom = 'auto';
                el.style.maxHeight = '';
                el.style.overflowY = '';
                el.style.top = (rect.bottom + gap) + 'px';

                if (name === 'categories') {
                    el.style.left = Math.max(12, rect.left) + 'px';
                    el.style.right = 'auto';
                    el.style.width = '320px';
                } else if (name === 'studio') {
                    const width = Math.min(720, window.innerWidth - 24);
                    el.style.width = width + 'px';
                    el.style.left = Math.max(12, Math.min(rect.right - width, window.innerWidth - width - 12)) + 'px';
                    el.style.right = 'auto';
                } else {
                    const width = Math.min(340, window.innerWidth - 24);
                    el.style.width = width + 'px';
                    el.style.left = Math.max(12, Math.min(rect.right - width, window.innerWidth - width - 12)) + 'px';
                    el.style.right = 'auto';
                }
            }

            function setPanelBackdrop(show) {
                if (!panelBackdrop) return;
                panelBackdrop.classList.toggle('hidden', !show);
            }

            function closeAllPanels() {
                panels.forEach(function (name) {
                    const content = panelEls[name];
                    if (content) {
                        content.classList.add('hidden');
                        content.classList.remove('header-panel--sheet');
                    }
                    document.querySelectorAll('.header-panel-btn[data-panel="' + name + '"]').forEach(function (btn) {
                        btn.setAttribute('aria-expanded', 'false');
                        const openIcon = btn.querySelector('.panel-icon-open');
                        const closeIcon = btn.querySelector('.panel-icon-close');
                        if (openIcon) openIcon.classList.remove('hidden');
                        if (closeIcon) closeIcon.classList.add('hidden');
                    });
                });
                setPanelBackdrop(false);
                const drawer = document.getElementById('mobile-drawer');
                const searchOverlay = document.getElementById('mobile-search-overlay');
                if ((!drawer || !drawer.classList.contains('is-open')) &&
                    (!searchOverlay || searchOverlay.classList.contains('hidden'))) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function openPanel(name, triggerBtn) {
                closeMobileDrawer();
                closeMobileSearch();
                closeAllPanels();
                const content = panelEls[name];
                if (content) {
                    positionPanel(name, triggerBtn);
                    content.classList.remove('hidden');
                    if (isMobile() && (name === 'user' || name === 'studio')) {
                        setPanelBackdrop(true);
                        document.body.classList.add('overflow-hidden');
                    }
                }
                document.querySelectorAll('.header-panel-btn[data-panel="' + name + '"]').forEach(function (btn) {
                    btn.setAttribute('aria-expanded', 'true');
                    const openIcon = btn.querySelector('.panel-icon-open');
                    const closeIcon = btn.querySelector('.panel-icon-close');
                    if (openIcon) openIcon.classList.add('hidden');
                    if (closeIcon) closeIcon.classList.remove('hidden');
                });
            }

            document.querySelectorAll('.header-panel-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const name = btn.getAttribute('data-panel');
                    const content = panelEls[name];
                    const isOpen = content && !content.classList.contains('hidden');
                    if (isOpen) closeAllPanels();
                    else openPanel(name, btn);
                });
            });

            document.addEventListener('click', function (e) {
                if (e.target.closest('[data-close-panel]')) {
                    e.preventDefault();
                    closeAllPanels();
                    return;
                }
                if (e.target.closest('.header-panel') || e.target.closest('.header-panel-btn')) return;
                if (e.target === panelBackdrop) {
                    closeAllPanels();
                    return;
                }
                closeAllPanels();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeAllPanels();
                    closeMobileDrawer();
                    closeMobileSearch();
                }
            });

            window.addEventListener('resize', function () {
                closeAllPanels();
                closeMobileDrawer();
                closeMobileSearch();
            });
            window.addEventListener('scroll', function () {
                if (!isMobile()) closeAllPanels();
            }, true);

            // Announcement slider
            const slides = root.querySelectorAll('.announcement-slide');
            if (slides.length > 1) {
                let idx = 0;
                setInterval(function () {
                    slides[idx].classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
                    slides[idx].classList.remove('opacity-100', 'translate-y-0');
                    idx = (idx + 1) % slides.length;
                    slides[idx].classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
                    slides[idx].classList.add('opacity-100', 'translate-y-0');
                }, 3500);
            }

            // Portal mobile overlays + FAB to body (avoid sticky header containing block)
            ['mobile-drawer-backdrop', 'mobile-drawer', 'mobile-drawer-close', 'mobile-search-overlay', 'gen-ai-fab', 'header-panel-backdrop'].forEach(function (id) {
                const node = document.getElementById(id);
                if (node) document.body.appendChild(node);
            });

            // Mobile drawer
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileDrawer = document.getElementById('mobile-drawer');
            const mobileDrawerBackdrop = document.getElementById('mobile-drawer-backdrop');
            const mobileDrawerClose = document.getElementById('mobile-drawer-close');

            function openMobileDrawer() {
                closeAllPanels();
                closeMobileSearch();
                if (!mobileDrawer) return;
                mobileDrawer.classList.add('is-open');
                mobileDrawer.classList.remove('-translate-x-full');
                mobileDrawer.setAttribute('aria-hidden', 'false');
                if (mobileDrawerBackdrop) mobileDrawerBackdrop.classList.remove('hidden');
                if (mobileDrawerClose) {
                    mobileDrawerClose.classList.remove('hidden');
                    mobileDrawerClose.classList.add('flex');
                }
                if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'true');
                document.body.classList.add('overflow-hidden');
            }

            function closeMobileDrawer() {
                if (!mobileDrawer) return;
                mobileDrawer.classList.remove('is-open');
                mobileDrawer.classList.add('-translate-x-full');
                mobileDrawer.setAttribute('aria-hidden', 'true');
                if (mobileDrawerBackdrop) mobileDrawerBackdrop.classList.add('hidden');
                if (mobileDrawerClose) {
                    mobileDrawerClose.classList.add('hidden');
                    mobileDrawerClose.classList.remove('flex');
                }
                if (mobileMenuBtn) mobileMenuBtn.setAttribute('aria-expanded', 'false');
                if (!document.getElementById('mobile-search-overlay') || document.getElementById('mobile-search-overlay').classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const open = mobileDrawer && mobileDrawer.classList.contains('is-open');
                    if (open) closeMobileDrawer();
                    else openMobileDrawer();
                });
            }
            if (mobileDrawerBackdrop) mobileDrawerBackdrop.addEventListener('click', closeMobileDrawer);
            if (mobileDrawerClose) mobileDrawerClose.addEventListener('click', closeMobileDrawer);

            // Mobile search overlay
            const mobileSearchBtn = document.getElementById('mobile-search-btn');
            const mobileSearchOverlay = document.getElementById('mobile-search-overlay');
            const mobileSearchClose = document.getElementById('mobile-search-close');
            const mobileSearchInput = document.getElementById('mobile-search-input');

            function openMobileSearch() {
                closeAllPanels();
                closeMobileDrawer();
                if (!mobileSearchOverlay) return;
                mobileSearchOverlay.classList.remove('hidden');
                mobileSearchOverlay.setAttribute('aria-hidden', 'false');
                if (mobileSearchBtn) mobileSearchBtn.setAttribute('aria-expanded', 'true');
                document.body.classList.add('overflow-hidden');
                setTimeout(function () {
                    if (mobileSearchInput) mobileSearchInput.focus();
                }, 50);
            }

            function closeMobileSearch() {
                if (!mobileSearchOverlay) return;
                mobileSearchOverlay.classList.add('hidden');
                mobileSearchOverlay.setAttribute('aria-hidden', 'true');
                if (mobileSearchBtn) mobileSearchBtn.setAttribute('aria-expanded', 'false');
                if (!mobileDrawer || !mobileDrawer.classList.contains('is-open')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            if (mobileSearchBtn) {
                mobileSearchBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const open = mobileSearchOverlay && !mobileSearchOverlay.classList.contains('hidden');
                    if (open) closeMobileSearch();
                    else openMobileSearch();
                });
            }
            if (mobileSearchClose) mobileSearchClose.addEventListener('click', closeMobileSearch);

            const searchPlaceholders = @json($header['search_placeholders'] ?? ['Search']);
            let placeholderIndex = 0;
            function rotatePlaceholder(input) {
                if (!input || document.activeElement === input || input.value) return;
                input.setAttribute('placeholder', searchPlaceholders[placeholderIndex] || 'Search');
                placeholderIndex = (placeholderIndex + 1) % Math.max(searchPlaceholders.length, 1);
            }
            ['search-input', 'mobile-search-input'].forEach(function (id) {
                const input = document.getElementById(id);
                if (!input) return;
                rotatePlaceholder(input);
                setInterval(function () { rotatePlaceholder(input); }, 3000);
            });

            updateHeaderCartCount();
            syncHeaderWithBackend();

            let isHeaderScrolled = false;
            let expandedHeaderHeight = 0;
            let compactHeaderHeight = 0;

            function applyHeaderMetrics(scrolled) {
                const height = scrolled ? compactHeaderHeight : expandedHeaderHeight;
                if (!height) return;

                const heightValue = height + 'px';
                const stickyValue = (height + 8) + 'px';
                const previous = document.documentElement.style.getPropertyValue('--site-header-height');
                const previousSticky = document.documentElement.style.getPropertyValue('--product-show-sticky-top');

                if (previous === heightValue && previousSticky === stickyValue) {
                    return;
                }

                document.documentElement.style.setProperty('--site-header-height', heightValue);
                document.documentElement.style.setProperty('--product-show-sticky-top', stickyValue);
                window.dispatchEvent(new CustomEvent('siteHeaderMetricsUpdated', { detail: { height } }));
            }

            function measureHeaderHeights() {
                if (!root) return;

                const wasScrolled = isHeaderScrolled;
                root.classList.remove('site-header--scrolled');
                expandedHeaderHeight = root.offsetHeight;
                root.classList.add('site-header--scrolled');
                compactHeaderHeight = root.offsetHeight;

                if (wasScrolled) {
                    root.classList.add('site-header--scrolled');
                } else {
                    root.classList.remove('site-header--scrolled');
                }

                applyHeaderMetrics(isHeaderScrolled);
            }

            function syncSiteHeaderMetrics() {
                measureHeaderHeights();
            }

            window.syncSiteHeaderMetrics = syncSiteHeaderMetrics;

            function updateHeaderScrollState() {
                if (!root || !expandedHeaderHeight) return;
                const y = window.scrollY;

                if (!isHeaderScrolled && y > expandedHeaderHeight) {
                    isHeaderScrolled = true;
                    root.classList.add('site-header--scrolled');
                    applyHeaderMetrics(true);
                } else if (isHeaderScrolled && y <= 10) {
                    isHeaderScrolled = false;
                    root.classList.remove('site-header--scrolled');
                    applyHeaderMetrics(false);
                }
            }

            let headerScrollTicking = false;
            window.addEventListener('scroll', function () {
                if (!headerScrollTicking) {
                    window.requestAnimationFrame(function () {
                        updateHeaderScrollState();
                        headerScrollTicking = false;
                    });
                    headerScrollTicking = true;
                }
            }, { passive: true });

            measureHeaderHeights();
            updateHeaderScrollState();
            window.addEventListener('resize', measureHeaderHeights);
        });

        function updateHeaderCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            ['mobile-cart-count', 'desktop-cart-count'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = totalItems;
                    el.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            });
        }

        window.addEventListener('storage', function (e) {
            if (e.key === 'cart') updateHeaderCartCount();
        });
        window.addEventListener('cartUpdated', updateHeaderCartCount);

        function syncHeaderWithBackend() {
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) return;
            fetch('/api/cart/get', {
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': token.getAttribute('content') }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.cart_items) {
                    const backendCart = data.cart_items.map(item => ({
                        id: item.product_id || item.id,
                        name: item.display_name || item.product?.name,
                        price: parseFloat(item.price),
                        quantity: item.quantity,
                        selectedVariant: item.selected_variant,
                        customizations: item.customizations,
                        addedAt: Date.now()
                    }));
                    localStorage.setItem('cart', JSON.stringify(backendCart));
                    updateHeaderCartCount();
                }
            })
            .catch(() => {});
        }

        const searchInput = document.getElementById('search-input');
        const suggestionsContainer = document.getElementById('search-suggestions');
        const suggestionsContent = document.getElementById('suggestions-content');
        let searchTimeout;
        if (searchInput && suggestionsContainer) {
            searchInput.addEventListener('input', function (e) {
                const query = e.target.value.trim();
                clearTimeout(searchTimeout);
                if (query.length < 2) {
                    suggestionsContainer.classList.add('hidden');
                    return;
                }
                searchTimeout = setTimeout(() => {
                    fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.length) {
                                suggestionsContainer.classList.add('hidden');
                                return;
                            }
                            let html = '';
                            data.forEach(item => {
                                html += `<a href="${item.url}" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-lg transition">
                                    <div class="w-10 h-10 bg-gray-100 rounded overflow-hidden shrink-0">${item.image ? `<img src="${item.image}" alt="" class="w-full h-full object-cover">` : ''}</div>
                                    <div class="min-w-0 flex-1"><p class="text-sm font-bold text-gray-900 truncate">${item.name}</p></div>
                                </a>`;
                            });
                            html += `<div class="border-t border-gray-100 p-2"><a href="{{ route('search') }}?q=${encodeURIComponent(query)}" class="block text-center text-sm text-[#005366] font-bold py-2">View all results</a></div>`;
                            suggestionsContent.innerHTML = html;
                            suggestionsContainer.classList.remove('hidden');
                        })
                        .catch(() => suggestionsContainer.classList.add('hidden'));
                }, 300);
            });
            document.addEventListener('click', function (e) {
                if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.classList.add('hidden');
                }
            });
        }
    </script>
</header>
