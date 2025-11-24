@extends('layouts.admin')

@section('title', 'Cấu hình Google Analytics theo Domain')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Cấu hình Google Analytics theo Domain</h1>
                <p class="text-gray-600">
                    Quản lý cấu hình Google Analytics riêng cho từng domain. Mỗi domain có thể có Property ID và credentials file riêng.
                </p>
            </div>
            <a href="{{ route('admin.settings.domain-analytics.create') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                + Thêm Domain
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Domain Configs Table -->
    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credentials File</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ghi chú</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($configs as $config)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $config->domain }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $config->property_id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600 font-mono">{{ $config->credentials_file ? basename($config->credentials_file) : '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($config->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Đang hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Tạm tắt
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ $config->notes ? Str::limit($config->notes, 30) : '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.settings.domain-analytics.edit', $config->id) }}" class="text-blue-600 hover:text-blue-900">
                                        Sửa
                                    </a>
                                    <form action="{{ route('admin.settings.domain-analytics.destroy', $config->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cấu hình này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Chưa có cấu hình nào. <a href="{{ route('admin.settings.domain-analytics.create') }}" class="text-blue-600 hover:underline">Tạo cấu hình đầu tiên</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.settings.analytics.edit') }}" class="text-blue-600 hover:text-blue-800">
            ← Quay lại cấu hình Tracking & Pixels
        </a>
    </div>
</div>
@endsection

