@extends('layouts.admin')

@section('title', 'Create New Page')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create New Page</h1>
        <p class="text-gray-600">Create a static page for your website</p>
    </div>

    <form action="{{ route('admin.pages.store') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366]">
                @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Content -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                <textarea name="content" rows="15" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366]">{{ old('content') }}</textarea>
                @error('content')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                <p class="text-sm text-gray-500 mt-1">You can use HTML</p>
            </div>

            <!-- Excerpt -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Excerpt</label>
                <textarea name="excerpt" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366]">{{ old('excerpt') }}</textarea>
                @error('excerpt')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Featured Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                <input type="file" name="featured_image" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                @error('featured_image')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-6">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                </div>

                <!-- Template -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Template</label>
                    <select name="template" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="default">Default</option>
                        <option value="fullwidth">Full Width</option>
                        <option value="sidebar">With Sidebar</option>
                    </select>
                </div>

                <!-- Published At -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Parent Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent Page</label>
                    <select name="parent_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">None (Top Level)</option>
                        @foreach($parentPages as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Menu Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Menu Title</label>
                    <input type="text" name="menu_title" value="{{ old('menu_title') }}" placeholder="Leave empty to use page title"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <!-- Show in Menu -->
            <div class="flex items-center">
                <input type="checkbox" name="show_in_menu" id="show_in_menu" value="1" {{ old('show_in_menu') ? 'checked' : '' }}
                       class="w-4 h-4 text-[#005366] rounded">
                <label for="show_in_menu" class="ml-2 text-sm text-gray-700">Show this page in navigation menu</label>
            </div>

            <!-- SEO Section -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Settings</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                        <textarea name="meta_description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('meta_description') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="keyword1, keyword2, keyword3"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                <a href="{{ route('admin.pages.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d]">
                    Create Page
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

