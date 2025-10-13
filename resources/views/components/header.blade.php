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
                        +84 123 456 789
                    </span>
                    <span class="flex items-center text-gray-600">
                        <svg class="w-4 h-4 mr-2 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        info@bluprinter.com
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-xs bg-gradient-to-r from-[#005366] to-[#E2150C] text-white px-4 py-1.5 rounded-full font-semibold shadow-sm">
                        🚚 Free Shipping on Orders Over $50
                    </span>
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-[#005366] transition font-medium">Login</a>
                        <a href="{{ route('register') }}" class="text-gray-600 hover:text-[#005366] transition font-medium">Register</a>
                    @else
                        <span class="text-sm text-gray-600">Welcome, {{ auth()->user()->name }}!</span>
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-[#005366] transition font-medium">Dashboard</a>
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
                <div class="flex items-center justify-between py-3">
                    <!-- Logo -->
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-[#005366] rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold">
                                <span class="text-[#005366]">Blu</span><span class="text-gray-800">printer</span>
                            </h1>
                        </div>
                    </a>

                    <!-- Mobile Actions -->
                    <div class="flex items-center space-x-2">
                        <!-- Search Button -->
                        <button id="mobile-search-btn" class="p-2 text-gray-600 hover:text-[#005366] transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                        
                        <!-- Cart -->
                        <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-600 hover:text-[#E2150C] transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                            </svg>
                            <span id="mobile-cart-count" class="cart-count absolute -top-1 -right-1 bg-[#E2150C] text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-semibold" style="display: none;">0</span>
                        </a>

                        <!-- Menu Button -->
                        <button id="mobile-menu-btn" class="p-2 text-gray-600 hover:text-[#005366] transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Search Bar -->
                <div id="mobile-search" class="hidden pb-3">
                    <form action="#" method="GET" class="relative">
                        <div class="relative">
                            <input type="text" name="search" placeholder=""
                                   id="mobile-search-input"
                                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#005366] focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                   value="{{ request('search') }}">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Desktop Layout -->
            <div class="hidden lg:flex items-center justify-between py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
                        <div class="w-14 h-14 bg-[#005366] rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 transform group-hover:scale-105">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
                            </svg>
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
                    <form action="#" method="GET" class="relative">
                        <div class="relative">
                            <input type="text" name="search" placeholder=""
                                   id="search-input"
                                   class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-[#005366] focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white"
                                   value="{{ request('search') }}">
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
                    </form>
                </div>

                <!-- User Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Wishlist -->
                    <a href="#" class="relative p-3 text-gray-600 hover:text-[#E2150C] transition-colors group">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span class="absolute -top-1 -right-1 bg-[#E2150C] text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold">3</span>
                        <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-800 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">
                            Wishlist
                        </span>
                    </a>

                    <!-- Cart -->
                    <a href="{{ route('cart.index') }}" class="relative p-3 text-gray-600 hover:text-[#E2150C] transition-colors group">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
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
                                <div class="w-9 h-9 bg-[#005366] rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-md">
                                    {{ substr(auth()->user()->name, 0, 1) }}
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
                                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4 mr-3 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                        </svg>
                                        Dashboard
                                    </a>
                                    <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4 mr-3 text-[#005366]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                        My Orders
                                    </a>
                                    <a href="#" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">
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
                    <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t border-gray-100">
                        <a href="{{ route('home') }}" class="block px-3 py-2 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition font-medium">Home</a>
                        <a href="{{ route('products.index') }}" class="block px-3 py-2 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition font-medium {{ request()->routeIs('products.*') ? 'text-[#005366] bg-gray-50' : '' }}">Products</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition font-medium">Collections</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition font-medium">Templates</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition font-medium">Shops</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition font-medium">Blog</a>
                        <a href="#" class="block px-3 py-2 text-gray-700 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition font-medium">Help Center</a>
                        
                        <!-- Mobile Categories -->
                        <div class="border-t border-gray-100 pt-2 mt-2">
                            <div class="px-3 py-2">
                                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Categories</h3>
                            </div>
                            @php
                                $categories = \App\Models\Category::whereNull('parent_id')->take(6)->get();
                            @endphp
                            @foreach($categories as $category)
                                <a href="#" class="block px-3 py-2 text-sm text-gray-600 hover:text-[#005366] hover:bg-gray-50 rounded-lg transition">
                                    {{ $category->name }}
                                </a>
                            @endforeach
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
                                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div class="w-8 h-8 bg-gradient-to-br from-[#005366] to-[#003d4d] rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Apparel</span>
                                </a>
                                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div class="w-8 h-8 bg-gradient-to-br from-[#E2150C] to-[#c0120a] rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Accessories</span>
                                </a>
                                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div class="w-8 h-8 bg-gradient-to-br from-[#005366] to-[#003d4d] rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Home & Living</span>
                                </a>
                                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div class="w-8 h-8 bg-gradient-to-br from-[#E2150C] to-[#c0120a] rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Stationery</span>
                                </a>
                                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div class="w-8 h-8 bg-gradient-to-br from-[#005366] to-[#003d4d] rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Gifts</span>
                                </a>
                                <a href="#" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div class="w-8 h-8 bg-gradient-to-br from-[#E2150C] to-[#c0120a] rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-700">Electronics</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-[#005366] transition font-medium {{ request()->routeIs('home') ? 'text-[#005366] font-semibold' : '' }}">Home</a>
                        <a href="{{ route('products.index') }}" class="text-gray-700 hover:text-[#005366] transition font-medium {{ request()->routeIs('products.*') ? 'text-[#005366] font-semibold' : '' }}">Products</a>
                    <a href="#" class="text-gray-700 hover:text-[#005366] transition font-medium">Collections</a>
                    <a href="#" class="text-gray-700 hover:text-[#005366] transition font-medium">Posts</a>
                    <a href="#" class="text-gray-700 hover:text-[#005366] transition font-medium">Help Center</a>
                    
                   
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
    </script>
</header>