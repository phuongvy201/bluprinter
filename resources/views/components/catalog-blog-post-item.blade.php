@props([
    'post',
    'view' => 'both',
])

@php
    $publishedAt = $post->published_at ?? $post->created_at;
    $showGrid = in_array($view, ['grid', 'both'], true);
    $showList = in_array($view, ['list', 'both'], true);
@endphp

<div {{ $attributes->merge(['class' => 'catalog-blog-grid__item']) }}>
    @if ($showGrid)
        <div class="catalog-blog-grid__grid">
            <x-blog-card :post="$post" />
        </div>
    @endif

    @if ($showList)
        <div class="catalog-blog-grid__list">
            <article @class(['catalog-blog-post', 'catalog-blog-post--pinned' => $post->sticky])>
                @if ($post->featured_image_url)
                    <a href="{{ route('blog.show', $post->slug) }}" class="catalog-blog-post__media">
                        <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="lazy">
                    </a>
                @endif
                <div class="catalog-blog-post__body">
                    <div class="catalog-blog-post__meta">
                        @if ($post->sticky)
                            <span class="catalog-blog-post__pin">Pinned</span>
                        @endif
                        @if ($post->category)
                            <a href="{{ route('blog.category', $post->category->slug) }}" class="catalog-blog-post__category">
                                {{ $post->category->name }}
                            </a>
                        @endif
                        @if ($publishedAt)
                            <time datetime="{{ $publishedAt->toDateString() }}">{{ $publishedAt->format('M d, Y') }}</time>
                        @endif
                        <span class="catalog-blog-post__meta-sep" aria-hidden="true">·</span>
                        <span>{{ $post->reading_time ?? 1 }} min read</span>
                        @if ($post->views)
                            <span class="catalog-blog-post__meta-sep" aria-hidden="true">·</span>
                            <span>{{ number_format($post->views) }} views</span>
                        @endif
                    </div>
                    <h2 class="catalog-blog-post__title">
                        <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                    </h2>
                    @if ($post->excerpt)
                        <p class="catalog-blog-post__excerpt">{{ $post->excerpt }}</p>
                    @endif
                    <div class="catalog-blog-post__foot">
                        @if ($post->shop)
                            <a href="{{ route('shops.show', $post->shop->shop_slug) }}" class="catalog-blog-post__shop">
                                {{ $post->shop->shop_name }}
                            </a>
                        @endif
                        <a href="{{ route('blog.show', $post->slug) }}" class="catalog-blog-post__cta">
                            Read more
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </article>
        </div>
    @endif
</div>
