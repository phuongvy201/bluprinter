@extends('layouts.admin')

@section('title', 'Edit Shipping Rate')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Shipping Rate</h1>
            <p class="mt-1 text-sm text-gray-600">Chỉnh sửa: {{ $shippingRate->name }}</p>
        </div>
        <a href="{{ route('admin.shipping-rates.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Rates
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.shipping-rates.update', $shippingRate) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
            <!-- Basic Info Section -->
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Shipping Zone -->
                    <div>
                        <label for="shipping_zone_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Shipping Zone <span class="text-red-500">*</span>
                        </label>
                        <select name="shipping_zone_id" 
                                id="shipping_zone_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('shipping_zone_id') border-red-500 @enderror"
                                required>
                            <option value="">Select Zone</option>
                            @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ old('shipping_zone_id', $shippingRate->shipping_zone_id) == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }} ({{ implode(', ', array_slice($zone->countries, 0, 3)) }}{{ count($zone->countries) > 3 ? '...' : '' }})
                            </option>
                            @endforeach
                        </select>
                        @error('shipping_zone_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Category (Optional)
                        </label>
                        <select name="category_id" 
                                id="category_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">General (All Categories)</option>
                            @foreach($categories as $category)
                            <option value="{{ $category['id'] }}" {{ old('category_id', $shippingRate->category_id) == $category['id'] ? 'selected' : '' }}>
                                @if($category['level'] == 0)
                                    📁 {{ $category['name'] }}
                                @else
                                    &nbsp;&nbsp;&nbsp;&nbsp;└─ {{ $category['name'] }}
                                @endif
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Để trống = áp dụng chung cho tất cả categories</p>
                    </div>
                </div>

                <!-- Rate Name -->
                <div class="mt-6">
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Rate Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name', $shippingRate->name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="e.g., Standard Shipping - T-Shirts (USA)"
                           required>
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Mô tả về mức giá shipping này...">{{ old('description', $shippingRate->description) }}</textarea>
                </div>
            </div>

            <!-- Pricing Section -->
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Pricing</h3>
                <p class="text-sm text-gray-600 mb-4">
                    💡 <strong>First Item Cost</strong> đã bao gồm TẤT CẢ phí (shipping + label + fees)
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- First Item Cost -->
                    <div>
                        <label for="first_item_cost" class="block text-sm font-semibold text-gray-700 mb-2">
                            First Item Cost <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-500">$</span>
                            <input type="number" 
                                   name="first_item_cost" 
                                   id="first_item_cost" 
                                   value="{{ old('first_item_cost', $shippingRate->first_item_cost) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('first_item_cost') border-red-500 @enderror"
                                   placeholder="10.00"
                                   required>
                        </div>
                        <p class="mt-1 text-xs text-blue-600">
                            ✨ Bao gồm: Shipping + Label Fee + All Fees
                        </p>
                        @error('first_item_cost')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Additional Item Cost -->
                    <div>
                        <label for="additional_item_cost" class="block text-sm font-semibold text-gray-700 mb-2">
                            Additional Item Cost <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-500">$</span>
                            <input type="number" 
                                   name="additional_item_cost" 
                                   id="additional_item_cost" 
                                   value="{{ old('additional_item_cost', $shippingRate->additional_item_cost) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('additional_item_cost') border-red-500 @enderror"
                                   placeholder="3.00"
                                   required>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Phí ship cho mỗi item tiếp theo
                        </p>
                        @error('additional_item_cost')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Constraints Section (Optional) -->
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Constraints (Optional)</h3>
                <p class="text-sm text-gray-600 mb-4">Để trống nếu không có giới hạn</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Min Items -->
                    <div>
                        <label for="min_items" class="block text-sm font-semibold text-gray-700 mb-2">
                            Minimum Items
                        </label>
                        <input type="number" 
                               name="min_items" 
                               id="min_items" 
                               value="{{ old('min_items', $shippingRate->min_items) }}"
                               min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="1">
                        <p class="mt-1 text-xs text-gray-500">Số item tối thiểu để áp dụng rate này</p>
                    </div>

                    <!-- Max Items -->
                    <div>
                        <label for="max_items" class="block text-sm font-semibold text-gray-700 mb-2">
                            Maximum Items
                        </label>
                        <input type="number" 
                               name="max_items" 
                               id="max_items" 
                               value="{{ old('max_items', $shippingRate->max_items) }}"
                               min="1"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="100">
                        <p class="mt-1 text-xs text-gray-500">Số item tối đa để áp dụng rate này</p>
                    </div>

                    <!-- Min Order Value -->
                    <div>
                        <label for="min_order_value" class="block text-sm font-semibold text-gray-700 mb-2">
                            Minimum Order Value
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-500">$</span>
                            <input type="number" 
                                   name="min_order_value" 
                                   id="min_order_value" 
                                   value="{{ old('min_order_value', $shippingRate->min_order_value) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Giá trị đơn hàng tối thiểu</p>
                    </div>

                    <!-- Max Order Value -->
                    <div>
                        <label for="max_order_value" class="block text-sm font-semibold text-gray-700 mb-2">
                            Maximum Order Value
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-500">$</span>
                            <input type="number" 
                                   name="max_order_value" 
                                   id="max_order_value" 
                                   value="{{ old('max_order_value', $shippingRate->max_order_value) }}"
                                   step="0.01"
                                   min="0"
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="1000.00">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Giá trị đơn hàng tối đa</p>
                    </div>

                    <!-- Max Weight -->
                    <div>
                        <label for="max_weight" class="block text-sm font-semibold text-gray-700 mb-2">
                            Maximum Weight (kg)
                        </label>
                        <input type="number" 
                               name="max_weight" 
                               id="max_weight" 
                               value="{{ old('max_weight', $shippingRate->max_weight) }}"
                               step="0.01"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="10.00">
                        <p class="mt-1 text-xs text-gray-500">Trọng lượng tối đa (kg)</p>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-2">
                            Sort Order
                        </label>
                        <input type="number" 
                               name="sort_order" 
                               id="sort_order" 
                               value="{{ old('sort_order', $shippingRate->sort_order) }}"
                               min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="0">
                        <p class="mt-1 text-xs text-gray-500">Số nhỏ hơn = ưu tiên cao hơn</p>
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center pt-8">
                        <input type="checkbox" 
                               name="is_active" 
                               id="is_active" 
                               value="1"
                               {{ old('is_active', $shippingRate->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                            Active (rate có thể sử dụng)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Pricing Example -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-blue-900 mb-2">💡 Ví dụ Tính toán</h4>
                <div class="text-xs text-blue-800 space-y-1">
                    <p><strong>Với giá hiện tại (First = ${{ number_format($shippingRate->first_item_cost, 2) }}, Additional = ${{ number_format($shippingRate->additional_item_cost, 2) }}):</strong></p>
                    <p>• Đơn 1 item: ${{ number_format($shippingRate->first_item_cost, 2) }}</p>
                    <p>• Đơn 3 items: ${{ number_format($shippingRate->first_item_cost + ($shippingRate->additional_item_cost * 2), 2) }}</p>
                    <p>• Item đắt nhất luôn được tính là "first item"</p>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('admin.shipping-rates.index') }}" 
               class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                Update Rate
            </button>
        </div>
    </form>
</div>
@endsection

