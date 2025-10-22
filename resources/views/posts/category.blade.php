@extends('layouts.app')

@section('title', 'Category: ' . $category->name)

@section('content')
<div class="bg-gradient-to-r from-[#005366] to-[#003d4d] text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-xl text-gray-100">{{ $category->description }}</p>
        @endif
        <p class="text-gray-200 mt-4">{{ $posts->total() }} articles in this category</p>
    </div>
</div>

<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
                <article class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-xl transition-all">
                    <a href="{{ route('blog.show', $post->slug) }}">
                        @if($post->featured_image)
                            <div class="aspect-video overflow-hidden bg-gray-100">
                                <img src="{{ $post->featured_image_url }}" 
                                     alt="{{ $post->title }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                            </div>
                        @endif
                        <div class="p-6">
                            <p class="text-xs text-gray-500 mb-2">{{ $post->published_at ? $post->published_at->format('M d, Y') : 'Draft' }} • {{ $post->reading_time ?? 1 }} min</p>
                            <h2 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 hover:text-[#005366]">{{ $post->title }}</h2>
                            @if($post->excerpt)
                                <p class="text-gray-600 text-sm line-clamp-3">{{ $post->excerpt }}</p>
                            @endif
                        </div>
                    </a>
                </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection

