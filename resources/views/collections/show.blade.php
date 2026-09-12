@extends('layouts.app')

@section('title', $collection->meta_title ?? $collection->name)
@section('meta_description', $collection->meta_description ?? $collection->description)

@section('content')
@php
    $collectionUrl = route('collections.show', $collection->slug);
    $hasFilters = filled(request('min_price'))
        || filled(request('max_price'))
        || filled(request('category'))
        || (filled(request('sort')) && ! in_array(request('sort'), ['default', 'featured'], true));
    $activeFilterCount = collect([request('min_price'), request('max_price'), request('category')])->filter(fn ($v) => filled($v))->count();
    $currentSort = request('sort', 'default');
    if ($currentSort === 'price_asc') {
        $currentSort = 'price_low';
    } elseif ($currentSort === 'price_desc') {
        $currentSort = 'price_high';
    } elseif ($currentSort === 'default') {
        $currentSort = 'featured';
    }

    $collectionDescription = trim(strip_tags($collection->description ?? ''));
    $displayableCount = $products->total();
    $collectionSeo = $collectionDescription !== '' ? [
        'eyebrow' => 'About this collection',
        'title' => $collection->name,
        'intro' => $collectionDescription,
    ] : null;

    $gtagItems = collect($products->items())->map(function ($product, $loopIndex) use ($products, $collection) {
        return [
            'item_id' => $product->sku ?? $product->id,
            'item_name' => $product->name,
            'item_list_name' => $collection->name,
            'item_category' => optional($product->template?->category)->name,
            'price' => (float) ($product->price ?? $product->base_price ?? 0),
            'index' => ($products->perPage() * max($products->currentPage() - 1, 0)) + $loopIndex + 1,
        ];
    })->values()->toArray();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: @json($collection->name),
            content_type: 'product_group'
        });
    }

    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            event: 'view_item_list',
            item_list_name: @json($collection->name),
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

        <div class="catalog-collection-show-hero scroll-reveal">
            <div class="catalog-collection-show-hero__content">
                <p class="catalog-collection-show-hero__eyebrow">Collection</p>
                <h1 id="catalog-heading" class="catalog-collection-show-hero__title">
                    <span class="gradient-text">{{ $collection->name }}</span>
                </h1>
                @if ($collectionDescription !== '')
                    @include('partials.catalog-intro', ['text' => $collectionDescription, 'introId' => 'collection-intro'])
                @else
                    <p class="catalog-collection-show-hero__sub">Curated products hand-picked for this collection</p>
                @endif
                <div class="catalog-collection-show-hero__meta">
                    @if ($displayableCount > 0)
                        <span class="catalog-collection-show-hero__chip">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            {{ number_format($displayableCount) }} products
                        </span>
                    @else
                        <span class="catalog-collection-show-hero__chip catalog-collection-show-hero__chip--soon">Coming soon</span>
                    @endif
                    @if ($collection->shop)
                        <a href="{{ route('shops.show', $collection->shop->shop_slug) }}" class="catalog-collection-show-hero__chip catalog-collection-show-hero__chip--link">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            {{ $collection->shop->shop_name }}
                        </a>
                    @endif
                    @if ($collection->featured)
                        <span class="catalog-collection-show-hero__chip catalog-collection-show-hero__chip--featured">Featured collection</span>
                    @endif
                </div>
            </div>
            @if ($collection->image)
                <div class="catalog-collection-show-hero__media">
                    <img src="{{ $collection->image }}" alt="{{ $collection->name }}" loading="lazy">
                </div>
            @endif
        </div>

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

                <form method="GET" action="{{ $collectionUrl }}" class="catalog-toolbar__filters" id="catalog-filters-form">
                    <div class="catalog-toolbar__price-group">
                        <input type="number"
                               name="min_price"
                               value="{{ request('min_price') }}"
                               class="catalog-toolbar__price"
                               placeholder="Min"
                               min="0"
                               step="0.01"
                               aria-label="Minimum price">
                        <span class="catalog-toolbar__price-sep" aria-hidden="true">–</span>
                        <input type="number"
                               name="max_price"
                               value="{{ request('max_price') }}"
                               class="catalog-toolbar__price"
                               placeholder="Max"
                               min="0"
                               step="0.01"
                               aria-label="Maximum price">
                    </div>

                    @if ($filterCategories->isNotEmpty())
                        <select id="catalog-category" name="category" class="catalog-filters__select" onchange="this.form.submit()" aria-label="Category">
                            <option value="">All categories</option>
                            @foreach ($filterCategories as $filterCategory)
                                <option value="{{ $filterCategory->slug }}" {{ request('category') === $filterCategory->slug ? 'selected' : '' }}>{{ $filterCategory->name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <select id="catalog-sort" name="sort" class="catalog-filters__select" onchange="this.form.submit()" aria-label="Sort by">
                        <option value="default" {{ $currentSort === 'featured' ? 'selected' : '' }}>Featured order</option>
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name" {{ $currentSort === 'name' ? 'selected' : '' }}>Name: A–Z</option>
                    </select>
                </form>

                <div class="catalog-toolbar__actions">
                    @if ($hasFilters)
                        <a href="{{ $collectionUrl }}" class="catalog-toolbar__clear">Clear</a>
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
                        <a href="{{ $collectionUrl }}" class="catalog-mobile-btn catalog-mobile-btn--ghost">Clear</a>
                    @endif
                </div>
            </div>
        </div>

        @if ($products->isEmpty())
            <div class="catalog-empty">
                <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                @if ($displayableCount === 0 && ! $hasFilters)
                    <h2 class="catalog-empty__title">Coming soon</h2>
                    <p class="catalog-empty__sub">We are curating products for this collection. Check back soon or explore other collections.</p>
                    <a href="{{ route('collections.index') }}" class="btn-cta">Browse collections</a>
                @else
                    <h2 class="catalog-empty__title">No products found</h2>
                    <p class="catalog-empty__sub">Try adjusting your filters or browse all products in this collection.</p>
                    <a href="{{ $collectionUrl }}" class="btn-cta">View all in collection</a>
                @endif
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

        @if ($collectionSeo)
            @include('products.partials.catalog-seo', ['seo' => $collectionSeo])
        @endif

        @include('partials.recently-viewed-section', ['recentlyViewedId' => 'collection-recently-viewed'])
    </div>
</section>

<div class="catalog-sheet" id="catalog-sheet-filter" hidden aria-hidden="true">
    <div class="catalog-sheet__backdrop" data-catalog-sheet-close></div>
    <div class="catalog-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="catalog-sheet-filter-title">
        <div class="catalog-sheet__head">
            <h2 id="catalog-sheet-filter-title" class="catalog-sheet__title">Filter products</h2>
            <button type="button" class="catalog-sheet__close" data-catalog-sheet-close aria-label="Close">&times;</button>
        </div>
        <form method="GET" action="{{ $collectionUrl }}" class="catalog-sheet__body">
            @if ($currentSort !== 'featured')
                <input type="hidden" name="sort" value="{{ $currentSort === 'featured' ? 'default' : $currentSort }}">
            @endif
            <label class="catalog-sheet__label" for="sheet-min-price">Min price</label>
            <input type="number"
                   id="sheet-min-price"
                   name="min_price"
                   value="{{ request('min_price') }}"
                   class="catalog-filters__select catalog-sheet__select"
                   placeholder="Min"
                   min="0"
                   step="0.01">
            <label class="catalog-sheet__label" for="sheet-max-price">Max price</label>
            <input type="number"
                   id="sheet-max-price"
                   name="max_price"
                   value="{{ request('max_price') }}"
                   class="catalog-filters__select catalog-sheet__select"
                   placeholder="Max"
                   min="0"
                   step="0.01">
            @if ($filterCategories->isNotEmpty())
                <label class="catalog-sheet__label" for="sheet-category">Category</label>
                <select id="sheet-category" name="category" class="catalog-filters__select catalog-sheet__select">
                    <option value="">All categories</option>
                    @foreach ($filterCategories as $filterCategory)
                        <option value="{{ $filterCategory->slug }}" {{ request('category') === $filterCategory->slug ? 'selected' : '' }}>{{ $filterCategory->name }}</option>
                    @endforeach
                </select>
            @endif
            <div class="catalog-sheet__foot">
                <a href="{{ $collectionUrl }}" class="catalog-sheet__btn catalog-sheet__btn--ghost">Reset</a>
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
        <form method="GET" action="{{ $collectionUrl }}" class="catalog-sheet__body">
            @if (request('min_price'))
                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
            @endif
            @if (request('max_price'))
                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
            @endif
            @if (request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @php
                $sortOptions = [
                    'featured' => 'Featured order',
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
                            <input type="radio" name="sort" value="{{ $value === 'featured' ? 'default' : $value }}" {{ $currentSort === $value ? 'checked' : '' }}>
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
