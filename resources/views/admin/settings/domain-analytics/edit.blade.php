@extends('layouts.admin')

@section('title', 'Chỉnh sửa cấu hình Google Analytics cho Domain')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Chỉnh sửa cấu hình Google Analytics</h1>
        <p class="text-gray-600">
            Cập nhật cấu hình Google Analytics cho domain: <strong>{{ $config->domain }}</strong>
        </p>
    </div>

    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <form method="POST" action="{{ route('admin.settings.domain-analytics.update', $config->id) }}" enctype="multipart/form-data" class="p-6 space-y-6">
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
                    value="{{ old('property_id', $config->property_id) }}"
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
                    File Credentials JSON
                </label>
                @if($config->credentials_file)
                    <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-700 mb-1">
                            <strong>File hiện tại:</strong> 
                            <span class="font-mono text-xs">{{ basename($config->credentials_file) }}</span>
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
                <p class="mt-2 text-xs text-gray-500">
                    Tải lên file JSON credentials mới để thay thế file hiện tại. Để trống nếu muốn giữ nguyên file cũ.
                </p>
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

            <div>
                <label for="notes" class="block text-sm font-semibold text-gray-900 mb-1">
                    Ghi chú (tùy chọn)
                </label>
                <textarea
                    name="notes"
                    id="notes"
                    rows="3"
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200/50 @error('notes') border-red-500 @enderror"
                >{{ old('notes', $config->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.settings.domain-analytics.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    Hủy
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

