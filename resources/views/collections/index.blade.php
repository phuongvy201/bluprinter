@extends('layouts.app')

@section('title', 'Collections - Bluprinter')
@section('meta_description', 'Browse our curated collections of custom products')

@section('content')
@php
    $hasFilters = request()->hasAny(['search', 'sort', 'group'])
        && (request('sort', 'featured') !== 'featured' || filled(request('search')) || filled(request('group')));
    $activeFilterCount = collect([request('search'), request('group')])->filter(fn ($v) => filled($v))->count();
    $currentSort = request('sort', 'featured');
    $showGroupTabs = $collectionGroups->count() > 1;

    $breadcrumbs = [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Collections', 'url' => null],
    ];

    $tabQuery = collect(request()->only(['search', 'sort']))->filter(fn ($v) => filled($v));
@endphp

<section class="catalog-page catalog-page--collections" aria-labelledby="catalog-heading">
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
                        <span class="gradient-text">{{ $hero['title_accent'] ?? 'Collections' }}</span>
                    </h1>
                    @if ($hero['subtitle'] ?? null)
                        <p class="catalog-collections-hero__sub">{{ $hero['subtitle'] }}</p>
                    @endif
                </header>

                <form method="GET"
                      action="{{ route('collections.index') }}"
                      class="catalog-collections-hero__search"
                      id="catalog-collections-search-form">
                    @if (filled(request('group')))
                        <input type="hidden" name="group" value="{{ request('group') }}">
                    @endif
                    <label class="catalog-collections-hero__search-field">
                        <span class="sr-only">Search collections</span>
                        <svg class="catalog-collections-hero__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search"
                               name="search"
                               value="{{ request('search') }}"
                               class="catalog-collections-hero__search-input"
                               placeholder="Search collections…"
                               aria-label="Search collections">
                    </label>
                    <select id="catalog-sort" name="sort" class="catalog-collections-hero__sort" aria-label="Sort by">
                        <option value="featured" {{ $currentSort === 'featured' ? 'selected' : '' }}>Featured</option>
                        <option value="name" {{ $currentSort === 'name' ? 'selected' : '' }}>Name: A–Z</option>
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="oldest" {{ $currentSort === 'oldest' ? 'selected' : '' }}>Oldest</option>
                        <option value="products" {{ $currentSort === 'products' ? 'selected' : '' }}>Most products</option>
                    </select>
                    <button type="submit" class="btn-cta catalog-collections-hero__apply">Apply</button>
                </form>

                @if ($hasFilters)
                    <p class="catalog-collections-hero__clear-wrap">
                        <a href="{{ route('collections.index') }}" class="catalog-collections-hero__clear">Clear filters</a>
                    </p>
                @endif
            </div>
        </div>
    </div>

    @if ($showGroupTabs)
        <div class="catalog-collections-tabs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="catalog-collections-tabs__track mobile-scroll-hide">
                    @foreach ($collectionGroups as $group)
                        @php
                            $groupParams = $tabQuery->merge(['group' => $group['slug']])->all();
                        @endphp
                        <a href="{{ route('collections.index', $groupParams) }}"
                           class="catalog-collections-tab {{ $groupSlug === $group['slug'] ? 'is-active' : '' }}">
                            {{ $group['label'] }}
                            <span class="catalog-collections-tab__count">({{ number_format($group['products_count']) }})</span>
                        </a>
                    @endforeach
                    <a href="{{ route('collections.index', $tabQuery->all()) }}"
                       class="catalog-collections-tab catalog-collections-tab--all {{ blank($groupSlug) ? 'is-active' : '' }}">
                        All
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="catalog-toolbar catalog-toolbar--collections-mobile">
            <div class="catalog-toolbar__mobile">
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
                </div>
            </div>
        </div>

        @if ($collections->isEmpty())
            <div class="catalog-empty">
                <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h2 class="catalog-empty__title">No collections found</h2>
                <p class="catalog-empty__sub">Try adjusting your search or browse all collections again.</p>
                <a href="{{ route('collections.index') }}" class="btn-cta">View all collections</a>
            </div>
        @else
            <ul class="catalog-collections-grid">
                @foreach ($collections as $collection)
                    @php
                        $itemCount = $collection->displayable_products_count;
                        $descText = trim(strip_tags($collection->description ?? ''));
                    @endphp
                    <li>
                        <a href="{{ route('collections.show', $collection->slug) }}" class="catalog-collection-card scroll-reveal">
                            <div class="catalog-collection-card__media">
                                @if ($collection->image)
                                    <img src="{{ $collection->image }}" alt="{{ $collection->name }}" loading="lazy">
                                @endif
                                @if ($collection->featured)
                                    <span class="catalog-collection-card__featured">Featured</span>
                                @endif
                            </div>
                            <span class="catalog-collection-card__overlay" aria-hidden="true"></span>
                            <span class="catalog-collection-card__content">
                                <span class="catalog-collection-card__badge">{{ number_format($itemCount) }} items</span>
                                <span class="catalog-collection-card__title">{{ $collection->name }}</span>
                                @if ($descText !== '')
                                    <span class="catalog-collection-card__desc">{{ $descText }}</span>
                                @endif
                                @if ($collection->shop)
                                    <span class="catalog-collection-card__shop">{{ $collection->shop->shop_name }}</span>
                                @endif
                                <span class="catalog-collection-card__cta">
                                    Shop now
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($collections->hasPages())
                <div class="catalog-pagination">
                    {{ $collections->onEachSide(1)->links('vendor.pagination.catalog') }}
                </div>
            @endif
        @endif
    </div>
</section>

<div class="catalog-sheet" id="catalog-sheet-filter" hidden aria-hidden="true">
    <div class="catalog-sheet__backdrop" data-catalog-sheet-close></div>
    <div class="catalog-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="catalog-sheet-filter-title">
        <div class="catalog-sheet__head">
            <h2 id="catalog-sheet-filter-title" class="catalog-sheet__title">Search collections</h2>
            <button type="button" class="catalog-sheet__close" data-catalog-sheet-close aria-label="Close">&times;</button>
        </div>
        <form method="GET" action="{{ route('collections.index') }}" class="catalog-sheet__body">
            @if ($currentSort !== 'featured')
                <input type="hidden" name="sort" value="{{ $currentSort }}">
            @endif
            @if (filled(request('group')))
                <input type="hidden" name="group" value="{{ request('group') }}">
            @endif
            <label class="catalog-sheet__label" for="sheet-search">Search</label>
            <input type="search"
                   id="sheet-search"
                   name="search"
                   value="{{ request('search') }}"
                   class="catalog-filters__select catalog-sheet__select"
                   placeholder="Search collections…">
            @if ($showGroupTabs)
                <label class="catalog-sheet__label" for="sheet-group">Series</label>
                <select id="sheet-group" name="group" class="catalog-filters__select catalog-sheet__select">
                    <option value="">All series</option>
                    @foreach ($collectionGroups as $group)
                        <option value="{{ $group['slug'] }}" {{ $groupSlug === $group['slug'] ? 'selected' : '' }}>
                            {{ $group['label'] }} ({{ number_format($group['products_count']) }})
                        </option>
                    @endforeach
                </select>
            @endif
            <div class="catalog-sheet__foot">
                <a href="{{ route('collections.index') }}" class="catalog-sheet__btn catalog-sheet__btn--ghost">Reset</a>
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
        <form method="GET" action="{{ route('collections.index') }}" class="catalog-sheet__body">
            @if (request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if (filled(request('group')))
                <input type="hidden" name="group" value="{{ request('group') }}">
            @endif
            @php
                $sortOptions = [
                    'featured' => 'Featured',
                    'name' => 'Name: A–Z',
                    'newest' => 'Newest first',
                    'oldest' => 'Oldest first',
                    'products' => 'Most products',
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
