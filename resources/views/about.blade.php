@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-blue-800">
    <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Về chúng tôi
            </h1>
            <p class="mt-6 max-w-3xl mx-auto text-xl text-blue-100">
                Bluprinter - Đối tác tin cậy cho mọi nhu cầu in ấn của bạn
            </p>
        </div>
    </div>
</div>

<!-- About Content -->
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Câu chuyện của chúng tôi
                </h2>
                <p class="mt-3 max-w-3xl text-lg text-gray-500">
                    Được thành lập vào năm 2020, Bluprinter đã không ngừng phát triển và trở thành một trong những 
                    đơn vị cung cấp dịch vụ in ấn hàng đầu tại Việt Nam. Với đội ngũ nhân viên giàu kinh nghiệm 
                    và hệ thống máy móc hiện đại, chúng tôi cam kết mang đến những sản phẩm chất lượng cao nhất.
                </p>
                <div class="mt-8 space-y-5">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-md bg-blue-500 text-white">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Chất lượng vượt trội</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Sử dụng công nghệ in ấn tiên tiến và nguyên liệu chất lượng cao
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-md bg-blue-500 text-white">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Giao hàng nhanh chóng</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Hệ thống logistics hiện đại đảm bảo giao hàng đúng hẹn
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-8 w-8 rounded-md bg-blue-500 text-white">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Giá cả hợp lý</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Báo giá minh bạch, cạnh tranh và không phát sinh chi phí ẩn
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-8 lg:mt-0">
                <div class="bg-gray-50 rounded-lg p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Số liệu ấn tượng</h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">10,000+</div>
                            <div class="text-sm text-gray-500 mt-1">Khách hàng hài lòng</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">500,000+</div>
                            <div class="text-sm text-gray-500 mt-1">Đơn hàng hoàn thành</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">99.9%</div>
                            <div class="text-sm text-gray-500 mt-1">Tỷ lệ giao hàng đúng hẹn</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">24/7</div>
                            <div class="text-sm text-gray-500 mt-1">Hỗ trợ khách hàng</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Team Section -->
<div class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Đội ngũ của chúng tôi
            </h2>
            <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">
                Những con người tài năng và nhiệt huyết đứng sau thành công của Bluprinter
            </p>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Team Member 1 -->
            <div class="text-center">
                <div class="mx-auto h-24 w-24 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                    <span class="text-white text-2xl font-bold">NT</span>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nguyễn Thành</h3>
                <p class="mt-1 text-sm text-gray-500">Giám đốc điều hành</p>
                <p class="mt-2 text-sm text-gray-600">
                    Với hơn 10 năm kinh nghiệm trong ngành in ấn, anh Thành dẫn dắt đội ngũ với tầm nhìn chiến lược.
                </p>
            </div>

            <!-- Team Member 2 -->
            <div class="text-center">
                <div class="mx-auto h-24 w-24 rounded-full bg-gradient-to-r from-green-400 to-green-600 flex items-center justify-center">
                    <span class="text-white text-2xl font-bold">LM</span>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Lê Minh</h3>
                <p class="mt-1 text-sm text-gray-500">Trưởng phòng kỹ thuật</p>
                <p class="mt-2 text-sm text-gray-600">
                    Chuyên gia về công nghệ in ấn, đảm bảo chất lượng sản phẩm luôn đạt tiêu chuẩn cao nhất.
                </p>
            </div>

            <!-- Team Member 3 -->
            <div class="text-center">
                <div class="mx-auto h-24 w-24 rounded-full bg-gradient-to-r from-purple-400 to-purple-600 flex items-center justify-center">
                    <span class="text-white text-2xl font-bold">HT</span>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Hoàng Thảo</h3>
                <p class="mt-1 text-sm text-gray-500">Trưởng phòng khách hàng</p>
                <p class="mt-2 text-sm text-gray-600">
                    Chị Thảo đảm bảo mỗi khách hàng đều nhận được dịch vụ tốt nhất và hài lòng tuyệt đối.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Mission Section -->
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-8 lg:items-center">
            <div class="mt-8 lg:mt-0">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                    Sứ mệnh của chúng tôi
                </h2>
                <p class="mt-3 max-w-3xl text-lg text-gray-500">
                    Chúng tôi cam kết mang đến những sản phẩm in ấn chất lượng cao, 
                    góp phần thúc đẩy sự phát triển của doanh nghiệp và cá nhân.
                </p>
                <div class="mt-8">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-10 w-10 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Tầm nhìn</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Trở thành đơn vị cung cấp dịch vụ in ấn hàng đầu Việt Nam với công nghệ hiện đại và dịch vụ xuất sắc.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-8 text-white">
                    <h3 class="text-2xl font-bold mb-4">Giá trị cốt lõi</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Chất lượng sản phẩm vượt trội
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Dịch vụ khách hàng chuyên nghiệp
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Giá cả minh bạch và hợp lý
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 text-green-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Đổi mới và phát triển liên tục
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
