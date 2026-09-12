@extends('layouts.app')

@section('title', 'All Products')

@section('content')
@php
    $gtagItems = collect($products->items())->map(function ($product, $loopIndex) use ($products) {
        $categories = $product->categories ?? collect();
        if (!($categories instanceof \Illuminate\Support\Collection)) {
            $categories = collect($categories);
        }

        $primaryCategory = optional($categories->first())->name ?? null;

        return [
            'item_id' => $product->sku ?? $product->id,
            'item_name' => $product->name,
            'item_list_name' => 'All Products',
            'item_category' => $primaryCategory,
            'price' => (float) $product->getEffectivePrice(),
            'index' => ($products->perPage() * max($products->currentPage() - 1, 0)) + $loopIndex + 1,
        ];
    })->values()->toArray();

    $hasFilters = request()->hasAny(['category', 'shop', 'min_price', 'max_price', 'search', 'sort']);
    $activeFilterCount = collect([request('category'), request('shop')])->filter(fn ($v) => filled($v))->count();
    $currentSort = request('sort', 'newest');

    $promo = $catalogPage['promo'] ?? [];
    $seo = $catalogPage['seo'] ?? [];
    $promoPosition = $promo['position'] ?? 'after_toolbar';
    $seoPosition = $seo['position'] ?? 'bottom';
    $hasSeoContent = ! empty($seo['intro']) || ! empty($seo['highlights']) || ! empty($seo['content']);
    $showPromoTop = ($promo['enabled'] ?? false) && $promoPosition === 'after_header' && ($promo['title'] ?? '');
    $showPromoMid = ($promo['enabled'] ?? false) && $promoPosition !== 'after_header' && ($promo['title'] ?? '');
    $showSeoTop = ($seo['enabled'] ?? false) && $seoPosition === 'top' && $hasSeoContent;
    $showSeoBottom = ($seo['enabled'] ?? false) && $seoPosition !== 'top' && $hasSeoContent;
@endphp

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_type: 'product_list',
            content_name: 'All Products'
        });
    }

    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            'event': 'view_item_list',
            'item_list_name': 'All Products',
            'items': @json($gtagItems)
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
                @if ($breadcrumb['url'] && $index < count($breadcrumbs) - 1)
                    <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                @else
                    <span class="catalog-breadcrumb__current">{{ $breadcrumb['name'] }}</span>
                @endif
            @endforeach
        </nav>

        <header class="section-heading section-heading--catalog scroll-reveal">
            <p class="section-heading__eyebrow">Shop</p>
            <h1 id="catalog-heading" class="section-heading__title">
                All <span class="gradient-text">Products</span>
            </h1>
            <p class="section-heading__sub">
                Discover unique and customizable products from our community of creators
            </p>
            <span class="section-heading__accent" aria-hidden="true"></span>
        </header>

        @if ($showPromoTop)
            @include('products.partials.catalog-promo', ['promo' => $promo])
        @endif

        @if ($showSeoTop)
            @include('products.partials.catalog-seo', ['seo' => $seo])
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

                <form method="GET" action="{{ route('products.index') }}" class="catalog-toolbar__filters" id="catalog-filters-form">
                    <select id="catalog-category" name="category" class="catalog-filters__select" onchange="this.form.submit()" aria-label="Category">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select id="catalog-shop" name="shop" class="catalog-filters__select" onchange="this.form.submit()" aria-label="Shop">
                        <option value="">All shops</option>
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                    <select id="catalog-sort" name="sort" class="catalog-filters__select" onchange="this.form.submit()" aria-label="Sort by">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name" {{ $currentSort === 'name' ? 'selected' : '' }}>Name: A–Z</option>
                    </select>
                </form>

                <div class="catalog-toolbar__actions">
                    @if ($hasFilters)
                        <a href="{{ route('products.index') }}" class="catalog-toolbar__clear">Clear</a>
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
                        <a href="{{ route('products.index') }}" class="catalog-mobile-btn catalog-mobile-btn--ghost">Clear</a>
                    @endif
                </div>
            </div>
        </div>

        @if ($showPromoMid)
            @include('products.partials.catalog-promo', ['promo' => $promo])
        @endif

        @if ($products->isEmpty())
            <div class="catalog-empty">
                <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h2 class="catalog-empty__title">No products found</h2>
                <p class="catalog-empty__sub">Try adjusting your filters or browse all products again.</p>
                <a href="{{ route('products.index') }}" class="btn-cta">View all products</a>
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

        @if ($showSeoBottom)
            @include('products.partials.catalog-seo', ['seo' => $seo])
        @endif
    </div>
</section>

{{-- Mobile bottom sheets --}}
<div class="catalog-sheet" id="catalog-sheet-filter" hidden aria-hidden="true">
    <div class="catalog-sheet__backdrop" data-catalog-sheet-close></div>
    <div class="catalog-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="catalog-sheet-filter-title">
        <div class="catalog-sheet__head">
            <h2 id="catalog-sheet-filter-title" class="catalog-sheet__title">Filter products</h2>
            <button type="button" class="catalog-sheet__close" data-catalog-sheet-close aria-label="Close">&times;</button>
        </div>
        <form method="GET" action="{{ route('products.index') }}" class="catalog-sheet__body">
            @if ($currentSort !== 'newest')
                <input type="hidden" name="sort" value="{{ $currentSort }}">
            @endif
            <label class="catalog-sheet__label" for="sheet-category">Category</label>
            <select id="sheet-category" name="category" class="catalog-filters__select catalog-sheet__select">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <label class="catalog-sheet__label" for="sheet-shop">Shop</label>
            <select id="sheet-shop" name="shop" class="catalog-filters__select catalog-sheet__select">
                <option value="">All shops</option>
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}" {{ request('shop') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                @endforeach
            </select>
            <div class="catalog-sheet__foot">
                <a href="{{ route('products.index') }}" class="catalog-sheet__btn catalog-sheet__btn--ghost">Reset</a>
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
        <form method="GET" action="{{ route('products.index') }}" class="catalog-sheet__body">
            @if (request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
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
@endsection
