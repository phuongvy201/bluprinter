@extends('layouts.admin')

@section('title', 'Edit Shop')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 text-white">
        <h1 class="text-3xl font-bold">⚙️ Edit Shop Information</h1>
        <p class="text-purple-100 mt-2">Update your shop information</p>
    </div>

    <!-- Form -->
    <form action="{{ route('seller.shop.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Shop Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Shop Name <span class="text-red-500">*</span></label>
            <input type="text" name="shop_name" value="{{ old('shop_name', $shop->shop_name) }}" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                   placeholder="e.g: ABC Fashion Store">
            @error('shop_name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Shop Description</label>
            <textarea name="shop_description" rows="5"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                      placeholder="Introduce your shop...">{{ old('shop_description', $shop->shop_description) }}</textarea>
            @error('shop_description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Current Logo & Banner -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Current Logo</label>
                @if($shop->shop_logo)
                    <img src="{{ $shop->shop_logo }}" class="w-32 h-32 rounded-lg object-cover border-2 border-gray-200 mb-2">
                @else
                    <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center mb-2">
                        <span class="text-gray-400">No logo</span>
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Current Banner</label>
                @if($shop->shop_banner)
                    <img src="{{ $shop->shop_banner }}" class="w-full h-32 rounded-lg object-cover border-2 border-gray-200 mb-2">
                @else
                    <div class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center mb-2">
                        <span class="text-gray-400">No banner</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- New Logo & Banner -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Change Logo</label>
                <input type="file" name="shop_logo" accept="image/*"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF (Max 2MB)</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Change Banner</label>
                <input type="file" name="shop_banner" accept="image/*"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                <p class="text-xs text-gray-500 mt-1">PNG, JPG (Max 5MB)</p>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number</label>
                    <input type="text" name="shop_phone" value="{{ old('shop_phone', $shop->shop_phone) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="0123456789">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" name="shop_email" value="{{ old('shop_email', $shop->shop_email) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="shop@example.com">
                </div>
            </div>
        </div>

        <!-- Address -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
            <input type="text" name="shop_address" value="{{ old('shop_address', $shop->shop_address) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                   placeholder="123 ABC Street">
        </div>

        <!-- City -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">City</label>
            <input type="text" name="shop_city" value="{{ old('shop_city', $shop->shop_city) }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                   placeholder="Ho Chi Minh City">
        </div>

        <!-- Social Links -->
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Social Media</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Facebook</label>
                    <input type="url" name="facebook_url" value="{{ old('facebook_url', $shop->facebook_url) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="https://facebook.com/yourshop">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instagram</label>
                    <input type="url" name="instagram_url" value="{{ old('instagram_url', $shop->instagram_url) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="https://instagram.com/yourshop">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Website</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $shop->website_url) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                           placeholder="https://yourshop.com">
                </div>
            </div>
        </div>

        <!-- Policies -->
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Policies</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Return Policy</label>
                    <textarea name="return_policy" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                              placeholder="Describe your return policy...">{{ old('return_policy', $shop->return_policy) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Shipping Policy</label>
                    <textarea name="shipping_policy" rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                              placeholder="Describe your shipping policy...">{{ old('shipping_policy', $shop->shipping_policy) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end space-x-4 pt-6 border-t">
            <a href="{{ route('seller.shop.dashboard') }}" 
               class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-pink-700 shadow-lg transition transform hover:scale-105">
                💾 Update Shop
            </button>
        </div>
    </form>
</div>
@endsection

