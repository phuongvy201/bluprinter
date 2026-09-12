@props(['post'])

@php
    $publishedAt = $post->published_at ?? $post->created_at;
@endphp

<article class="blog-card">
    <a href="{{ route('blog.show', $post->slug) }}" class="blog-card__link">
        <div class="blog-card__media">
            @if ($post->featured_image_url)
                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="lazy">
            @else
                <div class="blog-card__placeholder" aria-hidden="true">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
            @endif

            @if ($post->category)
                <span class="blog-card__category">{{ $post->category->name }}</span>
            @endif
        </div>

        <div class="blog-card__body">
            <p class="blog-card__meta">
                <time datetime="{{ $publishedAt->toDateString() }}">{{ $publishedAt->format('M d, Y') }}</time>
                @if ($post->reading_time)
                    <span class="blog-card__meta-sep" aria-hidden="true">&middot;</span>
                    <span>{{ $post->reading_time }} min</span>
                @endif
            </p>
            <h3 class="blog-card__title">{{ $post->title }}</h3>
        </div>
    </a>
</article>
