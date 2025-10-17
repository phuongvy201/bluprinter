@extends('layouts.admin')

@section('title', 'Edit Page')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Edit Page</h1>
        <p class="text-gray-600">Edit: {{ $page->title }}</p>
    </div>

    <form action="{{ route('admin.pages.update', $page) }}" method="POST" enctype="multipart/form-data" class="max-w-4xl">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366]">
                @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Content -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                <textarea name="content" rows="15" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366]">{{ old('content', $page->content) }}</textarea>
                @error('content')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Excerpt -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Excerpt</label>
                <textarea name="excerpt" rows="3"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366]">{{ old('excerpt', $page->excerpt) }}</textarea>
            </div>

            <!-- Featured Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                @if($page->featured_image)
                    <img src="{{ Storage::url($page->featured_image) }}" alt="{{ $page->title }}" class="w-48 h-32 object-cover rounded-lg mb-2">
                @endif
                <input type="file" name="featured_image" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div class="grid grid-cols-2 gap-6">
                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="draft" {{ old('status', $page->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $page->status) == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="scheduled" {{ old('status', $page->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    </select>
                </div>

                <!-- Template -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Template</label>
                    <select name="template" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="default" {{ old('template', $page->template) == 'default' ? 'selected' : '' }}>Default</option>
                        <option value="fullwidth" {{ old('template', $page->template) == 'fullwidth' ? 'selected' : '' }}>Full Width</option>
                        <option value="sidebar" {{ old('template', $page->template) == 'sidebar' ? 'selected' : '' }}>With Sidebar</option>
                    </select>
                </div>

                <!-- Published At -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                    <input type="datetime-local" name="published_at" 
                           value="{{ old('published_at', $page->published_at?->format('Y-m-d\TH:i')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>

                <!-- Parent Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Parent Page</label>
                    <select name="parent_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">None (Top Level)</option>
                        @foreach($parentPages as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id', $page->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Menu Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Menu Title</label>
                    <input type="text" name="menu_title" value="{{ old('menu_title', $page->menu_title) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <!-- Show in Menu -->
            <div class="flex items-center">
                <input type="checkbox" name="show_in_menu" id="show_in_menu" value="1" 
                       {{ old('show_in_menu', $page->show_in_menu) ? 'checked' : '' }}
                       class="w-4 h-4 text-[#005366] rounded">
                <label for="show_in_menu" class="ml-2 text-sm text-gray-700">Show in navigation menu</label>
            </div>

            <!-- SEO -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Settings</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                        <textarea name="meta_description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('meta_description', $page->meta_description) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $page->meta_keywords) }}"
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
                    Update Page
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

