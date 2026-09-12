@extends('layouts.app')

@section('title', 'Search Results' . ($query ? ' for "' . $query . '"' : ''))

@section('content')
@php
    $type = $type ?: 'all';
    $tiktokSearchContents = collect($products instanceof \Illuminate\Contracts\Pagination\Paginator ? $products->items() : ($products ?? []))
        ->take(5)
        ->map(function ($product) {
            return [
                'content_id' => (string) ($product->id ?? $product->slug ?? ''),
                'content_type' => 'product',
                'content_name' => $product->name ?? '',
            ];
        })
        ->filter(function ($item) {
            return !empty($item['content_id']) && !empty($item['content_name']);
        })
        ->values();

    $breadcrumbs = [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Search', 'url' => null],
    ];

    $showProducts = ($type === 'all' || $type === 'products') && $products->count() > 0;
    $showCollections = ($type === 'all' || $type === 'collections') && $collections->count() > 0;
    $showShops = ($type === 'all' || $type === 'shops') && $shops->count() > 0;
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        @if($query)
        fbq('track', 'Search', {
            search_string: '{{ addslashes($query) }}',
            content_category: 'product'
        });
        @endif

        fbq('track', 'ViewContent', {
            content_name: 'Search Results',
            content_type: 'search'
        });
    }

    if (typeof window !== 'undefined' && window.ttq) {
        const tiktokSearchPayload = {
            contents: {!! $tiktokSearchContents->isEmpty() ? '[]' : $tiktokSearchContents->toJson(JSON_UNESCAPED_UNICODE) !!},
            value: 0,
            currency: 'USD'
        };

        @if($query)
        tiktokSearchPayload.search_string = {!! json_encode($query) !!};
        @endif

        window.ttq.track('Search', tiktokSearchPayload);
    }
});
</script>

<section class="catalog-page catalog-page--search" aria-labelledby="catalog-heading">
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
                    <p class="catalog-collections-hero__eyebrow">Search</p>
                    <h1 id="catalog-heading" class="catalog-collections-hero__title">
                        @if ($query)
                            Results for <span class="gradient-text">{{ $query }}</span>
                        @else
                            Find <span class="gradient-text">anything</span>
                        @endif
                    </h1>
                    <p class="catalog-collections-hero__sub">
                        @if ($query && $totalResults > 0)
                            {{ number_format($totalResults) }} result{{ $totalResults !== 1 ? 's' : '' }} across products, collections, and shops
                        @else
                            Search products, collections, and shops from our creators
                        @endif
                    </p>
                </header>

                <form method="GET" action="{{ route('search') }}" class="catalog-collections-hero__search">
                    <label class="catalog-collections-hero__search-field">
                        <span class="sr-only">Search products, collections, and shops</span>
                        <svg class="catalog-collections-hero__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="search"
                               name="q"
                               value="{{ $query }}"
                               class="catalog-collections-hero__search-input"
                               placeholder="Search products, collections, shops…"
                               aria-label="Search products, collections, and shops">
                    </label>
                    @if ($type !== 'all')
                        <input type="hidden" name="type" value="{{ $type }}">
                    @endif
                    <button type="submit" class="btn-cta catalog-collections-hero__apply">Search</button>
                </form>
            </div>
        </div>
    </div>

    @if ($query)
        <div class="catalog-collections-tabs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="catalog-collections-tabs__track mobile-scroll-hide">
                    <a href="{{ route('search', ['q' => $query]) }}"
                       class="catalog-collections-tab {{ $type === 'all' ? 'is-active' : '' }}">
                        All
                        <span class="catalog-collections-tab__count">({{ number_format($totalResults) }})</span>
                    </a>
                    <a href="{{ route('search', ['q' => $query, 'type' => 'products']) }}"
                       class="catalog-collections-tab {{ $type === 'products' ? 'is-active' : '' }}">
                        Products
                        <span class="catalog-collections-tab__count">({{ number_format($counts['products'] ?? 0) }})</span>
                    </a>
                    <a href="{{ route('search', ['q' => $query, 'type' => 'collections']) }}"
                       class="catalog-collections-tab {{ $type === 'collections' ? 'is-active' : '' }}">
                        Collections
                        <span class="catalog-collections-tab__count">({{ number_format($counts['collections'] ?? 0) }})</span>
                    </a>
                    <a href="{{ route('search', ['q' => $query, 'type' => 'shops']) }}"
                       class="catalog-collections-tab {{ $type === 'shops' ? 'is-active' : '' }}">
                        Shops
                        <span class="catalog-collections-tab__count">({{ number_format($counts['shops'] ?? 0) }})</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if ($query)
            @if ($totalResults > 0)
                @if ($showProducts)
                    <section class="catalog-search-section" aria-labelledby="search-products-heading">
                        <div class="catalog-search-section__head">
                            <h2 id="search-products-heading" class="catalog-search-section__title">Products</h2>
                            @if ($type === 'all' && ($counts['products'] ?? 0) > $products->count())
                                <a href="{{ route('search', ['q' => $query, 'type' => 'products']) }}" class="catalog-search-section__link">
                                    View all products
                                    <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <div class="catalog-grid" id="catalog-product-grid">
                            @foreach ($products as $product)
                                <x-product-card :product="$product" :show-shop="true" :show-description="true" class="catalog-product-card" />
                            @endforeach
                        </div>
                        @if ($type === 'products' && method_exists($products, 'hasPages') && $products->hasPages())
                            <div class="catalog-pagination">
                                {{ $products->onEachSide(1)->links('vendor.pagination.catalog') }}
                            </div>
                        @endif
                    </section>
                @endif

                @if ($showCollections)
                    <section class="catalog-search-section" aria-labelledby="search-collections-heading">
                        <div class="catalog-search-section__head">
                            <h2 id="search-collections-heading" class="catalog-search-section__title">Collections</h2>
                            @if ($type === 'all' && ($counts['collections'] ?? 0) > $collections->count())
                                <a href="{{ route('search', ['q' => $query, 'type' => 'collections']) }}" class="catalog-search-section__link">
                                    View all collections
                                    <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <ul class="catalog-collections-grid">
                            @foreach ($collections as $collection)
                                @php
                                    $itemCount = $collection->displayable_products_count ?? $collection->active_products_count ?? 0;
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
                        @if ($type === 'collections' && method_exists($collections, 'hasPages') && $collections->hasPages())
                            <div class="catalog-pagination">
                                {{ $collections->onEachSide(1)->links('vendor.pagination.catalog') }}
                            </div>
                        @endif
                    </section>
                @endif

                @if ($showShops)
                    <section class="catalog-search-section" aria-labelledby="search-shops-heading">
                        <div class="catalog-search-section__head">
                            <h2 id="search-shops-heading" class="catalog-search-section__title">Shops</h2>
                            @if ($type === 'all' && ($counts['shops'] ?? 0) > $shops->count())
                                <a href="{{ route('search', ['q' => $query, 'type' => 'shops']) }}" class="catalog-search-section__link">
                                    View all shops
                                    <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        <ul class="catalog-shops-grid">
                            @foreach ($shops as $shop)
                                @php
                                    $shopDesc = trim(strip_tags($shop->shop_description ?? ''));
                                @endphp
                                <li>
                                    <a href="{{ route('shops.show', $shop->shop_slug ?? $shop->id) }}" class="catalog-shop-card">
                                        <div class="catalog-shop-card__logo">
                                            @if ($shop->shop_logo)
                                                <img src="{{ $shop->shop_logo }}" alt="" loading="lazy">
                                            @else
                                                {{ strtoupper(substr($shop->shop_name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div class="catalog-shop-card__body">
                                            <h3 class="catalog-shop-card__title">{{ $shop->shop_name }}</h3>
                                            @if ($shopDesc !== '')
                                                <p class="catalog-shop-card__desc">{{ $shopDesc }}</p>
                                            @endif
                                            <p class="catalog-shop-card__meta">{{ number_format($shop->products_count ?? 0) }} products</p>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        @if ($type === 'shops' && method_exists($shops, 'hasPages') && $shops->hasPages())
                            <div class="catalog-pagination">
                                {{ $shops->onEachSide(1)->links('vendor.pagination.catalog') }}
                            </div>
                        @endif
                    </section>
                @endif
            @else
                <div class="catalog-empty">
                    <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h2 class="catalog-empty__title">No results found</h2>
                    <p class="catalog-empty__sub">We couldn't find anything for “{{ $query }}”. Try a different keyword or browse the catalog.</p>
                    <div class="catalog-empty__actions">
                        <a href="{{ route('products.index') }}" class="btn-cta">Browse products</a>
                        <a href="{{ route('collections.index') }}" class="btn-outline-petrol">View collections</a>
                    </div>
                </div>
            @endif
        @else
            <div class="catalog-empty">
                <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h2 class="catalog-empty__title">Start searching</h2>
                <p class="catalog-empty__sub">Enter a keyword above to find products, collections, or shops.</p>
                <div class="catalog-empty__actions">
                    <a href="{{ route('products.index') }}" class="btn-cta">Browse products</a>
                    <a href="{{ route('collections.index') }}" class="btn-outline-petrol">View collections</a>
                </div>
            </div>
        @endif

        @include('partials.recently-viewed-section', ['recentlyViewedId' => 'search-recently-viewed'])
    </div>
</section>
@endsection
