<!-- Header Component -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <!-- Top Header Section -->
    <div class="bg-gray-50 border-b border-gray-100 hidden lg:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
            <div class="flex items-center justify-between py-3 text-sm">
                <div class="flex items-center space-x-6">
                    <span class="flex items-center text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        +18563782798
                    </span>
                    <span class="flex items-center text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        admin@bluprinter.com
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    @php
                        $domain = \App\Services\CurrencyService::getCurrentDomain();
                        $currency = \App\Services\CurrencyService::getCurrencyForDomain($domain);
                        $currencyRate = \App\Services\CurrencyService::getCurrencyRateForDomain($domain) ?? 1.0;
                        $freeShippingThreshold = 100; // USD
                        $convertedThreshold = $currency !== 'USD' 
                            ? \App\Services\CurrencyService::convertFromUSDWithRate($freeShippingThreshold, $currency, $currencyRate)
                            : $freeShippingThreshold;
                        $formattedThreshold = \App\Services\CurrencyService::formatPrice($convertedThreshold, $currency, $domain);
                    @endphp
                    <span class="text-xs bg-gradient-to-r from-[#005366] to-[#E2150C] text-white px-4 py-1.5 rounded-full font-semibold shadow-sm">
                        🚚 Free Shipping on Orders Over {{ $formattedThreshold }}
                    </span>
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-[#005366] transition font-medium">Login</a>
                        <a href="{{ route('register') }}" class="text-gray-600 hover:text-[#005366] transition font-medium">Register</a>
                    @else
                        <span class="text-sm text-gray-600">Welcome, {{ auth()->user()->name }}!</span>
                        @if(auth()->user()->hasAnyRole(['admin', 'seller', 'ad-partner']))
                            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-[#005366] transition font-medium">Dashboard</a>
                        @endif
                        @if(!auth()->user()->hasVerifiedEmail())
                            <a href="{{ route('verification.notice') }}" class="text-orange-600 hover:text-orange-700 transition font-medium">
                                Verify Email
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-[#005366] transition font-medium">Logout</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header Section -->
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
            <!-- Mobile Layout -->
            <div class="lg:hidden">
                <div class="flex items-center justify-between py-3 md:py-4">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 md:space-x-3">
                        <div class="w-10 h-10 md:w-12 md:h-12  overflow-hidden">
                            <img src="{{ asset('images/logo nhỏ.png') }}" 
                                 alt="Bluprinter Logo" 
                                 class="w-full h-full object-contain"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <!-- Fallback SVG if image fails to load -->
                            <div class="w-full h-full flex items-center justify-center" style="display: none;">
                                <svg class="w-6 h-6 md:w-7 md:h-7 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-lg md:text-xl font-bold">
                                <span class="text-[#005366]">Blu</span><span class="text-gray-800">printer</span>
                            </h1>
                            <p class="text-xs text-gray-500 -mt-1 hidden md:block">Customize Your Products</p>
                        </div>
                    </a>

                    <!-- Mobile Actions -->
                    <div class="flex items-center space-x-1 md:space-x-3">
                        <!-- Search Button -->
                        <button id="mobile-search-btn" class="p-2 md:p-3 text-gray-600 hover:text-[#005366] transition rounded-lg hover:bg-gray-50">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                        
                        <!-- Wishlist -->
                        <a href="{{ route('wishlist.index') }}" class="relative p-2 md:p-3 text-gray-600 hover:text-[#E2150C] transition rounded-lg hover:bg-gray-50">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span id="mobile-wishlist-count" class="wishlist-count absolute -top-1 -right-1 bg-[#E2150C] text-white text-xs rounded-full w-4 h-4 md:w-5 md:h-5 flex items-center justify-center font-semibold" style="display: none;">0</span>
                        </a>

                        <!-- Cart -->
                        <a href="{{ route('cart.index') }}" class="relative p-2 md:p-3 text-gray-600 hover:text-[#E2150C] transition rounded-lg hover:bg-gray-50">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span id="mobile-cart-count" class="cart-count absolute -top-1 -right-1 bg-[#E2150C] text-white text-xs rounded-full w-4 h-4 md:w-5 md:h-5 flex items-center justify-center font-semibold" style="display: none;">0</span>
                        </a>

                        <!-- Login/User Button for Mobile -->
                        @auth
                            <!-- User Avatar for Mobile -->
                            <div class="relative group">
                                <button class="p-2 md:p-3 text-gray-600 hover:text-[#005366] transition rounded-lg hover:bg-gray-50">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" 
                                             class="w-6 h-6 md:w-7 md:h-7 rounded-full object-cover">
                                    @else
                                        <div class="w-6 h-6 md:w-7 md:h-7 bg-[#005366] rounded-full flex items-center justify-center text-white font-semibold text-xs">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                </button>
                                
                                <!-- Mobile User Dropdown -->
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="py-2">
                                        <div class="px-3 py-2 border-b border-gray-100">
                                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                            <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                        </div>
                                        @if(auth()->user()->hasAnyRole(['admin', 'seller', 'ad-partner']))
                                            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                <svg class="w-4 h-4 mr-2 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                                </svg>
                                                Dashboard
                                            </a>
                                        @endif
                                        @if(!auth()->user()->hasVerifiedEmail())
                                            <a href="{{ route('verification.notice') }}" class="flex items-center px-3 py-2 text-sm text-orange-600 hover:bg-orange-50 transition">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                                Verify Email
                                            </a>
                                        @endif
                                        <a href="{{ route('customer.orders.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                            <svg class="w-4 h-4 mr-2 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                            </svg>
                                            My Orders
                                        </a>
                                        <a href="{{ route('customer.profile.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                            <svg class="w-4 h-4 mr-2 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            Profile
                                        </a>
                                        <hr class="my-1">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-[#E2150C] hover:bg-red-50 transition">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                </svg>
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Login Button for Mobile -->
                            <a href="{{ route('login') }}" class="p-2 md:p-3 text-gray-600 hover:text-[#005366] transition rounded-lg hover:bg-gray-50">
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </a>
                        @endauth

                        <!-- Menu Button -->
                        <button id="mobile-menu-btn" class="p-2 md:p-3 text-gray-600 hover:text-[#005366] transition rounded-lg hover:bg-gray-50">
                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Search Bar -->
                <div id="mobile-search" class="hidden pb-3 md:pb-4">
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <div class="relative">
                            <input type="text" name="q" placeholder="Search products, collections, shops..."
                                   id="mobile-search-input"
                                   class="w-full pl-10 pr-4 py-3 md:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#005366] focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white text-sm md:text-base"
                                   value="{{ request('q') }}">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 md:w-5 md:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <div class="bg-[#005366] text-white px-3 py-1.5 md:px-4 md:py-2 rounded-lg hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                                    <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Desktop Layout -->
            <div class="hidden lg:flex items-center justify-between py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                        <div class="w-14 h-14 overflow-hidden transition-all duration-300 transform group-hover:scale-105">
                            <img src="{{ asset('images/logo nhỏ.png') }}" 
                                 alt="Bluprinter Logo" 
                                 class="w-full h-full object-contain"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <!-- Fallback SVG if image fails to load -->
                            <div class="w-full h-full flex items-center justify-center" style="display: none;">
                                <svg class="w-8 h-8 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">
                                <span class="text-[#005366]">Blu</span><span class="text-gray-800">printer</span>
                            </h1>
                            <p class="text-xs text-gray-500 -mt-1">Customize Your Products</p>
                        </div>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="flex-1 max-w-3xl mx-8">
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <div class="relative">
                            <input type="text" name="q" placeholder="Search products, collections, shops..."
                                   id="search-input"
                                   class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-[#005366] focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                   value="{{ request('q') }}">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <button type="submit" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <div class="bg-[#005366] text-white px-5 py-2.5 rounded-xl hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                        
                        <!-- Search Suggestions Dropdown -->
                        <div id="search-suggestions" class="hidden absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 max-h-96 overflow-y-auto z-50">
                            <div id="suggestions-content" class="p-2">
                                <!-- Suggestions will be loaded here -->
                            </div>
                        </div>
                    </form>
                </div>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Wishlist -->
                    <a href="{{ route('wishlist.index') }}" class="relative p-3 text-gray-600 hover:text-[#E2150C] transition-colors group">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span id="desktop-wishlist-count" class="wishlist-count absolute -top-1 -right-1 bg-[#E2150C] text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold" style="display: none;">0</span>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-800 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">
                            Wishlist
                        </span>
                    </a>

                    <!-- Cart -->
                    <a href="{{ route('cart.index') }}" class="relative p-3 text-gray-600 hover:text-[#E2150C] transition-colors group">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        <span id="desktop-cart-count" class="cart-count absolute -top-1 -right-1 bg-[#E2150C] text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold" style="display: none;">0</span>
                        <span id="cart-tooltip" class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-800 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">
                            Cart
                        </span>
                    </a>

                    <!-- User Menu -->
                    @auth
                        <div class="relative group">
                            <button class="flex items-center space-x-2 p-2 text-gray-600 hover:text-[#005366] transition-colors">
                                <div class="header-user-avatar">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" 
                                             class="w-9 h-9 rounded-full object-cover shadow-md">
                                    @else
                                        <div class="w-9 h-9 bg-[#005366] rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="py-3">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                    </div>
                    @if(auth()->user()->hasAnyRole(['admin', 'seller', 'ad-partner']))
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4 mr-3 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                            Dashboard
                        </a>
                    @endif
                                    @if(!auth()->user()->hasVerifiedEmail())
                                        <a href="{{ route('verification.notice') }}" class="flex items-center px-4 py-3 text-sm text-orange-600 hover:bg-orange-50 transition">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            Verify Email
                                        </a>
                                    @endif
                                    <a href="{{ route('customer.orders.index') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4 mr-3 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                        My Orders
                                    </a>
                                    <a href="{{ route('customer.profile.index') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4 mr-3 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Profile
                                    </a>
                                    <hr class="my-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-4 py-3 text-sm text-[#E2150C] hover:bg-red-50 transition">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                            </svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" 
                           class="bg-[#005366] text-white px-6 py-2.5 rounded-xl hover:shadow-lg transition-all duration-200 transform hover:scale-105 font-semibold">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
            <!-- Mobile Navigation -->
            <div class="lg:hidden">
                <div id="mobile-menu" class="hidden">
                    <div class="bg-white border-t border-gray-100 max-h-[calc(100vh-200px)] overflow-y-auto">
                        <!-- Main Navigation -->
                        <div class="px-4 py-3 space-y-1">
                            <a href="{{ route('home') }}" class="flex items-center px-3 py-3 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-xl transition font-medium">
                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Home
                            </a>
                            <a href="{{ route('products.index') }}" class="flex items-center px-3 py-3 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-xl transition font-medium {{ request()->routeIs('products.*') ? 'text-[#005366] bg-gray-50' : '' }}">
                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Products
                            </a>
                            <!-- Collections Dropdown -->
                            <div class="border-t border-gray-100">
                                <button id="collections-toggle" class="w-full flex items-center justify-between px-3 py-3 text-left text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-xl transition font-medium">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        <span>Collections</span>
                                    </div>
                                    <svg id="collections-arrow" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div id="collections-content" class="hidden px-4 pb-3">
                                    @php
                                        $mobileCollections = \App\Models\Collection::with('shop')
                                            ->active()
                                            ->approved()
                                            ->latest()
                                            ->limit(6)
                                            ->get();
                                    @endphp
                                    @foreach($mobileCollections as $collection)
                                        <a href="{{ route('collections.show', $collection->slug) }}" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition">
                                            <div class="w-2 h-2 bg-[#E2150C] rounded-full mr-3"></div>
                                            {{ $collection->name }}
                                        </a>
                                    @endforeach
                                    <a href="{{ route('collections.index') }}" class="flex items-center px-3 py-2 text-sm text-[#005366] font-semibold hover:bg-gray-50 rounded-lg transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                        View All Collections
                                    </a>
                                </div>
                            </div>

                            <!-- Blog Dropdown -->
                            <div class="border-t border-gray-100">
                                <button id="blog-toggle" class="w-full flex items-center justify-between px-3 py-3 text-left text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-xl transition font-medium">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                        </svg>
                                        <span>Blog</span>
                                    </div>
                                    <svg id="blog-arrow" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div id="blog-content" class="hidden px-4 pb-3">
                                    <a href="{{ route('blog.index') }}" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition">
                                        <div class="w-2 h-2 bg-[#005366] rounded-full mr-3"></div>
                                        All Posts
                                    </a>
                                    @php
                                        $mobileBlogCategories = \App\Models\PostCategory::orderBy('sort_order')->orderBy('name')->limit(6)->get();
                                    @endphp
                                    @foreach($mobileBlogCategories as $blogCat)
                                        <a href="{{ route('blog.category', $blogCat->slug) }}" class="flex items-center justify-between px-3 py-2 text-sm text-gray-600 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition">
                                            <div class="flex items-center">
                                                @if($blogCat->icon)
                                                    <span class="mr-2 text-lg">{{ $blogCat->icon }}</span>
                                                @else
                                                    <div class="w-2 h-2 bg-[#E2150C] rounded-full mr-3"></div>
                                                @endif
                                                {{ $blogCat->name }}
                                            </div>
                                            @if($blogCat->posts_count > 0)
                                                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $blogCat->posts_count }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            <a href="#" class="flex items-center px-3 py-3 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-xl transition font-medium">
                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Shops
                            </a>
                            
                            <!-- Help Center Dropdown -->
                            <div class="border-t border-gray-100">
                                <button id="help-toggle" class="w-full flex items-center justify-between px-3 py-3 text-left text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-xl transition font-medium">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Help Center</span>
                                    </div>
                                    <svg id="help-arrow" class="w-4 h-4 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div id="help-content" class="hidden px-4 pb-3">
                                    @php
                                        // Get important pages from database (from PageSeeder) for mobile
                                        $mobileImportantPages = \App\Models\Page::where('status', 'published')
                                            ->whereIn('slug', [
                                                'faqs', 'about-us', 'privacy-policy', 'terms-of-service', 
                                                'contact-us', 'refund-policy', 'returns-exchanges-policy'
                                            ])
                                            ->orderBy('sort_order')
                                            ->get();
                                    @endphp
                                    
                                    @foreach($mobileImportantPages as $page)
                                        @php
                                            $title = $page->title;
                                            $url = '/page/' . $page->slug;
                                        @endphp
                                        <a href="{{ $url }}" class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition">
                                            <div class="w-2 h-2 bg-[#005366] rounded-full mr-3"></div>
                                            {{ $title }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center justify-center space-x-8 py-4">
                <!-- Categories Dropdown -->
                <div class="relative group">
                    <button class="flex items-center space-x-2 text-gray-700 hover:text-[#005366] transition-colors font-semibold py-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <span>Categories</span>
                        <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Categories Dropdown -->
                    <div class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">Product Categories</h3>
                            <div class="grid grid-cols-2 gap-3">
                                @php
                                    $productCategories = \App\Models\Category::whereNull('parent_id')
                                        ->orderBy('name')
                                        ->limit(8)
                                        ->get();
                                    $categoryColors = ['from-[#005366] to-[#003d4d]', 'from-[#E2150C] to-[#c0120a]'];
                                @endphp
                                @foreach($productCategories as $index => $category)
                                    <a href="{{ route('category.show', $category->slug) }}" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition group/item">
                                        <div class="w-8 h-8 bg-gradient-to-br {{ $categoryColors[$index % 2] }} rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                    </div>
                                        <span class="text-sm text-gray-700 group-hover/item:text-[#005366] font-medium">{{ $category->name }}</span>
                                    </a>
                                @endforeach
                                    </div>
                            <div class="border-t border-gray-100 mt-3 pt-3">
                                <a href="{{ route('products.index') }}" class="flex items-center justify-center text-sm text-[#005366] hover:text-[#003d4d] font-semibold py-2 rounded-lg hover:bg-gray-50 transition">
                                    View All Categories
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-[#005366] transition font-medium {{ request()->routeIs('home') ? 'text-[#005366] font-semibold' : '' }}">Home</a>
                        <a href="{{ route('products.index') }}" class="text-gray-700 hover:text-[#005366] transition font-medium {{ request()->routeIs('products.*') ? 'text-[#005366] font-semibold' : '' }}">Products</a>
                    
                    <!-- Collections Dropdown -->
                    <div class="relative group">
                        <a href="{{ route('collections.index') }}" class="flex items-center space-x-1 text-gray-700 hover:text-[#005366] transition font-medium {{ request()->routeIs('collections.*') ? 'text-[#005366] font-semibold' : '' }}">
                            <span>Collections</span>
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        
                        <!-- Collections Dropdown -->
                        <div class="absolute left-0 mt-2 w-96 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Latest Collections</h3>
                                <div class="grid grid-cols-2 gap-3">
                                    @php
                                        $latestCollections = \App\Models\Collection::with('shop')
                                            ->active()
                                            ->approved()
                                            ->latest()
                                            ->limit(6)
                                            ->get();
                                    @endphp
                                    @foreach($latestCollections as $collection)
                                        <a href="{{ route('collections.show', $collection->slug) }}" class="group/item">
                                            <div class="relative aspect-[4/3] rounded-lg overflow-hidden bg-gray-100 mb-2">
                                                @if($collection->image)
                                                    <img src="{{ $collection->image }}" 
                                                         alt="{{ $collection->name }}"
                                                         class="w-full h-full object-cover group-hover/item:scale-110 transition-transform duration-300">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#005366] to-[#003d4d]">
                                                        <svg class="w-8 h-8 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div class="absolute bottom-2 left-2">
                                                    <span class="inline-block px-2 py-0.5 bg-white/90 backdrop-blur text-gray-900 text-xs font-semibold rounded-full">
                                                        {{ $collection->active_products_count }} items
                                                    </span>
                                                </div>
                                            </div>
                                            <h4 class="text-sm font-semibold text-gray-900 group-hover/item:text-[#005366] line-clamp-2 transition">
                                                {{ $collection->name }}
                                            </h4>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-100 mt-3 pt-3">
                                    <a href="{{ route('collections.index') }}" class="flex items-center justify-center text-sm text-[#005366] hover:text-[#003d4d] font-semibold py-2 rounded-lg hover:bg-gray-50 transition">
                                        View All Collections
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Blog Dropdown -->
                    <div class="relative group">
                        <a href="{{ route('blog.index') }}" class="flex items-center space-x-1 text-gray-700 hover:text-[#005366] transition font-medium {{ request()->routeIs('blog.*') ? 'text-[#005366] font-semibold' : '' }}">
                            <span>Blog</span>
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </a>
                        
                        <!-- Blog Categories Dropdown -->
                        <div class="absolute left-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Blog Categories</h3>
                                <div class="space-y-1">
                                    @php
                                        $postCategories = \App\Models\PostCategory::orderBy('sort_order')->orderBy('name')->get();
                                    @endphp
                                    @foreach($postCategories as $postCategory)
                                        <a href="{{ route('blog.category', $postCategory->slug) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition group/item">
                                            <div class="flex items-center space-x-3">
                                                @if($postCategory->icon)
                                                    <span class="text-xl">{{ $postCategory->icon }}</span>
                                                @else
                                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: {{ $postCategory->color ?? '#005366' }}20;">
                                                        <svg class="w-4 h-4" style="color: {{ $postCategory->color ?? '#005366' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <span class="text-sm text-gray-700 group-hover/item:text-[#005366]">{{ $postCategory->name }}</span>
                                            </div>
                                            @if($postCategory->posts_count > 0)
                                                <span class="text-xs text-gray-400">{{ $postCategory->posts_count }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                                <div class="border-t border-gray-100 mt-3 pt-3">
                                    <a href="{{ route('blog.index') }}" class="flex items-center justify-center text-sm text-[#005366] hover:text-[#003d4d] font-semibold py-2 rounded-lg hover:bg-gray-50 transition">
                                        View All Posts
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Help Center Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center space-x-1 text-gray-700 hover:text-[#005366] transition font-medium">
                            <span>Help Center</span>
                            <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Help Center Dropdown -->
                        <div class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Help & Support</h3>
                                <div class="grid grid-cols-1 gap-2">
                                    @php
                                        // Get important pages from database (from PageSeeder)
                                        $importantPages = \App\Models\Page::where('status', 'published')
                                            ->whereIn('slug', [
                                                'faqs', 'about-us', 'privacy-policy', 'terms-of-service', 
                                                'contact-us', 'refund-policy', 'returns-exchanges-policy'
                                            ])
                                            ->orderBy('sort_order')
                                            ->get();
                                        
                                        // Fallback data if page doesn't exist
                                        $fallbackPages = [
                                            'faqs' => ['title' => 'FAQ', 'excerpt' => 'Frequently Asked Questions'],
                                            'about-us' => ['title' => 'About Us', 'excerpt' => 'Learn more about our company'],
                                            'privacy-policy' => ['title' => 'Privacy Policy', 'excerpt' => 'How we protect your data'],
                                            'terms-of-service' => ['title' => 'Terms of Service', 'excerpt' => 'Terms and conditions'],
                                            'contact-us' => ['title' => 'Contact Us', 'excerpt' => 'Get in touch with us'],
                                            'refund-policy' => ['title' => 'Refund Policy', 'excerpt' => 'Our refund policy'],
                                            'returns-exchanges-policy' => ['title' => 'Returns & Exchanges', 'excerpt' => 'Return and exchange policy']
                                        ];
                                    @endphp
                                    
                                    @foreach($importantPages as $page)
                                        @php
                                            $slug = $page->slug;
                                            $title = $page->title;
                                            $excerpt = $page->excerpt ?? ($fallbackPages[$slug]['excerpt'] ?? '');
                                            $url = '/page/' . $page->slug;
                                        @endphp
                                        <a href="{{ $url }}" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition group/item">
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-[#005366] to-[#003d4d]">
                                                @if($slug === 'faqs')
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @elseif($slug === 'about-us')
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @elseif($slug === 'privacy-policy')
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                    </svg>
                                                @elseif($slug === 'terms-of-service')
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                @elseif($slug === 'contact-us')
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                @elseif($slug === 'refund-policy')
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                                    </svg>
                                                @elseif($slug === 'returns-exchanges-policy')
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 group-hover/item:text-[#005366] transition">{{ $title }}</p>
                                                <p class="text-xs text-gray-500 line-clamp-1">{{ $excerpt }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </nav>

    <!-- JavaScript for Mobile Menu and Search Animation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileSearchBtn = document.getElementById('mobile-search-btn');
            const mobileSearch = document.getElementById('mobile-search');

            // Toggle mobile menu
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                    mobileSearch.classList.add('hidden'); // Hide search when menu opens
                });
            }

            // Collapsible sections functionality
            const collectionsToggle = document.getElementById('collections-toggle');
            const collectionsContent = document.getElementById('collections-content');
            const collectionsArrow = document.getElementById('collections-arrow');
            
            const blogToggle = document.getElementById('blog-toggle');
            const blogContent = document.getElementById('blog-content');
            const blogArrow = document.getElementById('blog-arrow');
            
            const helpToggle = document.getElementById('help-toggle');
            const helpContent = document.getElementById('help-content');
            const helpArrow = document.getElementById('help-arrow');

            // Collections toggle
            if (collectionsToggle && collectionsContent && collectionsArrow) {
                collectionsToggle.addEventListener('click', function() {
                    const isHidden = collectionsContent.classList.contains('hidden');
                    
                    if (isHidden) {
                        collectionsContent.classList.remove('hidden');
                        collectionsArrow.style.transform = 'rotate(180deg)';
                    } else {
                        collectionsContent.classList.add('hidden');
                        collectionsArrow.style.transform = 'rotate(0deg)';
                    }
                });
            }

            // Blog toggle
            if (blogToggle && blogContent && blogArrow) {
                blogToggle.addEventListener('click', function() {
                    const isHidden = blogContent.classList.contains('hidden');
                    
                    if (isHidden) {
                        blogContent.classList.remove('hidden');
                        blogArrow.style.transform = 'rotate(180deg)';
                    } else {
                        blogContent.classList.add('hidden');
                        blogArrow.style.transform = 'rotate(0deg)';
                    }
                });
            }

            // Help toggle
            if (helpToggle && helpContent && helpArrow) {
                helpToggle.addEventListener('click', function() {
                    const isHidden = helpContent.classList.contains('hidden');
                    
                    if (isHidden) {
                        helpContent.classList.remove('hidden');
                        helpArrow.style.transform = 'rotate(180deg)';
                    } else {
                        helpContent.classList.add('hidden');
                        helpArrow.style.transform = 'rotate(0deg)';
                    }
                });
            }

            // Toggle mobile search
            if (mobileSearchBtn && mobileSearch) {
                mobileSearchBtn.addEventListener('click', function() {
                    mobileSearch.classList.toggle('hidden');
                    mobileMenu.classList.add('hidden'); // Hide menu when search opens
                });
            }

            // Close menus when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileMenuBtn.contains(event.target) && !mobileMenu.contains(event.target)) {
                    mobileMenu.classList.add('hidden');
                }
                if (!mobileSearchBtn.contains(event.target) && !mobileSearch.contains(event.target)) {
                    mobileSearch.classList.add('hidden');
                }
            });

            // Search placeholder animation
            const searchPlaceholders = [
                "Search products, templates, or collections...",
                "Find custom t-shirts...",
                "Browse personalized mugs...",
                "Discover unique gifts...",
                "Explore wall art...",
                "Search phone cases...",
                "Find stickers & decals...",
                "Browse home decor..."
            ];

            let currentIndex = 0;
            let isDeleting = false;
            let currentText = '';
            let typeSpeed = 100;
            let deleteSpeed = 50;
            let pauseTime = 2000;

            function typeWriter(input) {
                const fullText = searchPlaceholders[currentIndex];
                
                if (isDeleting) {
                    currentText = fullText.substring(0, currentText.length - 1);
                    typeSpeed = deleteSpeed;
                } else {
                    currentText = fullText.substring(0, currentText.length + 1);
                    typeSpeed = 100;
                }

                input.setAttribute('placeholder', currentText + '|');

                if (!isDeleting && currentText === fullText) {
                    typeSpeed = pauseTime;
                    isDeleting = true;
                } else if (isDeleting && currentText === '') {
                    isDeleting = false;
                    currentIndex = (currentIndex + 1) % searchPlaceholders.length;
                    typeSpeed = 500;
                }

                setTimeout(() => typeWriter(input), typeSpeed);
            }

            // Initialize typing animation for desktop search
            const desktopSearchInput = document.getElementById('search-input');
            const mobileSearchInput = document.getElementById('mobile-search-input');
            
            if (desktopSearchInput) {
                typeWriter(desktopSearchInput);
            }
            
            if (mobileSearchInput) {
                typeWriter(mobileSearchInput);
            }

            // Pause animation on focus, resume on blur
            [desktopSearchInput, mobileSearchInput].forEach(input => {
                if (input) {
                    input.addEventListener('focus', function() {
                        this.setAttribute('placeholder', 'Search products, templates, or collections...');
                    });
                    
                    input.addEventListener('blur', function() {
                        if (this.value === '') {
                            typeWriter(this);
                        }
                    });
                }
            });

            // Update cart count on page load
            updateHeaderCartCount();
            
            // Try to sync with backend on page load
            syncHeaderWithBackend();
        });

        // Function to update cart count in header
        function updateHeaderCartCount() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            
            // Update both mobile and desktop cart counts
            const mobileCartCount = document.getElementById('mobile-cart-count');
            const desktopCartCount = document.getElementById('desktop-cart-count');
            
            [mobileCartCount, desktopCartCount].forEach(element => {
                if (element) {
                    element.textContent = totalItems;
                    element.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            });

            // Update tooltip
            const cartTooltip = document.getElementById('cart-tooltip');
            if (cartTooltip && totalItems > 0) {
                const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                cartTooltip.textContent = `Cart (${totalItems} items - $${totalPrice.toFixed(2)})`;
            }
        }

        // Listen for storage changes (when cart is updated in another tab)
        window.addEventListener('storage', function(e) {
            if (e.key === 'cart') {
                updateHeaderCartCount();
            }
        });

        // Listen for custom cart update event
        window.addEventListener('cartUpdated', function() {
            updateHeaderCartCount();
        });
        
        // Function to sync header with backend
        function syncHeaderWithBackend() {
            fetch('/api/cart/get', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.cart_items) {
                    // Convert backend cart items to localStorage format
                    const backendCart = data.cart_items.map(item => ({
                        id: item.product_id,
                        name: item.product.name,
                        price: parseFloat(item.price),
                        quantity: item.quantity,
                        selectedVariant: item.selected_variant,
                        customizations: item.customizations,
                        addedAt: Date.now()
                    }));
                    
                    // Update localStorage to match backend
                    localStorage.setItem('cart', JSON.stringify(backendCart));
                    
                    // Update header count
                    updateHeaderCartCount();
                    
                    console.log('Header synced with backend');
                }
            })
            .catch(error => {
                console.error('Failed to sync header with backend:', error);
            });
        }

        // Search Suggestions/Autocomplete
        const searchInput = document.getElementById('search-input');
        const suggestionsContainer = document.getElementById('search-suggestions');
        const suggestionsContent = document.getElementById('suggestions-content');
        let searchTimeout;

        if (searchInput && suggestionsContainer) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    suggestionsContainer.classList.add('hidden');
                    return;
                }
                
                // Debounce search
                searchTimeout = setTimeout(() => {
                    fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                suggestionsContainer.classList.add('hidden');
                                return;
                            }
                            
                            // Build suggestions HTML
                            let html = '';
                            
                            data.forEach(item => {
                                if (item.type === 'product') {
                                    html += `
                                        <a href="${item.url}" class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-xl transition-colors">
                                            <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                                ${item.image ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover">` : '<div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">No img</div>'}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate">${item.name}</p>
                                                <p class="text-xs text-[#005366] font-bold">$${parseFloat(item.price).toFixed(2)}</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                                    Product
                                                </span>
                                            </div>
                                        </a>
                                    `;
                                } else if (item.type === 'collection') {
                                    html += `
                                        <a href="${item.url}" class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-xl transition-colors">
                                            <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                                ${item.image ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover">` : '<div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">No img</div>'}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate">${item.name}</p>
                                                <p class="text-xs text-gray-500">${item.products_count} products</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-800">
                                                    Collection
                                                </span>
                                            </div>
                                        </a>
                                    `;
                                } else if (item.type === 'shop') {
                                    html += `
                                        <a href="${item.url}" class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-xl transition-colors">
                                            <div class="w-12 h-12 bg-gray-100 rounded-full overflow-hidden flex-shrink-0">
                                                ${item.image ? `<img src="${item.image}" alt="${item.name}" class="w-full h-full object-cover">` : `<div class="w-full h-full flex items-center justify-center bg-[#005366] text-white font-bold">${item.name.charAt(0)}</div>`}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate">${item.name}</p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                                    Shop
                                                </span>
                                            </div>
                                        </a>
                                    `;
                                }
                            });
                            
                            // Add "View all results" link
                            html += `
                                <div class="border-t border-gray-200 mt-2 pt-2">
                                    <a href="{{ route('search') }}?q=${encodeURIComponent(query)}" class="block text-center text-sm text-[#005366] hover:text-[#003d4d] font-semibold p-3 hover:bg-gray-50 rounded-xl transition-colors">
                                        View all results for "${query}"
                                    </a>
                                </div>
                            `;
                            
                            suggestionsContent.innerHTML = html;
                            suggestionsContainer.classList.remove('hidden');
                        })
                        .catch(error => {
                            console.error('Search suggestions error:', error);
                            suggestionsContainer.classList.add('hidden');
                        });
                }, 300); // 300ms debounce
            });

            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                    suggestionsContainer.classList.add('hidden');
                }
            });

            // Show suggestions when focusing search input if it has value
            searchInput.addEventListener('focus', function() {
                if (this.value.trim().length >= 2 && suggestionsContent.innerHTML !== '') {
                    suggestionsContainer.classList.remove('hidden');
                }
            });
        }

        // Update wishlist count
        function updateWishlistCount() {
            // Always fetch from server (no localStorage)
            fetch('{{ route("wishlist.count") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const count = data.count;
                        
                        // Update both mobile and desktop wishlist counts
                        const mobileWishlistCount = document.getElementById('mobile-wishlist-count');
                        const desktopWishlistCount = document.getElementById('desktop-wishlist-count');
                        
                        [mobileWishlistCount, desktopWishlistCount].forEach(element => {
                            if (element) {
                                element.textContent = count;
                                element.style.display = count > 0 ? 'flex' : 'none';
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Failed to fetch wishlist count:', error);
                });
        }

        // Update wishlist count on page load
        updateWishlistCount();

        // Listen for custom wishlist update event
        window.addEventListener('wishlistUpdated', function() {
            updateWishlistCount();
        });
    </script>
</header>