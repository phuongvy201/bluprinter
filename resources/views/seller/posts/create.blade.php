@extends('layouts.admin')

@section('title', 'Create New Post')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create New Blog Post</h1>
        <p class="text-gray-600">Share your story with the community</p>
    </div>

    <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data" class="max-w-5xl">
        @csrf
        
        <div class="grid grid-cols-3 gap-6">
            <!-- Main Content (2/3) -->
            <div class="col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow p-6 space-y-6">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Post Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366]">
                        @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                        <textarea name="content" rows="20" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#005366]">{{ old('content') }}</textarea>
                        @error('content')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Excerpt (Summary)</label>
                        <textarea name="excerpt" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('excerpt') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Short description shown in listings</p>
                    </div>
                </div>

                <!-- SEO Section -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Settings</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                            <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                            <textarea name="meta_description" rows="2"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('meta_description') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keywords</label>
                            <input type="text" name="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="keyword1, keyword2"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar (1/3) -->
            <div class="col-span-1 space-y-6">
                <!-- Publish Settings -->
                <div class="bg-white rounded-lg shadow p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900">Publish</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="draft">Save as Draft</option>
                            <option value="published">Publish Now</option>
                            <option value="scheduled">Schedule</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Published posts require admin approval</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    <button type="submit" class="w-full px-6 py-2 bg-[#005366] text-white rounded-lg hover:bg-[#003d4d]">
                        Create Post
                    </button>
                </div>

                <!-- Category & Tags -->
                <div class="bg-white rounded-lg shadow p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="post_category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">No Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                        <select name="tags[]" multiple class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" size="5">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple</p>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-white rounded-lg shadow p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                    <input type="file" name="featured_image" accept="image/*"
                           class="w-full text-sm">
                </div>

                <!-- Gallery -->
                <div class="bg-white rounded-lg shadow p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gallery Images</label>
                    <input type="file" name="gallery[]" accept="image/*" multiple
                           class="w-full text-sm">
                    <p class="text-xs text-gray-500 mt-1">Select multiple images</p>
                </div>

                <!-- Post Type -->
                <div class="bg-white rounded-lg shadow p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Post Type</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="article">Article</option>
                        <option value="video">Video</option>
                        <option value="gallery">Gallery</option>
                        <option value="product_review">Product Review</option>
                    </select>
                </div>

                <!-- Options -->
                <div class="bg-white rounded-lg shadow p-6 space-y-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="featured" value="1" class="w-4 h-4 text-[#005366] rounded">
                        <span class="ml-2 text-sm text-gray-700">Mark as Featured</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="allow_comments" value="1" checked class="w-4 h-4 text-[#005366] rounded">
                        <span class="ml-2 text-sm text-gray-700">Allow Comments</span>
                    </label>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

