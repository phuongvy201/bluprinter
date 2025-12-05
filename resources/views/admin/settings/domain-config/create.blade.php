@extends('layouts.admin')

@section('title', 'Thêm cấu hình Domain')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Thêm cấu hình Domain</h1>
        <p class="text-gray-600">
            Tạo cấu hình Currency và Google Analytics cho domain. Bạn có thể tạo một hoặc cả hai cấu hình cho cùng một domain.
        </p>
    </div>

    <!-- Currency Form -->
    <div class="bg-white shadow-md rounded-2xl overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Cấu hình Currency</h2>
            <p class="text-sm text-gray-600 mt-1">Cấu hình loại tiền tệ cho domain</p>
        </div>
        <form method="POST" action="{{ route('admin.settings.domain-config.store-currency') }}" class="p-6 space-y-6">
            @csrf

            <div>
                <label for="currency_domain" class="block text-sm font-semibold text-gray-900 mb-1">
                    Domain <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="domain"
                    id="currency_domain"
                    value="{{ old('domain') }}"
                    placeholder="bluprinter.com"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('domain') border-red-500 @enderror"
                >
                @error('domain')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Nhập domain (ví dụ: bluprinter.com, bluprinter.vn). Không bao gồm http:// hoặc https://
                </p>
            </div>

            <div>
                <label for="currency" class="block text-sm font-semibold text-gray-900 mb-1">
                    Currency <span class="text-red-500">*</span>
                </label>
                <select
                    name="currency"
                    id="currency"
                    required
                    onchange="updateCurrencyRate(this.value)"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('currency') border-red-500 @enderror"
                >
                    <option value="">-- Chọn Currency --</option>
                    @foreach($currencies as $code => $label)
                        <option value="{{ $code }}" {{ old('currency') === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('currency')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="currency_rate" class="block text-sm font-semibold text-gray-900 mb-1">
                    Exchange Rate (USD → Currency) <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    name="currency_rate"
                    id="currency_rate"
                    value="{{ old('currency_rate') }}"
                    placeholder="1.0"
                    step="0.000001"
                    min="0"
                    max="999999"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('currency_rate') border-red-500 @enderror"
                >
                @error('currency_rate')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Tỉ giá chuyển đổi từ USD. Ví dụ: <strong>1.0</strong> cho USD, <strong>0.79</strong> cho GBP (1 USD = 0.79 GBP), <strong>25000</strong> cho VND (1 USD = 25000 VND).
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

            <div>
                <label for="currency_notes" class="block text-sm font-semibold text-gray-900 mb-1">
                    Ghi chú (tùy chọn)
                </label>
                <textarea
                    name="notes"
                    id="currency_notes"
                    rows="3"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('notes') border-red-500 @enderror"
                    placeholder="Ví dụ: Domain cho thị trường Việt Nam"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                    Tạo cấu hình Currency
                </button>
            </div>
        </form>
    </div>

    <!-- Analytics Form -->
    <div class="bg-white shadow-md rounded-2xl overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Cấu hình Google Analytics</h2>
            <p class="text-sm text-gray-600 mt-1">Cấu hình Google Analytics riêng cho domain</p>
        </div>
        <form method="POST" action="{{ route('admin.settings.domain-config.store-analytics') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div>
                <label for="analytics_domain" class="block text-sm font-semibold text-gray-900 mb-1">
                    Domain <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="domain"
                    id="analytics_domain"
                    value="{{ old('domain') }}"
                    placeholder="example.com"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('domain') border-red-500 @enderror"
                >
                @error('domain')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Nhập domain (ví dụ: example.com, không bao gồm http:// hoặc https://)
                </p>
            </div>

            <div>
                <label for="property_id" class="block text-sm font-semibold text-gray-900 mb-1">
                    Google Analytics Property ID <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="property_id"
                    id="property_id"
                    value="{{ old('property_id') }}"
                    placeholder="123456789"
                    required
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('property_id') border-red-500 @enderror"
                >
                @error('property_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">
                    Property ID từ Google Analytics (ví dụ: 123456789)
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
                    Tải lên file JSON credentials từ Google Cloud Console. File sẽ được lưu tự động trong hệ thống.
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

            <div>
                <label for="analytics_notes" class="block text-sm font-semibold text-gray-900 mb-1">
                    Ghi chú (tùy chọn)
                </label>
                <textarea
                    name="notes"
                    id="analytics_notes"
                    rows="3"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('notes') border-red-500 @enderror"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                    Tạo cấu hình Analytics
                </button>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.settings.domain-config.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
            Quay lại
        </a>
    </div>

    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">💡 Ví dụ:</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li><strong>bluprinter.com</strong> → USD → Rate: 1.0 (1 USD = 1 USD)</li>
            <li><strong>bluprinter.vn</strong> → VND → Rate: 25000 (1 USD = 25000 VND)</li>
            <li><strong>bluprinter.co.uk</strong> → GBP → Rate: 0.79 (1 USD = 0.79 GBP)</li>
        </ul>
    </div>
</div>

<script>
function updateCurrencyRate(currency) {
    const defaultRates = @json($defaultRates);
    const rateInput = document.getElementById('currency_rate');
    
    if (rateInput && currency && defaultRates[currency]) {
        if (!rateInput.value || rateInput.value === '') {
            rateInput.value = defaultRates[currency];
        }
    }
}
</script>
@endsection

