@extends('layouts.admin')

@section('title', 'Thêm cấu hình Google Merchant Center')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Thêm cấu hình Google Merchant Center</h1>
        <p class="text-gray-600">
            Tạo cấu hình GMC riêng cho một shop và thị trường cụ thể.
        </p>
    </div>

    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <form method="POST" action="{{ route('admin.settings.shop-gmc.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div>
                <label for="shop_id" class="block text-sm font-semibold text-gray-900 mb-1">
                    Shop <span class="text-red-500">*</span>
                </label>
                <select
                    name="shop_id"
                    id="shop_id"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('shop_id') border-red-500 @enderror"
                >
                    <option value="">-- Chọn shop --</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ old('shop_id') == $shop->id ? 'selected' : '' }}>
                            {{ $shop->shop_name }}
                        </option>
                    @endforeach
                </select>
                @error('shop_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-semibold text-gray-900 mb-1">
                    Tên cấu hình <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    placeholder="VD: UK Store, US Store"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('name') border-red-500 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Tên để dễ nhận biết cấu hình này (VD: UK Store, US Store)
                </p>
            </div>

            <div>
                <label for="target_country" class="block text-sm font-semibold text-gray-900 mb-1">
                    Thị trường <span class="text-red-500">*</span>
                </label>
                <select
                    name="target_country"
                    id="target_country"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('target_country') border-red-500 @enderror"
                    onchange="updateCurrencyAndLanguage(this.value)"
                >
                    <option value="">-- Chọn thị trường --</option>
                    @foreach($countries as $code => $label)
                        <option value="{{ $code }}" {{ old('target_country') == $code ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('target_country')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Chọn thị trường mục tiêu cho cấu hình này
                </p>
            </div>

            <div>
                <label for="merchant_id" class="block text-sm font-semibold text-gray-900 mb-1">
                    Merchant ID <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="merchant_id"
                    id="merchant_id"
                    value="{{ old('merchant_id') }}"
                    placeholder="123456789"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('merchant_id') border-red-500 @enderror"
                >
                @error('merchant_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Merchant ID từ Google Merchant Center
                </p>
            </div>

            <div>
                <label for="data_source_id" class="block text-sm font-semibold text-gray-900 mb-1">
                    Data Source ID
                </label>
                <input
                    type="text"
                    name="data_source_id"
                    id="data_source_id"
                    value="{{ old('data_source_id', 'PRODUCT_FEED_API') }}"
                    placeholder="PRODUCT_FEED_API"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('data_source_id') border-red-500 @enderror"
                >
                @error('data_source_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Mặc định: PRODUCT_FEED_API
                </p>
            </div>

            <div>
                <label for="credentials_file" class="block text-sm font-semibold text-gray-900 mb-1">
                    File Credentials JSON <span class="text-red-500">*</span>
                </label>
                <input
                    type="file"
                    name="credentials_file"
                    id="credentials_file"
                    accept=".json,application/json"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('credentials_file') border-red-500 @enderror"
                >
                @error('credentials_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Tải lên file JSON credentials từ Google Cloud Console
                </p>
            </div>

            <div>
                <label for="currency" class="block text-sm font-semibold text-gray-900 mb-1">
                    Currency <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="currency"
                    id="currency"
                    value="{{ old('currency') }}"
                    placeholder="GBP, USD, VND"
                    required
                    maxlength="3"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('currency') border-red-500 @enderror"
                >
                @error('currency')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Mã currency (3 ký tự): GBP, USD, VND, EUR, etc.
                </p>
            </div>

            <div>
                <label for="content_language" class="block text-sm font-semibold text-gray-900 mb-1">
                    Content Language <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="content_language"
                    id="content_language"
                    value="{{ old('content_language') }}"
                    placeholder="en, vi, de, fr"
                    required
                    maxlength="5"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('content_language') border-red-500 @enderror"
                >
                @error('content_language')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Mã ngôn ngữ: en, vi, de, fr, it, es, etc.
                </p>
            </div>

            <div>
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <span class="ml-2 text-sm text-gray-700">Kích hoạt cấu hình này</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.settings.shop-gmc.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    Hủy
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                    Tạo cấu hình
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCurrencyAndLanguage(countryCode) {
    const currencyMap = {
        'US': 'USD',
        'GB': 'GBP',
        'VN': 'VND',
        'CA': 'CAD',
        'AU': 'AUD',
        'DE': 'EUR',
        'FR': 'EUR',
        'IT': 'EUR',
        'ES': 'EUR',
    };

    const languageMap = {
        'US': 'en',
        'GB': 'en',
        'VN': 'vi',
        'CA': 'en',
        'AU': 'en',
        'DE': 'de',
        'FR': 'fr',
        'IT': 'it',
        'ES': 'es',
    };

    if (countryCode && currencyMap[countryCode]) {
        document.getElementById('currency').value = currencyMap[countryCode];
        document.getElementById('content_language').value = languageMap[countryCode] || 'en';
    }
}
</script>
@endsection

