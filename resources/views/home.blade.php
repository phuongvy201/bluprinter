@extends('layouts.app')

@section('content')

<!-- Hero Section -->
<div class="relative bg-gradient-to-br from-gray-50 to-white">
    <div class="absolute inset-0 bg-gradient-to-r from-[#005366]/5 to-[#E2150C]/5"></div>
    <div class="relative max-w-7xl mx-auto py-12 px-4 sm:py-16 md:py-24 lg:py-32 sm:px-6 lg:px-6">
        <div class="text-center">
            <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl">
                Custom Products
                <span class="block bg-gradient-to-r from-[#005366] to-[#E2150C] bg-clip-text text-transparent">
                    Made Easy
                </span>
            </h1>
            <p class="mt-4 max-w-4xl mx-auto text-base text-gray-600 sm:text-lg md:text-xl lg:text-xl leading-relaxed px-4">
                Create personalized products from professional templates. 
                From custom t-shirts to branded merchandise, bring your ideas to life with Bluprinter's 
                <span class="text-[#005366] font-semibold">premium customization platform</span>.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-6 px-4">
                <a href="#products" class="w-full sm:w-auto inline-flex items-center justify-center px-6 sm:px-10 py-3 sm:py-4 border border-transparent text-base font-medium rounded-xl sm:rounded-2xl text-white bg-gradient-to-r from-[#005366] to-[#003d4d] hover:shadow-xl transition duration-300 ease-in-out transform hover:scale-105">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Browse Products
                </a>
                <a href="#create" class="w-full sm:w-auto inline-flex items-center justify-center px-6 sm:px-10 py-3 sm:py-4 border-2 border-[#E2150C] text-base font-medium rounded-xl sm:rounded-2xl text-[#E2150C] bg-transparent hover:bg-[#E2150C] hover:text-white transition duration-300 ease-in-out">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Create Your Own
                </a>
            </div>
            
            <!-- Stats -->
            <div class="mt-12 sm:mt-16 grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8 max-w-3xl mx-auto px-4">
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-[#005366]">50K+</div>
                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Happy Customers</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-[#E2150C]">100K+</div>
                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Products Created</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl sm:text-3xl font-bold text-[#005366]">99%</div>
                    <div class="text-xs sm:text-sm text-gray-600 mt-1">Satisfaction Rate</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-16 sm:py-20 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl md:text-5xl">
                Why Choose 
                <span class="bg-gradient-to-r from-[#005366] to-[#E2150C] bg-clip-text text-transparent">Bluprinter</span>?
            </h2>
            <p class="mt-4 sm:mt-6 max-w-3xl mx-auto text-lg sm:text-xl text-gray-600 px-4">
                We provide professional customization services with cutting-edge technology and exceptional customer support.
            </p>
        </div>

        <div class="mt-12 sm:mt-16 md:mt-20 grid grid-cols-1 gap-6 sm:gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Feature 1 -->
            <div class="text-center group p-6 rounded-2xl hover:shadow-lg transition-all duration-300">
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
            <div class="text-center group p-6 rounded-2xl hover:shadow-lg transition-all duration-300">
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
            <div class="text-center group p-6 rounded-2xl hover:shadow-lg transition-all duration-300">
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
            <div class="text-center group p-6 rounded-2xl hover:shadow-lg transition-all duration-300">
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

<!-- Products Preview Section -->
<div class="py-12 sm:py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-6">
        <div class="text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 md:text-4xl">
                Sản phẩm nổi bật
            </h2>
            <p class="mt-3 sm:mt-4 max-w-2xl mx-auto text-lg sm:text-xl text-gray-500 px-4">
                Khám phá các dịch vụ in ấn phổ biến của chúng tôi
            </p>
        </div>

        <div class="mt-10 sm:mt-12 md:mt-16 grid grid-cols-1 gap-6 sm:gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Product 1 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <div class="h-48 bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                    <svg class="h-16 w-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">In tài liệu văn phòng</h3>
                    <p class="mt-2 text-gray-500">
                        In tài liệu, báo cáo, hợp đồng với chất lượng cao
                    </p>
                    <div class="mt-4">
                        <span class="text-sm font-medium text-blue-600">Từ 500đ/trang</span>
                    </div>
                </div>
            </div>

            <!-- Product 2 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <div class="h-48 bg-gradient-to-r from-green-400 to-green-600 flex items-center justify-center">
                    <svg class="h-16 w-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">In ảnh kỹ thuật số</h3>
                    <p class="mt-2 text-gray-500">
                        In ảnh chất lượng cao trên giấy ảnh chuyên dụng
                    </p>
                    <div class="mt-4">
                        <span class="text-sm font-medium text-blue-600">Từ 3.000đ/ảnh</span>
                    </div>
                </div>
            </div>

            <!-- Product 3 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <div class="h-48 bg-gradient-to-r from-purple-400 to-purple-600 flex items-center justify-center">
                    <svg class="h-16 w-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900">In banner quảng cáo</h3>
                    <p class="mt-2 text-gray-500">
                        In banner, poster quảng cáo với kích thước lớn
                    </p>
                    <div class="mt-4">
                        <span class="text-sm font-medium text-blue-600">Từ 50.000đ/m²</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('products.index') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition duration-150 ease-in-out">
                Xem tất cả sản phẩm
                <svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </a>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-blue-600">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:py-12 md:py-16 sm:px-6 lg:px-6 lg:flex lg:items-center lg:justify-between">
        <div class="text-center lg:text-left">
            <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white md:text-4xl">
                <span class="block">Sẵn sàng bắt đầu?</span>
                <span class="block text-blue-200 text-lg sm:text-xl md:text-2xl">Liên hệ ngay để được tư vấn miễn phí</span>
            </h2>
            <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center lg:justify-start lg:mt-0">
                <div class="inline-flex rounded-md shadow">
                    <a href="{{ route('contact') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 transition duration-150 ease-in-out">
                        Liên hệ ngay
                    </a>
                </div>
                <div class="inline-flex rounded-md shadow">
                    <a href="tel:0123456789" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-500 hover:bg-blue-400 transition duration-150 ease-in-out">
                        📞 0123 456 789
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
