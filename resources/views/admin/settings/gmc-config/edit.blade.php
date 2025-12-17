@extends('layouts.admin')

@section('title', 'Chỉnh sửa cấu hình Google Merchant Center')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Chỉnh sửa cấu hình Google Merchant Center</h1>
        <p class="text-gray-600">
            Cập nhật cấu hình GMC: <strong>{{ $config->name }}</strong> - Domain: <strong>{{ $config->domain }}</strong>
        </p>
    </div>

    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <form method="POST" action="{{ route('admin.settings.gmc-config.update', $config->id) }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="domain" class="block text-sm font-semibold text-gray-900 mb-1">
                    Domain <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="domain"
                    id="domain"
                    value="{{ old('domain', $config->domain) }}"
                    placeholder="bluprinter.com"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('domain') border-red-500 @enderror"
                >
                @error('domain')
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
                    value="{{ old('name', $config->name) }}"
                    placeholder="VD: UK Store, US Store"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('name') border-red-500 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
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
                        <option value="{{ $code }}" {{ old('target_country', $config->target_country) == $code ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('target_country')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="merchant_id" class="block text-sm font-semibold text-gray-900 mb-1">
                    Merchant ID <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="merchant_id"
                    id="merchant_id"
                    value="{{ old('merchant_id', $config->merchant_id) }}"
                    placeholder="123456789"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('merchant_id') border-red-500 @enderror"
                >
                @error('merchant_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="data_source_id" class="block text-sm font-semibold text-gray-900 mb-1">
                    Data Source ID
                </label>
                <input
                    type="text"
                    name="data_source_id"
                    id="data_source_id"
                    value="{{ old('data_source_id', $config->data_source_id) }}"
                    placeholder="PRODUCT_FEED_API"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('data_source_id') border-red-500 @enderror"
                >
                @error('data_source_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="credentials_file" class="block text-sm font-semibold text-gray-900 mb-1">
                    File Credentials JSON
                </label>
                @if($config->credentials_path)
                    <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-700 mb-1">
                            <strong>File hiện tại:</strong> 
                            <span class="font-mono text-xs">{{ basename($config->credentials_path) }}</span>
                        </p>
                        <p class="text-xs text-gray-500">
                            Để giữ nguyên file hiện tại, không cần chọn file mới.
                        </p>
                    </div>
                @endif
                <input
                    type="file"
                    name="credentials_file"
                    id="credentials_file"
                    accept=".json,application/json"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('credentials_file') border-red-500 @enderror"
                >
                @error('credentials_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800 mb-2">
                    <strong>Lưu ý:</strong> Currency và tỉ giá được tự động lấy từ cấu hình Domain Currency của domain này.
                </p>
                @php
                    $domainCurrency = \App\Models\DomainCurrencyConfig::getForDomain($config->domain);
                @endphp
                @if($domainCurrency)
                    <div class="text-sm text-gray-700">
                        <p><strong>Currency hiện tại:</strong> {{ $domainCurrency->currency }}</p>
                        <p><strong>Tỉ giá hiện tại:</strong> {{ $domainCurrency->currency_rate ?? 'N/A' }}</p>
                    </div>
                @else
                    <p class="text-sm text-orange-600">
                        ⚠️ Chưa có cấu hình Domain Currency cho domain này. Vui lòng tạo cấu hình Domain Currency trước.
                    </p>
                @endif
            </div>

            <div>
                <label for="content_language" class="block text-sm font-semibold text-gray-900 mb-1">
                    Content Language <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="content_language"
                    id="content_language"
                    value="{{ old('content_language', $config->content_language) }}"
                    placeholder="en, vi, de, fr"
                    required
                    maxlength="5"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('content_language') border-red-500 @enderror"
                >
                @error('content_language')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $config->is_active) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <span class="ml-2 text-sm text-gray-700">Kích hoạt cấu hình này</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.settings.gmc-config.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    Hủy
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCurrencyAndLanguage(countryCode) {
    const languageMap = {
        'US': 'en', 'GB': 'en', 'VN': 'vi', 'CA': 'en', 'AU': 'en', 'NZ': 'en',
        'DE': 'de', 'FR': 'fr', 'IT': 'it', 'ES': 'es', 'MX': 'es', 'NL': 'nl',
    };
    if (countryCode && languageMap[countryCode]) {
        document.getElementById('content_language').value = languageMap[countryCode] || 'en';
    }
}
</script>
@endsection

