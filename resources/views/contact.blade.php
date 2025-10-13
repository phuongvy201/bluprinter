@extends('layouts.app')

@section('content')
<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-blue-800">
    <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Liên hệ với chúng tôi
            </h1>
            <p class="mt-6 max-w-3xl mx-auto text-xl text-blue-100">
                Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn
            </p>
        </div>
    </div>
</div>

<!-- Contact Form & Info -->
<div class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-8">
            <!-- Contact Form -->
            <div class="lg:col-span-1">
                <h2 class="text-2xl font-extrabold text-gray-900 sm:text-3xl">
                    Gửi tin nhắn cho chúng tôi
                </h2>
                <p class="mt-3 text-lg text-gray-500">
                    Điền thông tin bên dưới và chúng tôi sẽ liên hệ lại với bạn trong thời gian sớm nhất.
                </p>

                @if(session('success'))
                    <div class="mt-6 bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form class="mt-8 space-y-6" action="{{ route('contact.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Họ và tên *</label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" name="email" id="email" required value="{{ old('email') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-300 @enderror">
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại *</label>
                            <input type="tel" name="phone" id="phone" required value="{{ old('phone') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('phone') border-red-300 @enderror">
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Chủ đề *</label>
                            <select name="subject" id="subject" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('subject') border-red-300 @enderror">
                                <option value="">Chọn chủ đề</option>
                                <option value="tư vấn dịch vụ" {{ old('subject') == 'tư vấn dịch vụ' ? 'selected' : '' }}>Tư vấn dịch vụ</option>
                                <option value="báo giá" {{ old('subject') == 'báo giá' ? 'selected' : '' }}>Báo giá</option>
                                <option value="khiếu nại" {{ old('subject') == 'khiếu nại' ? 'selected' : '' }}>Khiếu nại</option>
                                <option value="hợp tác" {{ old('subject') == 'hợp tác' ? 'selected' : '' }}>Hợp tác</option>
                                <option value="khác" {{ old('subject') == 'khác' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('subject')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700">Tin nhắn *</label>
                        <textarea name="message" id="message" rows="4" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('message') border-red-300 @enderror" placeholder="Mô tả chi tiết về yêu cầu của bạn...">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Gửi tin nhắn
                        </button>
                    </div>
                </form>
            </div>

            <!-- Contact Information -->
            <div class="mt-12 lg:mt-0 lg:col-span-1">
                <h2 class="text-2xl font-extrabold text-gray-900 sm:text-3xl">
                    Thông tin liên hệ
                </h2>
                <p class="mt-3 text-lg text-gray-500">
                    Chúng tôi luôn sẵn sàng hỗ trợ bạn qua nhiều kênh liên lạc khác nhau.
                </p>

                <div class="mt-8 space-y-6">
                    <!-- Phone -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Điện thoại</h3>
                            <p class="mt-1 text-base text-gray-500">0123 456 789</p>
                            <p class="mt-1 text-base text-gray-500">0987 654 321</p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Email</h3>
                            <p class="mt-1 text-base text-gray-500">info@bluprinter.com</p>
                            <p class="mt-1 text-base text-gray-500">support@bluprinter.com</p>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Địa chỉ</h3>
                            <p class="mt-1 text-base text-gray-500">
                                123 Đường ABC, Phường XYZ<br>
                                Quận 1, TP. Hồ Chí Minh<br>
                                Việt Nam
                            </p>
                        </div>
                    </div>

                    <!-- Hours -->
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Giờ làm việc</h3>
                            <p class="mt-1 text-base text-gray-500">Thứ 2 - Thứ 6: 8:00 - 18:00</p>
                            <p class="mt-1 text-base text-gray-500">Thứ 7: 8:00 - 12:00</p>
                            <p class="mt-1 text-base text-gray-500">Chủ nhật: Nghỉ</p>
                        </div>
                    </div>
                </div>

                <!-- Map Placeholder -->
                <div class="mt-8">
                    <div class="bg-gray-200 rounded-lg h-64 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Bản đồ Google Maps</p>
                            <p class="text-xs text-gray-400">Tích hợp bản đồ thực tế tại đây</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<div class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">
                Câu hỏi thường gặp
            </h2>
            <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">
                Những câu hỏi phổ biến về dịch vụ của chúng tôi
            </p>
        </div>

        <div class="mt-16 max-w-3xl mx-auto">
            <div class="space-y-8">
                <!-- FAQ 1 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Thời gian giao hàng là bao lâu?</h3>
                    <p class="text-gray-600">
                        Thời gian giao hàng tùy thuộc vào loại sản phẩm và số lượng. Thông thường, 
                        các đơn hàng trong thành phố sẽ được giao trong vòng 24-48 giờ. 
                        Đơn hàng số lượng lớn có thể mất 3-5 ngày làm việc.
                    </p>
                </div>

                <!-- FAQ 2 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Có hỗ trợ thiết kế không?</h3>
                    <p class="text-gray-600">
                        Có, chúng tôi có đội ngũ thiết kế chuyên nghiệp sẵn sàng hỗ trợ bạn tạo ra 
                        những sản phẩm in ấn đẹp mắt và phù hợp với nhu cầu. 
                        Chi phí thiết kế sẽ được báo giá riêng tùy theo độ phức tạp.
                    </p>
                </div>

                <!-- FAQ 3 -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Có giảm giá cho đơn hàng số lượng lớn không?</h3>
                    <p class="text-gray-600">
                        Có, chúng tôi có chính sách giảm giá đặc biệt cho các đơn hàng số lượng lớn. 
                        Mức giảm giá sẽ tùy thuộc vào số lượng và loại sản phẩm. 
                        Liên hệ trực tiếp để được báo giá chi tiết.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
