@extends('layouts.app')

@section('title', $post->meta_title ?? $post->title)
@section('meta_description', $post->meta_description ?? $post->excerpt)

@section('content')
@php
    $publishedAt = $post->published_at ?? $post->created_at;
    $shareUrl = url(route('blog.show', $post->slug));

    $breadcrumbs = [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Blog', 'url' => route('blog.index')],
    ];
    if ($post->category) {
        $breadcrumbs[] = ['name' => $post->category->name, 'url' => route('blog.category', $post->category->slug)];
    }
    $breadcrumbs[] = ['name' => $post->title, 'url' => null];
@endphp

<section class="catalog-page catalog-page--blog catalog-page--blog-show" aria-labelledby="blog-post-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="catalog-breadcrumb" aria-label="Breadcrumb">
            @foreach ($breadcrumbs as $index => $breadcrumb)
                @if ($index > 0)
                    <span class="catalog-breadcrumb__sep" aria-hidden="true">/</span>
                @endif
                @if ($breadcrumb['url'])
                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                @else
                    <span class="catalog-breadcrumb__current">{{ $breadcrumb['name'] }}</span>
                @endif
            @endforeach
        </nav>

        <div class="catalog-blog-show-hero scroll-reveal {{ $post->featured_image_url ? 'catalog-blog-show-hero--has-media' : '' }}">
            <div class="catalog-blog-show-hero__content">
                @if ($post->category)
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="catalog-blog-show-hero__category">
                        {{ $post->category->name }}
                    </a>
                @endif
                <h1 id="blog-post-heading" class="catalog-blog-show-hero__title">{{ $post->title }}</h1>
                @if ($post->excerpt)
                    <p class="catalog-blog-show-hero__excerpt">{{ $post->excerpt }}</p>
                @endif
                <div class="catalog-blog-show-hero__meta">
                    @if ($publishedAt)
                        <span class="catalog-collection-show-hero__chip">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $publishedAt->format('M d, Y') }}
                        </span>
                    @endif
                    <span class="catalog-collection-show-hero__chip">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $post->reading_time ?? 1 }} min read
                    </span>
                    <span class="catalog-collection-show-hero__chip">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ number_format($post->views ?? 0) }} views
                    </span>
                </div>
            </div>
            @if ($post->featured_image_url)
                <div class="catalog-blog-show-hero__media">
                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" loading="eager">
                </div>
            @endif
        </div>

        <article class="catalog-blog-article scroll-reveal">
            @if ($post->shop)
                <div class="catalog-blog-article__author">
                    <div class="catalog-blog-article__author-avatar">
                        @if ($post->shop->shop_logo)
                            <img src="{{ $post->shop->shop_logo }}" alt="{{ $post->shop->shop_name }}" loading="lazy">
                        @else
                            <span aria-hidden="true">{{ strtoupper(substr($post->shop->shop_name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="catalog-blog-article__author-body">
                        <a href="{{ route('shops.show', $post->shop->shop_slug) }}" class="catalog-blog-article__author-name">
                            {{ $post->shop->shop_name }}
                        </a>
                        @if ($post->user)
                            <p class="catalog-blog-article__author-meta">Posted by {{ $post->user->name }}</p>
                        @endif
                    </div>
                    <a href="{{ route('shops.show', $post->shop->shop_slug) }}" class="btn-outline-petrol catalog-blog-article__author-cta">
                        Visit shop
                    </a>
                </div>
            @endif

            <div class="catalog-blog-article__content">
                {!! $post->content !!}
            </div>

            @if ($post->gallery && count($post->gallery) > 0)
                <div class="catalog-blog-article__gallery">
                    <h2 class="catalog-blog-article__gallery-title">Gallery</h2>
                    <div class="catalog-blog-article__gallery-grid">
                        @foreach ($post->gallery_urls as $imageUrl)
                            <figure class="catalog-blog-article__gallery-item">
                                <img src="{{ $imageUrl }}" alt="Gallery image" loading="lazy">
                            </figure>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($post->tags->isNotEmpty())
                <div class="catalog-blog-article__tags">
                    @foreach ($post->tags as $tag)
                        <a href="{{ route('blog.tag', $tag->slug) }}" class="catalog-blog-widget__tag">#{{ $tag->name }}</a>
                    @endforeach
                </div>
            @endif

            <footer class="catalog-blog-article__footer">
                <div class="catalog-blog-article__stats">
                    <span class="catalog-blog-article__stat">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        {{ number_format($post->likes) }}
                    </span>
                    <span class="catalog-blog-article__stat">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        {{ number_format($post->comments_count) }}
                    </span>
                </div>
                <div class="catalog-blog-article__share">
                    <span class="catalog-blog-article__share-label">Share</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
                       class="catalog-blog-article__share-btn"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="Share on Facebook">
                        <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($post->title) }}"
                       class="catalog-blog-article__share-btn"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="Share on X">
                        <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                </div>
            </footer>
        </article>

        @if ($relatedPosts->isNotEmpty())
            <section class="catalog-blog-related scroll-reveal" aria-labelledby="blog-related-heading">
                <div class="section-heading section-heading--catalog catalog-blog-related__head">
                    <p class="section-heading__eyebrow">Keep reading</p>
                    <h2 id="blog-related-heading" class="section-heading__title">
                        Related <span class="gradient-text">Articles</span>
                    </h2>
                    <span class="section-heading__accent" aria-hidden="true"></span>
                </div>
                <ul class="catalog-blog-related__grid">
                    @foreach ($relatedPosts as $related)
                        <li>
                            <x-blog-card :post="$related" />
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="catalog-blog-show-back">
            <a href="{{ route('blog.index') }}" class="btn-outline-petrol">Back to all articles</a>
        </div>

        @include('partials.recently-viewed-section', ['recentlyViewedId' => 'blog-show-recently-viewed'])
    </div>
</section>
@endsection
