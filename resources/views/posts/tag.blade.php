@extends('layouts.app')

@section('title', 'Tag: ' . $tag->name . ' - Bluprinter')
@section('meta_description', $tag->description ?? 'Articles tagged with #' . $tag->name)

@section('content')
@php
    $breadcrumbs = [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Blog', 'url' => route('blog.index')],
        ['name' => '#' . $tag->name, 'url' => null],
    ];
@endphp

<section class="catalog-page catalog-page--blog catalog-page--blog-tag" aria-labelledby="catalog-heading">
    <div class="catalog-collections-hero scroll-reveal">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="catalog-breadcrumb catalog-breadcrumb--hero" aria-label="Breadcrumb">
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

            <div class="catalog-collections-hero__body">
                <header class="catalog-collections-hero__head">
                    <p class="catalog-collections-hero__eyebrow">Tag</p>
                    <h1 id="catalog-heading" class="catalog-collections-hero__title">
                        <span class="gradient-text">#{{ $tag->name }}</span>
                    </h1>
                    @if ($tag->description)
                        <p class="catalog-collections-hero__sub">{{ $tag->description }}</p>
                    @else
                        <p class="catalog-collections-hero__sub">
                            {{ number_format($posts->total()) }} {{ Str::plural('article', $posts->total()) }} tagged with this topic
                        </p>
                    @endif
                </header>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="catalog-toolbar">
            <div class="catalog-toolbar__desktop">
                <p class="catalog-toolbar__summary">
                    <span class="catalog-toolbar__summary-count">{{ number_format($posts->total()) }}</span>
                    {{ Str::plural('article', $posts->total()) }}
                    @if ($posts->total() > 0 && $posts->hasPages())
                        <span class="catalog-toolbar__summary-dot" aria-hidden="true">·</span>
                        <span class="catalog-toolbar__summary-range">{{ $posts->firstItem() }}–{{ $posts->lastItem() }}</span>
                    @endif
                </p>

                <div class="catalog-toolbar__actions">
                    <a href="{{ route('blog.index') }}" class="catalog-toolbar__clear">All articles</a>
                    <div class="catalog-view-toggle" role="group" aria-label="Article view mode">
                        <button type="button" class="catalog-view-toggle__btn is-active" data-catalog-view="grid" aria-pressed="true" aria-label="Grid view">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </button>
                        <button type="button" class="catalog-view-toggle__btn" data-catalog-view="list" aria-pressed="false" aria-label="List view">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="catalog-toolbar__mobile">
                <div class="catalog-toolbar__mobile-head">
                    <p class="catalog-toolbar__summary">
                        <span class="catalog-toolbar__summary-count">{{ number_format($posts->total()) }}</span>
                        {{ Str::plural('article', $posts->total()) }}
                        @if ($posts->total() > 0 && $posts->hasPages())
                            <span class="catalog-toolbar__summary-dot">·</span>
                            <span class="catalog-toolbar__summary-range">{{ $posts->firstItem() }}–{{ $posts->lastItem() }}</span>
                        @endif
                    </p>
                    <div class="catalog-view-toggle" role="group" aria-label="Article view mode">
                        <button type="button" class="catalog-view-toggle__btn is-active" data-catalog-view="grid" aria-pressed="true" aria-label="Grid view">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </button>
                        <button type="button" class="catalog-view-toggle__btn" data-catalog-view="list" aria-pressed="false" aria-label="List view">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                    </div>
                </div>
                <div class="catalog-toolbar__mobile-actions">
                    <a href="{{ route('blog.index') }}" class="catalog-mobile-btn catalog-mobile-btn--ghost">All articles</a>
                </div>
            </div>
        </div>

        @include('posts.partials.blog-posts-grid', [
            'posts' => $posts,
            'gridId' => 'catalog-blog-tag-grid',
            'emptyTitle' => 'No articles with this tag',
            'emptySub' => 'Browse all articles or try another tag.',
            'emptyCtaUrl' => route('blog.index'),
            'emptyCtaLabel' => 'View all articles',
        ])

        @include('partials.recently-viewed-section', ['recentlyViewedId' => 'blog-tag-recently-viewed'])
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var grid = document.getElementById('catalog-blog-tag-grid');
    var viewButtons = document.querySelectorAll('[data-catalog-view]');
    var storageKey = 'bluprinter_catalog_view';

    function setView(mode) {
        if (!grid) return;
        var isList = mode === 'list';
        grid.classList.toggle('catalog-blog-grid--list', isList);
        viewButtons.forEach(function (btn) {
            var active = btn.getAttribute('data-catalog-view') === mode;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        try { localStorage.setItem(storageKey, mode); } catch (e) {}
    }

    if (grid) {
        var savedView = 'grid';
        try { savedView = localStorage.getItem(storageKey) || 'grid'; } catch (e) {}
        setView(savedView === 'list' ? 'list' : 'grid');

        viewButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setView(btn.getAttribute('data-catalog-view'));
            });
        });
    }
});
</script>
@endsection
