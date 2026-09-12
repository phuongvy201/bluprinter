@extends('layouts.app')

@section('title', 'Blog - Bluprinter')
@section('meta_description', 'Stories, tips, and insights from our print-on-demand community')

@section('content')
@php
    $hero = config('catalog.blog_index.hero', []);
    $currentSort = request('sort', 'latest');
    $currentCategory = request('category');
    $hasFilters = filled(request('search'))
        || filled($currentCategory)
        || filled(request('tag'))
        || ($currentSort !== 'latest');
    $activeFilterCount = collect([request('search'), $currentCategory, request('tag')])->filter(fn ($v) => filled($v))->count();
    $showCategoryTabs = $categories->isNotEmpty();

    $breadcrumbs = [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Blog', 'url' => null],
    ];

    $tabQuery = collect(request()->only(['search', 'sort', 'tag']))->filter(fn ($v) => filled($v));
@endphp

<section class="catalog-page catalog-page--blog" aria-labelledby="catalog-heading">
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
                    @if ($hero['eyebrow'] ?? null)
                        <p class="catalog-collections-hero__eyebrow">{{ $hero['eyebrow'] }}</p>
                    @endif
                    <h1 id="catalog-heading" class="catalog-collections-hero__title">
                        {{ $hero['title'] ?? 'Our' }}
                        <span class="gradient-text">{{ $hero['title_accent'] ?? 'Blog' }}</span>
                    </h1>
                    @if ($hero['subtitle'] ?? null)
                        <p class="catalog-collections-hero__sub">{{ $hero['subtitle'] }}</p>
                    @endif
                </header>

                <form method="GET"
                      action="{{ route('blog.index') }}"
                      class="catalog-collections-hero__search"
                      id="catalog-blog-search-form">
                    @if (filled($currentCategory))
                        <input type="hidden" name="category" value="{{ $currentCategory }}">
                    @endif
                    @if (filled(request('tag')))
                        <input type="hidden" name="tag" value="{{ request('tag') }}">
                    @endif
                    <label class="catalog-collections-hero__search-field">
                        <span class="sr-only">Search articles</span>
                        <svg class="catalog-collections-hero__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search"
                               name="search"
                               value="{{ request('search') }}"
                               class="catalog-collections-hero__search-input"
                               placeholder="Search articles…"
                               aria-label="Search articles">
                    </label>
                    <select id="catalog-sort" name="sort" class="catalog-collections-hero__sort" aria-label="Sort by">
                        <option value="latest" {{ $currentSort === 'latest' ? 'selected' : '' }}>Latest</option>
                        <option value="popular" {{ $currentSort === 'popular' ? 'selected' : '' }}>Most viewed</option>
                        <option value="trending" {{ $currentSort === 'trending' ? 'selected' : '' }}>Trending</option>
                        <option value="oldest" {{ $currentSort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                    </select>
                    <button type="submit" class="btn-cta catalog-collections-hero__apply">Apply</button>
                </form>

                @if ($hasFilters)
                    <p class="catalog-collections-hero__clear-wrap">
                        <a href="{{ route('blog.index') }}" class="catalog-collections-hero__clear">Clear filters</a>
                    </p>
                @endif
            </div>
        </div>
    </div>

    @if ($showCategoryTabs)
        <div class="catalog-collections-tabs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="catalog-collections-tabs__track mobile-scroll-hide">
                    @foreach ($categories as $category)
                        @php
                            $categoryParams = $tabQuery->merge(['category' => $category->slug])->all();
                        @endphp
                        <a href="{{ route('blog.index', $categoryParams) }}"
                           class="catalog-collections-tab {{ $currentCategory === $category->slug ? 'is-active' : '' }}">
                            {{ $category->name }}
                            <span class="catalog-collections-tab__count">({{ number_format($category->posts_count) }})</span>
                        </a>
                    @endforeach
                    <a href="{{ route('blog.index', $tabQuery->all()) }}"
                       class="catalog-collections-tab catalog-collections-tab--all {{ blank($currentCategory) ? 'is-active' : '' }}">
                        All
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="catalog-blog-layout">
            <div class="catalog-blog-main">
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
                            @if ($hasFilters)
                                <a href="{{ route('blog.index') }}" class="catalog-toolbar__clear">Clear</a>
                            @endif
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
                            <button type="button" class="catalog-mobile-btn" data-catalog-sheet-open="filter" aria-haspopup="dialog">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 12h12M10 20h4"/></svg>
                                Filter
                                @if ($activeFilterCount > 0)
                                    <span class="catalog-mobile-btn__badge">{{ $activeFilterCount }}</span>
                                @endif
                            </button>
                            <button type="button" class="catalog-mobile-btn" data-catalog-sheet-open="sort" aria-haspopup="dialog">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 12h9M3 20h5M16 6l4 4m0 0l-4 4m4-4H10"/></svg>
                                Sort
                            </button>
                            @if ($hasFilters)
                                <a href="{{ route('blog.index') }}" class="catalog-mobile-btn catalog-mobile-btn--ghost">Clear</a>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($posts->isEmpty())
                    <div class="catalog-empty">
                        <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h2 class="catalog-empty__title">No posts found</h2>
                        <p class="catalog-empty__sub">Try adjusting your search or browse all articles again.</p>
                        <a href="{{ route('blog.index') }}" class="btn-cta">View all posts</a>
                    </div>
                @else
                    <div class="catalog-blog-grid" id="catalog-blog-grid">
                        @foreach ($posts as $post)
                            <x-catalog-blog-post-item :post="$post" class="scroll-reveal" />
                        @endforeach
                    </div>

                    @if ($posts->hasPages())
                        <div class="catalog-pagination">
                            {{ $posts->onEachSide(1)->links('vendor.pagination.catalog') }}
                        </div>
                    @endif
                @endif
            </div>

            <aside class="catalog-blog-sidebar" aria-label="Blog sidebar">
                @if ($featuredPosts->isNotEmpty())
                    <div class="catalog-blog-widget scroll-reveal">
                        <p class="catalog-blog-widget__eyebrow">Featured</p>
                        <h2 class="catalog-blog-widget__title">Top stories</h2>
                        <ul class="catalog-blog-widget__list">
                            @foreach ($featuredPosts as $featured)
                                <li>
                                    <a href="{{ route('blog.show', $featured->slug) }}" class="catalog-blog-widget__link">
                                        <span class="catalog-blog-widget__link-title">{{ $featured->title }}</span>
                                        <span class="catalog-blog-widget__link-meta">
                                            {{ $featured->published_at ? $featured->published_at->format('M d, Y') : 'Draft' }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($categories->isNotEmpty())
                    <div class="catalog-blog-widget scroll-reveal">
                        <p class="catalog-blog-widget__eyebrow">Browse</p>
                        <h2 class="catalog-blog-widget__title">Categories</h2>
                        <ul class="catalog-blog-widget__chips">
                            @foreach ($categories as $category)
                                <li>
                                    <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
                                       class="catalog-blog-widget__chip {{ $currentCategory === $category->slug ? 'is-active' : '' }}">
                                        {{ $category->name }}
                                        <span>({{ number_format($category->posts_count) }})</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($popularTags->isNotEmpty())
                    <div class="catalog-blog-widget scroll-reveal">
                        <p class="catalog-blog-widget__eyebrow">Discover</p>
                        <h2 class="catalog-blog-widget__title">Popular tags</h2>
                        <div class="catalog-blog-widget__tags">
                            @foreach ($popularTags as $tag)
                                <a href="{{ route('blog.tag', $tag->slug) }}" class="catalog-blog-widget__tag">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>

        @include('partials.recently-viewed-section', ['recentlyViewedId' => 'blog-recently-viewed'])
    </div>
</section>

<div class="catalog-sheet" id="catalog-sheet-filter" hidden aria-hidden="true">
    <div class="catalog-sheet__backdrop" data-catalog-sheet-close></div>
    <div class="catalog-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="catalog-sheet-filter-title">
        <div class="catalog-sheet__head">
            <h2 id="catalog-sheet-filter-title" class="catalog-sheet__title">Filter articles</h2>
            <button type="button" class="catalog-sheet__close" data-catalog-sheet-close aria-label="Close">&times;</button>
        </div>
        <form method="GET" action="{{ route('blog.index') }}" class="catalog-sheet__body">
            @if ($currentSort !== 'latest')
                <input type="hidden" name="sort" value="{{ $currentSort }}">
            @endif
            <label class="catalog-sheet__label" for="sheet-search">Search</label>
            <input type="search"
                   id="sheet-search"
                   name="search"
                   value="{{ request('search') }}"
                   class="catalog-filters__select catalog-sheet__select"
                   placeholder="Search articles…">
            @if ($categories->isNotEmpty())
                <label class="catalog-sheet__label" for="sheet-category">Category</label>
                <select id="sheet-category" name="category" class="catalog-filters__select catalog-sheet__select">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" {{ $currentCategory === $category->slug ? 'selected' : '' }}>
                            {{ $category->name }} ({{ number_format($category->posts_count) }})
                        </option>
                    @endforeach
                </select>
            @endif
            <div class="catalog-sheet__foot">
                <a href="{{ route('blog.index') }}" class="catalog-sheet__btn catalog-sheet__btn--ghost">Reset</a>
                <button type="submit" class="catalog-sheet__btn catalog-sheet__btn--primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="catalog-sheet" id="catalog-sheet-sort" hidden aria-hidden="true">
    <div class="catalog-sheet__backdrop" data-catalog-sheet-close></div>
    <div class="catalog-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="catalog-sheet-sort-title">
        <div class="catalog-sheet__head">
            <h2 id="catalog-sheet-sort-title" class="catalog-sheet__title">Sort by</h2>
            <button type="button" class="catalog-sheet__close" data-catalog-sheet-close aria-label="Close">&times;</button>
        </div>
        <form method="GET" action="{{ route('blog.index') }}" class="catalog-sheet__body">
            @if (request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if (filled($currentCategory))
                <input type="hidden" name="category" value="{{ $currentCategory }}">
            @endif
            @if (filled(request('tag')))
                <input type="hidden" name="tag" value="{{ request('tag') }}">
            @endif
            @php
                $sortOptions = [
                    'latest' => 'Latest first',
                    'popular' => 'Most viewed',
                    'trending' => 'Trending',
                    'oldest' => 'Oldest first',
                ];
            @endphp
            <ul class="catalog-sort-options">
                @foreach ($sortOptions as $value => $label)
                    <li>
                        <label class="catalog-sort-option {{ $currentSort === $value ? 'is-selected' : '' }}">
                            <input type="radio" name="sort" value="{{ $value }}" {{ $currentSort === $value ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
            <div class="catalog-sheet__foot">
                <button type="submit" class="catalog-sheet__btn catalog-sheet__btn--primary catalog-sheet__btn--full">Apply sort</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var grid = document.getElementById('catalog-blog-grid');
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

    function openSheet(id) {
        var sheet = document.getElementById('catalog-sheet-' + id);
        if (!sheet) return;
        sheet.hidden = false;
        sheet.setAttribute('aria-hidden', 'false');
        document.body.classList.add('catalog-sheet-open');
    }

    function closeSheets() {
        document.querySelectorAll('.catalog-sheet').forEach(function (sheet) {
            sheet.hidden = true;
            sheet.setAttribute('aria-hidden', 'true');
        });
        document.body.classList.remove('catalog-sheet-open');
    }

    document.querySelectorAll('[data-catalog-sheet-open]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openSheet(btn.getAttribute('data-catalog-sheet-open'));
        });
    });

    document.querySelectorAll('[data-catalog-sheet-close]').forEach(function (el) {
        el.addEventListener('click', closeSheets);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSheets();
    });
});
</script>
@endsection
