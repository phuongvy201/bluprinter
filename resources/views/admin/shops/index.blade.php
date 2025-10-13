@extends('layouts.admin')

@section('title', 'Shop Management')

@section('content')
<div class="space-y-6 w-full max-w-full overflow-x-hidden">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">🏪 Shop Management</h1>
            <p class="mt-1 text-sm text-gray-600">All shops in the system</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-lg font-semibold">
                {{ $shops->total() }} shops
            </span>
        </div>
    </div>
    
    <!-- Shops Table -->
    @if($shops->count() > 0)
    <div class="bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden w-full max-w-full">
        <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-280px)] scrollbar-custom" 
             style="overscroll-behavior: contain;">
            <table class="min-w-[1200px] w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 sticky top-0 z-10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Shop</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Owner</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase">Products</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase">Sales</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase">Rating</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($shops as $shop)
                    <tr class="hover:bg-green-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                @if($shop->shop_logo)
                                    <img src="{{ $shop->shop_logo }}" class="w-14 h-14 rounded-lg object-cover border-2 border-gray-200">
                                @else
                                    <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg flex items-center justify-center text-2xl">
                                        🏪
                                    </div>
                                @endif
                                <div>
                                    <p class="font-bold text-gray-900">{{ $shop->shop_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $shop->shop_slug }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $shop->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-900">{{ $shop->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $shop->user->email }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col space-y-2">
                                @if($shop->verified)
                                    <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">
                                        Not Verified
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $shop->shop_status === 'active' ? 'bg-blue-100 text-blue-800' : 
                                       ($shop->shop_status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($shop->shop_status) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xl font-bold text-blue-600">{{ $shop->total_products }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xl font-bold text-green-600">{{ $shop->total_sales }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <p class="font-bold text-yellow-600 text-lg">⭐ {{ number_format($shop->rating, 1) }}</p>
                            <p class="text-xs text-gray-500">{{ $shop->total_ratings }} reviews</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center space-x-2">
                                @if(!$shop->verified)
                                    <form action="{{ route('admin.shops.verify', $shop) }}" method="POST" class="inline">
                                        @csrf
                                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xs font-semibold shadow-sm transition">
                                            ✓ Verify
                                        </button>
                                    </form>
                                @endif
                                @if($shop->shop_status !== 'suspended')
                                    <form action="{{ route('admin.shops.suspend', $shop) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to suspend this shop?')">
                                        @csrf
                                        <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-xs font-semibold shadow-sm transition">
                                            🚫 Suspend
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.shops.activate', $shop) }}" method="POST" class="inline">
                                        @csrf
                                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs font-semibold shadow-sm transition">
                                            ✓ Activate
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
        <div class="bg-white rounded-xl p-16 text-center">
            <p class="text-gray-500">No shops found</p>
        </div>
    @endif

    <!-- Pagination -->
    @if($shops->hasPages())
        <div class="bg-white px-6 py-4 rounded-xl shadow-md">
            {{ $shops->links() }}
        </div>
    @endif
</div>

<style>
/* Custom Scrollbar */
.scrollbar-custom::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}
.scrollbar-custom::-webkit-scrollbar-track {
    background: #e5e7eb;
    border-radius: 10px;
}
.scrollbar-custom::-webkit-scrollbar-thumb {
    background: linear-gradient(to right, #10b981, #14b8a6);
    border-radius: 10px;
}
</style>
@endsection

