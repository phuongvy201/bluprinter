@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Product</h1>
                <span class="inline-flex items-center px-4 py-2 rounded-lg bg-gradient-to-r from-green-500 to-teal-600 text-white font-bold shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                    </svg>
                    ID: {{ $product->id }}
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-600">Update product information</p>
        </div>
        <a href="{{ route('admin.products.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Products
        </a>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Template Info (Read-only) -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-600">Based on Template:</p>
                    <p class="text-lg font-bold text-gray-900">{{ $product->template->name }} <span class="text-sm text-gray-600">(ID: #{{ $product->template->id }})</span></p>
                </div>
            </div>
        </div>

        <!-- Basic Information -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Product Information</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Product Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $product->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                           placeholder="Enter product name"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price & Quantity -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                            Price (Template: ${{ number_format($product->template->base_price, 2) }})
                        </label>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               value="{{ old('price', $product->price) }}"
                               step="0.01" 
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('price') border-red-500 @enderror"
                               placeholder="{{ $product->template->base_price }}">
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Total Quantity *</label>
                        <input type="number" 
                               id="quantity" 
                               name="quantity" 
                               value="{{ old('quantity', $product->quantity) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('quantity') border-red-500 @enderror"
                               required>
                        @error('quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select id="status" 
                            name="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-500 @enderror"
                            required>
                        <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="draft" {{ old('status', $product->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <input type="hidden" id="description" name="description" value="{{ old('description', $product->description) }}">
                    <div id="description-editor" 
                         contenteditable="true"
                         class="w-full min-h-[100px] px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white"
                         style="white-space: pre-wrap;"
                         oninput="updateDescriptionValue()">{{ old('description', $product->description) }}</div>
                </div>
            </div>
        </div>

        <!-- Media Upload -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Product Media</h3>
                <p class="text-sm text-gray-600">Upload new media or keep existing</p>
            </div>
            <div class="p-6">
                @if($product->media && count($product->media) > 0)
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-4">Current Media:</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($product->media as $media)
                            <div class="relative bg-white rounded-lg border-2 border-gray-200 p-2">
                                @if(str_contains($media, '.mp4') || str_contains($media, '.mov') || str_contains($media, '.avi'))
                                    <div class="aspect-square rounded-lg bg-purple-100 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                        </svg>
                                    </div>
                                @else
                                    <img src="{{ $media }}" class="w-full aspect-square object-cover rounded-lg">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-400 transition-colors bg-gray-50">
                    <input type="file" id="media" name="media[]" multiple accept="image/*,video/*" class="hidden" onchange="handleMediaFiles(this.files)">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-lg font-semibold text-gray-700 mb-2">Upload New Media</p>
                    <button type="button" onclick="document.getElementById('media').click()" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Choose Files
                    </button>
                </div>
                
                <div id="media-preview" class="mt-6 hidden">
                    <h5 class="text-sm font-semibold text-gray-700 mb-4">New Files Selected:</h5>
                    <div id="media-preview-list" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.products.index') }}" 
               class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Update Product
            </button>
        </div>
    </form>
</div>

<script>
let selectedMediaFiles = [];

function handleMediaFiles(files) {
    selectedMediaFiles = Array.from(files);
    displayMediaPreview();
}

function displayMediaPreview() {
    const previewContainer = document.getElementById('media-preview');
    const previewList = document.getElementById('media-preview-list');
    
    if (selectedMediaFiles.length === 0) {
        previewContainer.classList.add('hidden');
        return;
    }
    
    previewContainer.classList.remove('hidden');
    previewList.innerHTML = '';
    
    selectedMediaFiles.forEach((file, index) => {
        const previewItem = document.createElement('div');
        previewItem.className = 'relative bg-white rounded-lg border-2 border-gray-200 p-2';
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewItem.innerHTML = `
                    <img src="${e.target.result}" class="w-full aspect-square object-cover rounded-lg mb-2">
                    <p class="text-xs text-gray-700 truncate">${file.name}</p>
                    <button type="button" onclick="removeMediaFile(${index})" 
                            class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600">×</button>
                `;
            };
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            previewItem.innerHTML = `
                <div class="aspect-square rounded-lg bg-purple-100 flex items-center justify-center mb-2">
                    <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    </svg>
                </div>
                <p class="text-xs text-gray-700 truncate">${file.name}</p>
                <button type="button" onclick="removeMediaFile(${index})" 
                        class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600">×</button>
            `;
        }
        
        previewList.appendChild(previewItem);
    });
}

function removeMediaFile(index) {
    selectedMediaFiles.splice(index, 1);
    
    const input = document.getElementById('media');
    const dt = new DataTransfer();
    selectedMediaFiles.forEach(file => dt.items.add(file));
    input.files = dt.files;
    
    displayMediaPreview();
}

function updateDescriptionValue() {
    const editor = document.getElementById('description-editor');
    const hiddenInput = document.getElementById('description');
    hiddenInput.value = editor.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    const editor = document.getElementById('description-editor');
    const hiddenInput = document.getElementById('description');
    
    if (hiddenInput.value) {
        editor.innerHTML = hiddenInput.value;
    }
});
</script>
@endsection







