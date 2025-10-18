@extends('layouts.admin')

@section('title', 'View Product Template')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $productTemplate->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">Template details and configuration</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.product-templates.edit', $productTemplate) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Template
            </a>
            <a href="{{ route('admin.product-templates.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Templates
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Template Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productTemplate->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Category</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productTemplate->category->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Base Price</label>
                            <p class="mt-1 text-sm text-gray-900 font-semibold">${{ number_format($productTemplate->base_price, 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Products Count</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productTemplate->products->count() }} products</p>
                        </div>
                    </div>
                    @if($productTemplate->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Description</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $productTemplate->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Media Files -->
            @if($productTemplate->media && count($productTemplate->media) > 0)
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Media Files</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($productTemplate->media as $mediaItem)
                            @php
                                // Get media URL safely
                                if (is_string($mediaItem)) {
                                    $mediaUrl = $mediaItem;
                                } elseif (is_array($mediaItem) && !empty($mediaItem)) {
                                    $mediaUrl = $mediaItem['url'] ?? $mediaItem['path'] ?? reset($mediaItem) ?? null;
                                } else {
                                    $mediaUrl = null;
                                }
                            @endphp
                            
                            @if($mediaUrl)
                            <div class="relative group">
                                @if(str_contains($mediaUrl, '.mp4') || str_contains($mediaUrl, '.mov'))
                                    <video class="w-full h-32 object-cover rounded-lg" controls>
                                        <source src="{{ $mediaUrl }}" type="video/mp4">
                                    </video>
                                @else
                                    <img src="{{ $mediaUrl }}" alt="Template media" class="w-full h-32 object-cover rounded-lg">
                                @endif
                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 rounded-lg flex items-center justify-center">
                                    <a href="{{ $mediaUrl }}" target="_blank" class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Template Attributes -->
            @if($productTemplate->attributes && count($productTemplate->attributes) > 0)
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Template Attributes</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($productTemplate->attributes as $attribute)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <label class="block text-sm font-medium text-gray-500">{{ $attribute->attribute_name }}</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $attribute->attribute_value }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Related Products -->
            @if($productTemplate->products && count($productTemplate->products) > 0)
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Related Products</h3>
                    <p class="text-sm text-gray-600">Products created from this template</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($productTemplate->products as $product)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $product->name }}</h4>
                                    <p class="text-sm text-gray-500">SKU: {{ $product->slug }}</p>
                                    <p class="text-sm text-gray-500">Status: 
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($product->status === 'active') bg-green-100 text-green-800
                                            @elseif($product->status === 'inactive') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif">
                                            {{ ucfirst($product->status) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">${{ number_format($product->getEffectivePrice(), 2) }}</p>
                                    <p class="text-sm text-gray-500">Qty: {{ $product->quantity }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('admin.product-templates.edit', $productTemplate) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Template
                    </a>
                    <a href="{{ route('admin.products.create', ['template_id' => $productTemplate->id]) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Product
                    </a>
                    <form method="POST" action="{{ route('admin.product-templates.destroy', $productTemplate) }}" onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Template
                        </button>
                    </form>
                </div>
            </div>

            <!-- Template Stats -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Template Statistics</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Products Created</span>
                        <span class="text-sm font-medium text-gray-900">{{ $productTemplate->products->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Attributes</span>
                        <span class="text-sm font-medium text-gray-900">{{ $productTemplate->attributes->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Media Files</span>
                        <span class="text-sm font-medium text-gray-900">{{ $productTemplate->media ? count($productTemplate->media) : 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Created</span>
                        <span class="text-sm font-medium text-gray-900">{{ $productTemplate->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Last Updated</span>
                        <span class="text-sm font-medium text-gray-900">{{ $productTemplate->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
