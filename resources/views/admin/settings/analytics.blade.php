@extends('layouts.admin')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Cấu hình Tracking & Pixels</h1>
                    <p class="text-gray-600">
                        Thay đổi ID tích hợp (Meta Pixel, TikTok Pixel, Google Tag Manager, Google Ads) trực tiếp trong admin.
                        Để trống một trường sẽ quay về giá trị mặc định đang cấu hình trong hệ thống.
                    </p>
                </div>
               
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-2xl overflow-hidden">
            <form method="POST" action="{{ route('admin.settings.analytics.update') }}" class="p-6 space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_pixel_id" class="block text-sm font-semibold text-gray-900 mb-1">
                            Meta Pixel ID
                        </label>
                        <input
                            type="text"
                            name="meta_pixel_id"
                            id="meta_pixel_id"
                            value="{{ old('meta_pixel_id', $settings['meta_pixel_id']) }}"
                            placeholder="{{ $defaults['meta_pixel_id'] }}"
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50"
                        >
                        @error('meta_pixel_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            Ví dụ: <code>{{ $defaults['meta_pixel_id'] }}</code>
                        </p>
                    </div>

                    <div>
                        <label for="tiktok_pixel_id" class="block text-sm font-semibold text-gray-900 mb-1">
                            TikTok Pixel ID
                        </label>
                        <input
                            type="text"
                            name="tiktok_pixel_id"
                            id="tiktok_pixel_id"
                            value="{{ old('tiktok_pixel_id', $settings['tiktok_pixel_id']) }}"
                            placeholder="{{ $defaults['tiktok_pixel_id'] }}"
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50"
                        >
                        @error('tiktok_pixel_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            Ví dụ: <code>{{ $defaults['tiktok_pixel_id'] }}</code>
                        </p>
                    </div>

                    <div>
                        <label for="tiktok_test_event_code" class="block text-sm font-semibold text-gray-900 mb-1">
                            TikTok Test Event Code
                        </label>
                        <input
                            type="text"
                            name="tiktok_test_event_code"
                            id="tiktok_test_event_code"
                            value="{{ old('tiktok_test_event_code', $settings['tiktok_test_event_code']) }}"
                            placeholder="{{ $defaults['tiktok_test_event_code'] }}"
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50"
                        >
                        @error('tiktok_test_event_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            Để trống nếu đang chạy sự kiện live.
                        </p>
                    </div>

                    <div>
                        <label for="google_tag_manager_id" class="block text-sm font-semibold text-gray-900 mb-1">
                            Google Tag Manager ID
                        </label>
                        <input
                            type="text"
                            name="google_tag_manager_id"
                            id="google_tag_manager_id"
                            value="{{ old('google_tag_manager_id', $settings['google_tag_manager_id']) }}"
                            placeholder="{{ $defaults['google_tag_manager_id'] }}"
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50"
                        >
                        @error('google_tag_manager_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            Ví dụ: <code>{{ $defaults['google_tag_manager_id'] }}</code>
                        </p>
                    </div>

                    <div>
                        <label for="google_ads_id" class="block text-sm font-semibold text-gray-900 mb-1">
                            Google Ads / gtag ID
                        </label>
                        <input
                            type="text"
                            name="google_ads_id"
                            id="google_ads_id"
                            value="{{ old('google_ads_id', $settings['google_ads_id']) }}"
                            placeholder="{{ $defaults['google_ads_id'] }}"
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50"
                        >
                        @error('google_ads_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            Ví dụ: <code>{{ $defaults['google_ads_id'] }}</code>
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Quay lại
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection


