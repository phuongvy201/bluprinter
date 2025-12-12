@extends('layouts.admin')

@section('title', 'Cấu hình Currency theo Domain')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Cấu hình Currency theo Domain</h1>
                <p class="text-gray-600">
                    Quản lý loại tiền tệ cho từng domain. Mỗi domain sẽ tự động hiển thị giá và convert sang loại tiền tệ đã cấu hình.
                </p>
            </div>
            <a href="{{ route('admin.settings.domain-currency.create') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
                + Thêm Domain
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Domain Currency Configs Table -->
    <div class="bg-white shadow-md rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Currency</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exchange Rate</th>
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
                                <span class="text-sm font-semibold text-gray-900">{{ $config->currency }}</span>
                                <span class="text-xs text-gray-500 ml-2">
                                    @if($config->currency === 'USD') $
                                    @elseif($config->currency === 'GBP') £
                                    @elseif($config->currency === 'EUR') €
                                    @elseif($config->currency === 'VND') ₫
                                    @elseif($config->currency === 'CAD') C$
                                    @elseif($config->currency === 'AUD') A$
                                    @elseif($config->currency === 'MXN') MX$
                                    @else {{ $config->currency }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($config->currency_rate)
                                    <span class="text-sm text-gray-900 font-mono">1 USD = {{ number_format($config->currency_rate, $config->currency === 'VND' || $config->currency === 'JPY' ? 0 : 6) }} {{ $config->currency }}</span>
                                @else
                                    <span class="text-xs text-gray-400">Mặc định (1.0)</span>
                                @endif
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
                                    <a href="{{ route('admin.settings.domain-currency.edit', $config->id) }}" class="text-blue-600 hover:text-blue-900">
                                        Sửa
                                    </a>
                                    <form action="{{ route('admin.settings.domain-currency.destroy', $config->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cấu hình này?');">
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
                                Chưa có cấu hình nào. <a href="{{ route('admin.settings.domain-currency.create') }}" class="text-blue-600 hover:underline">Tạo cấu hình đầu tiên</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">💡 Lưu ý:</h3>
        <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
            <li>Tất cả giá sản phẩm trong database được lưu bằng USD</li>
            <li>Hệ thống sẽ tự động convert và hiển thị giá theo currency đã cấu hình</li>
            <li>Exchange rate nên được cập nhật định kỳ để đảm bảo chính xác</li>
            <li>Nếu domain không có cấu hình, hệ thống sẽ sử dụng USD mặc định</li>
        </ul>
    </div>
</div>
@endsection














