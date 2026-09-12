@extends('layouts.app')

@section('title', $category->meta_title ?? $category->name . ' - Bluprinter')
@section('meta_description', $category->meta_description ?? 'Browse ' . $category->name . ' products and designs')

@section('content')
@php
    $categoryUrl = route('category.show', $category->slug);
    $hasFilters = request()->hasAny(['search', 'shop', 'sort']);
    $activeFilterCount = collect([request('shop'), request('search')])->filter(fn ($v) => filled($v))->count();
    $currentSort = request('sort', 'newest');
    if (in_array($currentSort, ['default', 'price_asc'], true)) {
        $currentSort = $currentSort === 'price_asc' ? 'price_low' : 'newest';
    } elseif ($currentSort === 'price_desc') {
        $currentSort = 'price_high';
    }

    $categoryDescription = trim(strip_tags($category->description ?? ''));
    $categorySeo = $categoryDescription !== '' ? [
        'eyebrow' => 'About this category',
        'title' => $category->name,
        'intro' => $categoryDescription,
    ] : null;

    $gtagItems = collect($products->items())->map(function ($product, $loopIndex) use ($products, $category) {
        return [
            'item_id' => $product->sku ?? $product->id,
            'item_name' => $product->name,
            'item_list_name' => $category->name,
            'item_category' => $category->name,
            'price' => (float) ($product->price ?? $product->base_price ?? 0),
            'index' => ($products->perPage() * max($products->currentPage() - 1, 0)) + $loopIndex + 1,
        ];
    })->values()->toArray();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: @json($category->name),
            content_category: @json($category->name),
            content_type: 'product_group'
        });
    }

    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            event: 'view_item_list',
            item_list_name: @json($category->name),
            items: @json($gtagItems)
        });
    }
});
</script>

<section class="catalog-page" aria-labelledby="catalog-heading">
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

        <header class="section-heading section-heading--catalog scroll-reveal">
            <p class="section-heading__eyebrow">Category</p>
            <h1 id="catalog-heading" class="section-heading__title">
                <span class="gradient-text">{{ $category->name }}</span>
            </h1>
            @if ($categoryDescription !== '')
                @include('partials.catalog-intro', ['text' => $categoryDescription, 'introId' => 'category-intro'])
            @else
                <p class="section-heading__sub">Discover amazing {{ strtolower($category->name) }} products and designs from our creators</p>
            @endif
            <span class="section-heading__accent" aria-hidden="true"></span>
        </header>

        @if ($subcategories->isNotEmpty())
            <div class="catalog-subcats scroll-reveal {{ $subcategories->count() <= 4 ? 'catalog-subcats--few' : '' }}">
                <div class="catalog-subcats__head">
                    <p class="catalog-subcats__eyebrow">Browse</p>
                    <h2 class="catalog-subcats__title">Shop by type</h2>
                    <p class="catalog-subcats__sub">Find the right {{ strtolower($category->name) }} style for you</p>
                </div>
                <div class="catalog-subcats__track-wrap">
                    <ul class="catalog-subcats__track">
                        @foreach ($subcategories as $subcategory)
                            <li>
                                <a href="{{ route('category.show', $subcategory->slug) }}" class="catalog-subcat">
                                    <div class="catalog-subcat__media">
                                        @if ($subcategory->image)
                                            <img src="{{ $subcategory->image }}" alt="{{ $subcategory->name }}" loading="lazy">
                                        @else
                                            <div class="catalog-subcat__placeholder" aria-hidden="true">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="catalog-subcat__overlay" aria-hidden="true"></span>
                                    <span class="catalog-subcat__content">
                                        <span class="catalog-subcat__label">{{ $subcategory->name }}</span>
                                        <span class="catalog-subcat__cta">
                                            Shop now
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="catalog-toolbar">
            <div class="catalog-toolbar__desktop">
                <p class="catalog-toolbar__summary">
                    <span class="catalog-toolbar__summary-count">{{ number_format($products->total()) }}</span>
                    products
                    @if ($products->total() > 0 && $products->hasPages())
                        <span class="catalog-toolbar__summary-dot" aria-hidden="true">·</span>
                        <span class="catalog-toolbar__summary-range">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span>
                    @endif
                </p>

                <form method="GET" action="{{ $categoryUrl }}" class="catalog-toolbar__filters" id="catalog-filters-form">
                    <input type="search"
                           name="search"
                           value="{{ request('search') }}"
                           class="catalog-toolbar__search catalog-toolbar__search--desktop"
                           placeholder="Search in category…"
                           aria-label="Search products">

                    @if ($shops->isNotEmpty())
                        <select id="catalog-shop" name="shop" class="catalog-filters__select" onchange="this.form.submit()" aria-label="Shop">
                            <option value="">All shops</option>
                            @foreach ($shops as $shop)
                                <option value="{{ $shop->id }}" {{ request('shop') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <select id="catalog-sort" name="sort" class="catalog-filters__select" onchange="this.form.submit()" aria-label="Sort by">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name" {{ $currentSort === 'name' ? 'selected' : '' }}>Name: A–Z</option>
                    </select>
                </form>

                <div class="catalog-toolbar__actions">
                    @if ($hasFilters)
                        <a href="{{ $categoryUrl }}" class="catalog-toolbar__clear">Clear</a>
                    @endif
                    <div class="catalog-view-toggle" role="group" aria-label="Product view mode">
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
                        <span class="catalog-toolbar__summary-count">{{ number_format($products->total()) }}</span> products
                        @if ($products->total() > 0 && $products->hasPages())
                            <span class="catalog-toolbar__summary-dot">·</span>
                            <span class="catalog-toolbar__summary-range">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span>
                        @endif
                    </p>
                    <div class="catalog-view-toggle" role="group" aria-label="Product view mode">
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
                        <a href="{{ $categoryUrl }}" class="catalog-mobile-btn catalog-mobile-btn--ghost">Clear</a>
                    @endif
                </div>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="catalog-empty">
                <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h2 class="catalog-empty__title">No products found</h2>
                <p class="catalog-empty__sub">Try adjusting your filters or browse all products in this category.</p>
                <a href="{{ $categoryUrl }}" class="btn-cta">View all in {{ $category->name }}</a>
            </div>
        @else
            <div class="catalog-grid" id="catalog-product-grid">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :show-shop="true" :show-description="true" class="catalog-product-card" />
                @endforeach
            </div>

            @if ($products->hasPages())
                <div class="catalog-pagination">
                    {{ $products->onEachSide(1)->links('vendor.pagination.catalog') }}
                </div>
            @endif
        @endif

        @if ($categorySeo)
            @include('products.partials.catalog-seo', ['seo' => $categorySeo])
        @endif

        @include('partials.recently-viewed-section', ['recentlyViewedId' => 'category-recently-viewed'])

        @if ($relatedCategories->isNotEmpty())
            <div class="catalog-related scroll-reveal">
                <div class="catalog-related__head">
                    <p class="catalog-related__eyebrow">Discover more</p>
                    <h2 class="catalog-related__title">Explore other categories</h2>
                    <p class="catalog-related__sub">More print-on-demand collections from our marketplace</p>
                </div>
                <ul class="catalog-related__grid">
                    @foreach ($relatedCategories as $related)
                        <li>
                            <a href="{{ route('category.show', $related->slug) }}" class="catalog-related-card">
                                <div class="catalog-related-card__media">
                                    @if ($related->image)
                                        <img src="{{ $related->image }}" alt="{{ $related->name }}" loading="lazy">
                                    @endif
                                </div>
                                <span class="catalog-related-card__overlay" aria-hidden="true"></span>
                                <span class="catalog-related-card__content">
                                    @if ($related->products_count > 0)
                                        <span class="catalog-related-card__badge">{{ number_format($related->products_count) }} products</span>
                                    @endif
                                    <span class="catalog-related-card__title">{{ $related->name }}</span>
                                    <span class="catalog-related-card__cta">
                                        Browse category
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</section>

<div class="catalog-sheet" id="catalog-sheet-filter" hidden aria-hidden="true">
    <div class="catalog-sheet__backdrop" data-catalog-sheet-close></div>
    <div class="catalog-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="catalog-sheet-filter-title">
        <div class="catalog-sheet__head">
            <h2 id="catalog-sheet-filter-title" class="catalog-sheet__title">Filter products</h2>
            <button type="button" class="catalog-sheet__close" data-catalog-sheet-close aria-label="Close">&times;</button>
        </div>
        <form method="GET" action="{{ $categoryUrl }}" class="catalog-sheet__body">
            @if ($currentSort !== 'newest')
                <input type="hidden" name="sort" value="{{ $currentSort }}">
            @endif
            <label class="catalog-sheet__label" for="sheet-search">Search</label>
            <input type="search"
                   id="sheet-search"
                   name="search"
                   value="{{ request('search') }}"
                   class="catalog-filters__select catalog-sheet__select"
                   placeholder="Search products…">
            @if ($shops->isNotEmpty())
                <label class="catalog-sheet__label" for="sheet-shop">Shop</label>
                <select id="sheet-shop" name="shop" class="catalog-filters__select catalog-sheet__select">
                    <option value="">All shops</option>
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                    @endforeach
                </select>
            @endif
            <div class="catalog-sheet__foot">
                <a href="{{ $categoryUrl }}" class="catalog-sheet__btn catalog-sheet__btn--ghost">Reset</a>
                <button type="submit" class="catalog-sheet__btn catalog-sheet__btn--primary">Apply filters</button>
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
        <form method="GET" action="{{ $categoryUrl }}" class="catalog-sheet__body">
            @if (request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            @if (request('shop'))
                <input type="hidden" name="shop" value="{{ request('shop') }}">
            @endif
            @php
                $sortOptions = [
                    'newest' => 'Newest first',
                    'price_low' => 'Price: Low to High',
                    'price_high' => 'Price: High to Low',
                    'name' => 'Name: A–Z',
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
    var grid = document.getElementById('catalog-product-grid');
    var viewButtons = document.querySelectorAll('[data-catalog-view]');
    var storageKey = 'bluprinter_catalog_view';

    function setView(mode) {
        if (!grid) return;
        var isList = mode === 'list';
        grid.classList.toggle('catalog-grid--list', isList);
        viewButtons.forEach(function (btn) {
            var active = btn.getAttribute('data-catalog-view') === mode;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        try { localStorage.setItem(storageKey, mode); } catch (e) {}
    }

    var savedView = 'grid';
    try { savedView = localStorage.getItem(storageKey) || 'grid'; } catch (e) {}
    setView(savedView === 'list' ? 'list' : 'grid');

    viewButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setView(btn.getAttribute('data-catalog-view'));
        });
    });

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
@include('partials.catalog-intro-script')
@endsection
