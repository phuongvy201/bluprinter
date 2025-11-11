<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5T5M2NG4');</script>
    <!-- End Google Tag Manager -->
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17718009492"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-17718009492');
    </script>
    <!-- End Google tag -->
    
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '663127653502118');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=663127653502118&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Bluprinter') }} - {{ $title ?? 'Home' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Global CSS for select styling -->
    <style>
    /* Hide default select arrows globally */
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
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5T5M2NG4"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    
    <div class="min-h-screen">
        <!-- Header Component -->
        <x-header />

        <!-- Email Verification Notice -->
        @auth
            @if(!auth()->user()->hasVerifiedEmail())
                <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                        <div class="flex items-center justify-between flex-wrap">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-sm md:text-base font-medium">
                                    Please verify your email address to access all features.
                                </p>
                            </div>
                            <div class="flex items-center space-x-3 mt-2 sm:mt-0">
                                <a href="{{ route('verification.notice') }}" class="text-sm font-semibold underline hover:text-orange-100 transition">
                                    Click here to verify
                                </a>
                                <form method="POST" action="{{ route('verification.send') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm font-semibold bg-white text-orange-600 px-4 py-1.5 rounded-lg hover:bg-orange-50 transition">
                                        Resend Email
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endauth

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-[#283044] text-white">
            <!-- Service Guarantees Section -->
            <div class="border-b border-gray-600">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                        <!-- Worldwide Shipping -->
                        <div class="flex flex-col items-center text-center group">
                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mb-4 group-hover:bg-blue-600 transition-colors">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold mb-2">Worldwide Shipping</h3>
                            <p class="text-sm text-gray-300 mb-3">Available as Standard or Express delivery</p>
                            <a href="{{ route('page.show', 'shipping-delivery') }}" class="text-blue-400 text-sm hover:text-blue-300 transition-colors">Learn more</a>
                        </div>

                        <!-- Secure Payments -->
                        <div class="flex flex-col items-center text-center group">
                            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mb-4 group-hover:bg-green-600 transition-colors">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold mb-2">Secure Payments</h3>
                            <p class="text-sm text-gray-300 mb-3">100% Secure payment with 256-bit SSL Encryption</p>
                            <a href="{{ route('page.show', 'privacy-policy') }}" class="text-blue-400 text-sm hover:text-blue-300 transition-colors">Learn more</a>
                        </div>

                        <!-- Free Return -->
                        <div class="flex flex-col items-center text-center group">
                            <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center mb-4 group-hover:bg-orange-600 transition-colors">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold mb-2">Free Return</h3>
                            <p class="text-sm text-gray-300 mb-3">Exchange or money back guarantee for all orders</p>
                            <a href="{{ route('page.show', 'returns-exchanges-policy') }}" class="text-blue-400 text-sm hover:text-blue-300 transition-colors">Learn more</a>
                        </div>

                        <!-- Local Support -->
                        <div class="flex flex-col items-center text-center group">
                            <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mb-4 group-hover:bg-purple-600 transition-colors">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold mb-2">Local Support</h3>
                            <p class="text-sm text-gray-300 mb-3">24/7 Dedicated support</p>
                            <a href="{{ route('page.show', 'contact-us') }}" class="text-blue-400 text-sm hover:text-blue-300 transition-colors">Submit a request</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Footer Content -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                    <!-- Company Info -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 overflow-hidden">
                                <img src="{{ asset('images/logo nhỏ.png') }}" 
                                     alt="Bluprinter Logo" 
                                     class="w-full h-full object-contain"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <!-- Fallback SVG if image fails to load -->
                                <div class="w-full h-full bg-gradient-to-br from-[#005366] to-[#003d4d] rounded-xl flex items-center justify-center shadow-lg" style="display: none;">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">
                                    <span class="text-[#005366]">Blu</span><span class="text-white">printer</span>
                                </h2>
                                <p class="text-sm text-gray-400">spice up your life</p>
                            </div>
                        </div>
                        <p class="text-gray-300 text-sm leading-relaxed mb-6">
                            Bluprinter.com is a global online marketplace, where people come together to make, sell, buy, and collect unique items. 
                            There's no Bluprinter warehouse – just independent sellers selling the things they love. 
                            We make the whole process easy, helping you connect directly with makers to find something extraordinary.
                        </p>
                        
                        <!-- Company Information -->
                        <div class="text-gray-300 text-xs leading-relaxed mb-4">
                            <p class="mb-2"><strong>The website is jointly operated by:</strong></p>
                            <p class="mb-1">• HM FULFILL COMPANY LIMITED – Registered address: 63/9Đ, Ap Chanh 1, Tan Xuan, Hoc Mon, Ho Chi Minh City 700000, Vietnam</p>
                            <p class="mb-1">• BLUE STAR TRADING LIMITED – Registered at: RM C, 6/F, WORLD TRUST TOWER, 50 STANLEY STREET, CENTRAL, HONG KONG</p>
                            <p class="mb-1">• Bluprinter LTD (UK) – Company Number: 16342615, Registered address: 71-75 Shelton Street, Covent Garden, London, United Kingdom, WC2H 9JQ</p>
                            <p class="mb-3">• Bluprinter LLC (US) – Address: 5900 BALCONES DR STE 100, AUSTIN, TX 78731, USA</p>
                            
                            <p class="mb-1"><strong>US Warehouse Address:</strong> 1301 E ARAPAHO RD, STE 101 RICHARDSON, TX 75081, USA</p>
                            <p class="mb-3"><strong>UK Warehouse Address:</strong> 3 Kincraig Rd, Blackpool FY2 0FY, United Kingdom</p>
                        </div>
                        
                        <!-- Social Media -->
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-300 mb-3">Follow us:</h3>
                            <div class="flex flex-wrap gap-3">
                                <!-- Facebook -->
                                <a href="https://www.facebook.com/profile.php?id=61571564261584" target="_blank" class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                                <!-- Instagram -->
                                <a href="https://www.instagram.com/blu.printer" target="_blank" class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center hover:from-purple-600 hover:to-pink-600 transition-colors">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987s11.987-5.367 11.987-11.987C24.014 5.367 18.647.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.708 13.744 3.708 12.447s.49-2.448 1.297-3.323c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.807.875 1.297 2.026 1.297 3.323s-.49 2.448-1.297 3.323c-.875.807-2.026 1.297-3.323 1.297z"/>
                                    </svg>
                                </a>
                                <!-- YouTube -->
                                <a href="https://www.youtube.com/@BLUPRINTER" target="_blank" class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center hover:bg-red-700 transition-colors">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                </a>
                                <!-- TikTok -->
                                <a href="https://www.tiktok.com/@blu.printer" target="_blank" class="w-8 h-8 bg-black rounded-full flex items-center justify-center hover:bg-gray-800 transition-colors">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                    </svg>
                                </a>
                                <!-- Pinterest -->
                                <a href="https://www.pinterest.com/bluprinter/" target="_blank" class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.748-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z"/>
                                    </svg>
                                </a>
                                <!-- Twitter -->
                                <a href="https://x.com/Bluprinter25" target="_blank" class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center hover:bg-blue-500 transition-colors">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <a href="/support/ticket" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md border border-blue-500 text-blue-100 hover:bg-blue-600/20 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 19h14M5 15h14"/></svg>
                                    Submit Ticket
                                </a>
                                <a href="/support/request" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md border border-purple-500 text-purple-100 hover:bg-purple-600/20 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>
                                    Submit Request
                                </a>
                                <a href="/bulk-order" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md border border-indigo-500 text-indigo-100 hover:bg-indigo-600/20 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                                    Bulk Order
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Company -->
                    <div>
                        <h3 class="text-lg font-bold mb-4">Company</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="/page/about-us" class="text-gray-300 hover:text-white transition-colors">About Us</a></li>
                            <li><a href="/page/privacy-policy" class="text-gray-300 hover:text-white transition-colors">Privacy Policy</a></li>
                            <li><a href="/page/terms-of-service" class="text-gray-300 hover:text-white transition-colors">Terms of Service</a></li>
                            <li><a href="/page/secure-payments" class="text-gray-300 hover:text-white transition-colors">Secure Payments</a></li>
                            <li><a href="/contact-us" class="text-gray-300 hover:text-white transition-colors">Contact Us</a></li>
                            <li><a href="/help-center" class="text-gray-300 hover:text-white transition-colors">Help Center</a></li>
                            <li><a href="/sitemap" class="text-gray-300 hover:text-white transition-colors">Sitemap</a></li>
                        </ul>
                    </div>

                    <!-- Get Help -->
                    <div>
                        <h3 class="text-lg font-bold mb-4">Get Help</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="/page/faqs" class="text-gray-300 hover:text-white transition-colors">FAQs</a></li>
                            <li><a href="/order-tracking" class="text-gray-300 hover:text-white transition-colors">Order Tracking</a></li>
                            <li><a href="/shipping-delivery" class="text-gray-300 hover:text-white transition-colors">Shipping & Delivery</a></li>
                            <li><a href="/page/cancelchange-order" class="text-gray-300 hover:text-white transition-colors">Cancel/Change Order</a></li>
                            <li><a href="/page/refund-policy" class="text-gray-300 hover:text-white transition-colors">Refund Policy</a></li>
                            <li><a href="/page/returns-exchanges-policy" class="text-gray-300 hover:text-white transition-colors">Returns & Exchanges Policy</a></li>
                            <li><a href="/page/dmca" class="text-gray-300 hover:text-white transition-colors">DMCA</a></li>
                            <li><a href="/page/our-intellectual-property-policy" class="text-gray-300 hover:text-white transition-colors">Our Intellectual Property Policy</a></li>
                            
                        </ul>
                    </div>

                    <!-- Shop -->
                    <div>
                        <h3 class="text-lg font-bold mb-4">Shop</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="/bulk-order" class="text-gray-300 hover:text-white transition-colors">Bulk Order</a></li>
                            <li><a href="/promo-code" class="text-gray-300 hover:text-white transition-colors">Promo Code</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Newsletter & Trust Badges -->
                <div class="mt-12 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="flex flex-wrap gap-3">
                            <!-- DMCA Badge -->
                            <a href="https://www.dmca.com/Protection/Status.aspx?id=7afce096-ea62-47a0-8c3b-a3fbd663bf4d&refurl=https%3a%2f%2fbluprinter.com%2f&rlo=true" target="_blank">
                                <img src="https://images.dmca.com/Badges/DMCA_logo-grn-btn150w.png?ID=005e124c-c682-4f1d-a564-1bc657921504" alt="DMCA" class="h-8">
                            </a>
                            
                            <!-- Trustpilot Widget Placeholder -->
                            <div class="bg-gray-700 px-3 py-2 rounded text-white text-xs">
                                <a href="https://www.trustpilot.com/review/bluprinter.com" target="_blank" class="hover:text-blue-300">
                                    Trustpilot
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-lg font-bold">Never miss out on a moment</h3>
                        <p class="text-sm text-gray-300">
                            Stay updated with the latest trends, exclusive offers, and exciting updates by signing up for our newsletter. 
                            Secret privileges for your purchase will be delivered straight to your inbox.
                        </p>
                        
                        <form class="flex" id="newsletter-form">
                            @csrf
                            <input type="email" name="email" id="newsletter-email" placeholder="Your email address" required
                                   class="flex-1 px-4 py-3 bg-gray-700 border border-gray-600 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-white placeholder-gray-400">
                            <button type="submit" id="newsletter-submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-r-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </form>
                        <div id="newsletter-message" class="mt-2 text-sm hidden"></div>
                        <p class="text-xs text-gray-400">
                            By clicking Subscribe, you agree to our 
                            <a href="/page/privacy-policy" class="text-blue-400 hover:text-blue-300">Privacy Policy</a> 
                            and to receive our promotional emails (opt out anytime).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Footer -->
            <div class="border-t border-gray-600">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                        <!-- Country Selector -->
                        <div class="flex items-center space-x-2">
                            <img src="https://flagcdn.com/w20/vn.png" alt="Vietnam" class="w-5 h-4">
                            <span class="text-sm text-gray-300">Vietnam</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>

                        <!-- Copyright -->
                        <div class="text-sm text-gray-400 text-center">
                            © Bluprinter. All Rights Reserved.
                        </div>

                        <!-- Payment Methods -->
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-5 bg-white rounded flex items-center justify-center">
                                <span class="text-xs font-bold text-blue-600">AE</span>
                            </div>
                            <div class="w-8 h-5 bg-blue-600 rounded flex items-center justify-center">
                                <span class="text-xs font-bold text-white">V</span>
                            </div>
                            <div class="w-8 h-5 bg-red-600 rounded flex items-center justify-center">
                                <span class="text-xs font-bold text-white">MC</span>
                            </div>
                            <div class="w-8 h-5 bg-black rounded flex items-center justify-center">
                                <span class="text-xs font-bold text-white">PP</span>
                            </div>
                            <div class="w-8 h-5 bg-gray-800 rounded flex items-center justify-center">
                                <span class="text-xs font-bold text-white">AP</span>
                            </div>
                            <div class="w-8 h-5 bg-gray-900 rounded flex items-center justify-center">
                                <span class="text-xs font-bold text-white">AP</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Newsletter Subscription JavaScript -->
    <script>
        document.getElementById('newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('newsletter-email').value;
            const button = document.getElementById('newsletter-submit');
            const messageDiv = document.getElementById('newsletter-message');
            const originalText = button.innerHTML;
            
            // Validate email
            if (!email || !/\S+@\S+\.\S+/.test(email)) {
                showMessage('Please enter a valid email address', 'error');
                return;
            }
            
            // Show loading state
            button.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            button.disabled = true;
            
            // Make API call
            fetch('{{ route("newsletter.subscribe") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    document.getElementById('newsletter-email').value = '';
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Newsletter subscription error:', error);
                showMessage('Something went wrong. Please try again later.', 'error');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
        
        function showMessage(message, type) {
            const messageDiv = document.getElementById('newsletter-message');
            messageDiv.textContent = message;
            messageDiv.className = `mt-2 text-sm ${type === 'success' ? 'text-green-400' : 'text-red-400'}`;
            messageDiv.classList.remove('hidden');
            
            // Hide message after 5 seconds
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 5000);
        }
    </script>
    
    <!-- Wishlist JavaScript -->
    <script src="{{ asset('js/wishlist.js') }}"></script>
</body>
</html>