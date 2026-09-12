@extends('layouts.app')

@section('title', $shop->shop_name . ' - Shop')
@section('meta_description', $shop->shop_description ?: 'Shop ' . $shop->shop_name . ' on Bluprinter')

@section('content')
@php
    use Illuminate\Support\Str;

    $shopUrl = route('shops.show', $shop->shop_slug ?? $shop->id);
    $currentSort = $currentSort ?? 'newest';
    $categorySlug = $categorySlug ?? request('category');
    $hasFilters = filled($categorySlug) || $currentSort !== 'newest';
    $shopDescription = trim(strip_tags($shop->shop_description ?? ''));
    $activeCategory = $categories->firstWhere('slug', $categorySlug);
    $productCount = (int) ($stats['total_products'] ?? 0);
    $followerCount = (int) ($stats['followers'] ?? 0);
    $hasRating = ($shop->total_ratings ?? 0) > 0 && ($shop->rating ?? 0) > 0;
    $isNewShop = $shop->created_at && $shop->created_at->greaterThan(now()->subDays(60));
    $joinedLabel = $shop->created_at ? 'Joined ' . $shop->created_at->format('M Y') : null;
    $shopTagline = $shopDescription !== ''
        ? Str::limit($shopDescription, 140)
        : 'Discover unique designs curated by ' . $shop->shop_name . '.';
    $showExpandableIntro = $shopDescription !== '' && strlen($shopDescription) > 140;
    $categoryCount = $categories->count();

    $breadcrumbs = [
        ['name' => 'Home', 'url' => route('home')],
        ['name' => $shop->shop_name, 'url' => null],
    ];
@endphp
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof fbq !== 'undefined') {
        fbq('track', 'ViewContent', {
            content_name: @json($shop->shop_name),
            content_type: 'product_group'
        });
    }
});
</script>

<section class="catalog-page catalog-page--shop" aria-labelledby="catalog-heading">
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

        <div class="catalog-shop-profile scroll-reveal" id="shop-profile">
            <div class="catalog-shop-profile__grid">
                <aside class="catalog-shop-showcase" aria-label="{{ $shop->shop_name }} showcase">
                    <div class="catalog-shop-showcase__frame">
                        @if ($shop->shop_banner)
                            <img class="catalog-shop-showcase__photo"
                                 src="{{ $shop->shop_banner }}"
                                 alt=""
                                 loading="eager">
                        @else
                            <div class="catalog-shop-showcase__fallback" aria-hidden="true"></div>
                        @endif

                        <div class="catalog-shop-showcase__mesh" aria-hidden="true"></div>
                        <div class="catalog-shop-showcase__grid-pattern" aria-hidden="true"></div>
                        <div class="catalog-shop-showcase__orb catalog-shop-showcase__orb--1" aria-hidden="true"></div>
                        <div class="catalog-shop-showcase__orb catalog-shop-showcase__orb--2" aria-hidden="true"></div>

                        <span class="catalog-shop-showcase__monogram" aria-hidden="true">
                            {{ strtoupper(substr($shop->shop_name, 0, 1)) }}
                        </span>

                        <div class="catalog-shop-showcase__footer">
                            <span class="catalog-shop-showcase__label">Creator shop</span>
                            @if ($shop->verified)
                                <span class="catalog-shop-showcase__pill">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Verified
                                </span>
                            @elseif ($isNewShop)
                                <span class="catalog-shop-showcase__pill catalog-shop-showcase__pill--new">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    New
                                </span>
                            @endif
                        </div>
                    </div>
                </aside>

                <div class="catalog-shop-panel">
                    <div class="catalog-shop-panel__head">
                        <div class="catalog-shop-hero__logo">
                            @if ($shop->shop_logo)
                                <img src="{{ $shop->shop_logo }}" alt="{{ $shop->shop_name }} logo">
                            @else
                                <span aria-hidden="true">{{ strtoupper(substr($shop->shop_name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="catalog-shop-panel__intro min-w-0">
                            <p class="catalog-collection-show-hero__eyebrow">Shop</p>
                            <h1 id="catalog-heading" class="catalog-collection-show-hero__title">
                                <span class="gradient-text">{{ $shop->shop_name }}</span>
                            </h1>
                            <p class="catalog-shop-hero__tagline">{{ $shopTagline }}</p>
                            @if ($showExpandableIntro)
                                @include('partials.catalog-intro', ['text' => $shopDescription, 'introId' => 'shop-intro'])
                            @endif
                        </div>
                    </div>

                    <div class="catalog-shop-panel__meta-scroll">
                        <div class="catalog-collection-show-hero__meta catalog-shop-panel__meta">
                    @if ($productCount >= 3)
                        <span class="catalog-collection-show-hero__chip">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            {{ number_format($productCount) }} products
                        </span>
                    @elseif ($productCount > 0)
                        <span class="catalog-collection-show-hero__chip catalog-shop-hero__chip--updating">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Updating catalog
                        </span>
                    @elseif ($isNewShop)
                        <span class="catalog-collection-show-hero__chip catalog-shop-hero__chip--new">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            New shop
                        </span>
                    @endif

                    @if ($followerCount >= 1)
                        <span class="catalog-collection-show-hero__chip">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span data-followers>{{ number_format($followerCount) }}</span> followers
                        </span>
                    @endif

                    @if ($hasRating)
                        <span class="catalog-collection-show-hero__chip">
                            <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            {{ number_format($shop->getRatingStars(), 1) }}
                            <span class="catalog-shop-hero__chip-muted">({{ number_format($shop->total_ratings) }})</span>
                        </span>
                    @endif

                    @if ($joinedLabel)
                        <span class="catalog-collection-show-hero__chip">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $joinedLabel }}
                        </span>
                    @endif

                    @if ($shop->verified)
                        <span class="catalog-collection-show-hero__chip catalog-shop-hero__chip--verified">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Verified
                        </span>
                    @endif
                        </div>
                    </div>

                <div class="catalog-shop-hero__actions catalog-shop-panel__actions">
                    <a href="#shop-products" class="btn-cta catalog-shop-hero__cta-primary catalog-shop-panel__cta-main">View products</a>
                    <div class="catalog-shop-panel__actions-row">
                        <button type="button"
                                id="followBtn"
                                class="btn-outline-petrol catalog-shop-panel__action-btn"
                                data-following="{{ $isFollowing ? '1' : '0' }}">
                            <span id="followText">{{ $isFollowing ? 'Unfollow' : 'Follow shop' }}</span>
                        </button>
                        <button type="button" id="contactShopBtn" class="btn-outline-petrol catalog-shop-panel__action-btn">Contact shop</button>
                        <button type="button"
                                id="shareShopBtn"
                                class="catalog-shop-hero__share catalog-shop-panel__share-btn"
                                aria-label="Share shop"
                                title="Share shop">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                </div>
            </div>
        </div>

        @if ($categories->isNotEmpty())
            <div class="shop-type-rail scroll-reveal">
                <div class="shop-type-rail__head">
                    <p class="catalog-subcats__eyebrow">Browse</p>
                    <h2 class="catalog-subcats__title">Shop by type</h2>
                    <p class="catalog-subcats__sub">Filter this shop’s products by category</p>
                </div>
                <div class="shop-type-rail__scroll">
                    <ul class="shop-type-rail__grid {{ $categoryCount <= 2 ? 'shop-type-rail__grid--compact' : '' }}">
                    @foreach ($categories as $index => $category)
                        @php
                            $isActiveCat = $categorySlug === $category->slug;
                            $typeAccent = $index % 4;
                        @endphp
                        <li>
                            <a href="{{ route('shops.show', array_filter(['shop' => $shop->shop_slug, 'category' => $category->slug, 'sort' => $currentSort !== 'newest' ? $currentSort : null])) }}"
                               class="shop-type-card shop-type-card--accent-{{ $typeAccent }} {{ $isActiveCat ? 'is-active' : '' }}">
                                <span class="shop-type-card__glow" aria-hidden="true"></span>
                                <span class="shop-type-card__icon">
                                    @if ($category->image)
                                        <img src="{{ $category->image }}" alt="" loading="lazy">
                                    @else
                                        <span class="shop-type-card__initial" aria-hidden="true">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                    @endif
                                </span>
                                <span class="shop-type-card__body">
                                    <span class="shop-type-card__label">{{ $category->name }}</span>
                                    @if ($category->shop_products_count > 0)
                                        <span class="shop-type-card__count">{{ number_format($category->shop_products_count) }} {{ Str::plural('item', $category->shop_products_count) }}</span>
                                    @endif
                                </span>
                                <span class="shop-type-card__arrow" aria-hidden="true">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </a>
                        </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="catalog-toolbar" id="shop-products">
            <div class="catalog-toolbar__desktop">
                <p class="catalog-toolbar__summary">
                    <span class="catalog-toolbar__summary-count">{{ number_format($allProducts->total()) }}</span>
                    products
                    @if ($activeCategory)
                        <span class="catalog-toolbar__summary-dot" aria-hidden="true">·</span>
                        <span>{{ $activeCategory->name }}</span>
                    @endif
                    @if ($allProducts->total() > 0 && $allProducts->hasPages())
                        <span class="catalog-toolbar__summary-dot" aria-hidden="true">·</span>
                        <span class="catalog-toolbar__summary-range">{{ $allProducts->firstItem() }}–{{ $allProducts->lastItem() }}</span>
                    @endif
                </p>

                <form method="GET" action="{{ $shopUrl }}" class="catalog-toolbar__filters" id="catalog-filters-form">
                    @if (filled($categorySlug))
                        <input type="hidden" name="category" value="{{ $categorySlug }}">
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
                        <a href="{{ $shopUrl }}" class="catalog-toolbar__clear">Clear</a>
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
                        <span class="catalog-toolbar__summary-count">{{ number_format($allProducts->total()) }}</span> products
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
                    <button type="button" class="catalog-mobile-btn" data-catalog-sheet-open="sort" aria-haspopup="dialog">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 12h9M3 20h5M16 6l4 4m0 0l-4 4m4-4H10"/></svg>
                        Sort
                    </button>
                    @if ($hasFilters)
                        <a href="{{ $shopUrl }}" class="catalog-mobile-btn catalog-mobile-btn--ghost">Clear</a>
                    @endif
                </div>
            </div>
        </div>

        @if ($allProducts->isEmpty())
            <div class="catalog-empty">
                <svg class="catalog-empty__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                @if ($hasFilters)
                    <h2 class="catalog-empty__title">No products found</h2>
                    <p class="catalog-empty__sub">Try clearing filters or browse all products from this shop.</p>
                    <a href="{{ $shopUrl }}" class="btn-cta">View all products</a>
                @else
                    <h2 class="catalog-empty__title">No products yet</h2>
                    <p class="catalog-empty__sub">This shop has not listed any products. Check back soon or explore other shops.</p>
                    <a href="{{ route('products.index') }}" class="btn-cta">Browse products</a>
                @endif
            </div>
        @else
            <div class="catalog-grid" id="catalog-product-grid">
                @foreach ($allProducts as $product)
                    <x-product-card :product="$product" :show-shop="false" :show-description="true" class="catalog-product-card" />
                @endforeach
            </div>

            @if ($allProducts->hasPages())
                <div class="catalog-pagination">
                    {{ $allProducts->onEachSide(1)->links('vendor.pagination.catalog') }}
                </div>
            @endif
        @endif

        @include('partials.recently-viewed-section', ['recentlyViewedId' => 'shop-recently-viewed'])
    </div>
</section>

<div class="shop-sticky-cta" id="shop-sticky-cta" aria-hidden="true">
    <div class="shop-sticky-cta__inner">
        <a href="#shop-products" class="btn-cta shop-sticky-cta__primary">View products</a>
        <button type="button" class="btn-outline-petrol shop-sticky-cta__secondary" id="stickyContactBtn">Contact shop</button>
    </div>
</div>

<div class="catalog-sheet" id="catalog-sheet-sort" hidden aria-hidden="true">
    <div class="catalog-sheet__backdrop" data-catalog-sheet-close></div>
    <div class="catalog-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="catalog-sheet-sort-title">
        <div class="catalog-sheet__head">
            <h2 id="catalog-sheet-sort-title" class="catalog-sheet__title">Sort by</h2>
            <button type="button" class="catalog-sheet__close" data-catalog-sheet-close aria-label="Close">&times;</button>
        </div>
        <form method="GET" action="{{ $shopUrl }}" class="catalog-sheet__body">
            @if (filled($categorySlug))
                <input type="hidden" name="category" value="{{ $categorySlug }}">
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

<div id="contactModal" class="catalog-modal hidden" role="dialog" aria-modal="true" aria-labelledby="contact-modal-title">
    <div class="catalog-modal__panel">
        <div class="catalog-modal__head">
            <h2 id="contact-modal-title" class="catalog-modal__title">Contact shop</h2>
            <button type="button" class="catalog-modal__close" data-contact-close aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="contactForm">
            <label class="catalog-modal__label" for="subject">Subject</label>
            <input type="text" id="subject" name="subject" required class="catalog-modal__input">

            <label class="catalog-modal__label" for="message">Message</label>
            <textarea id="message" name="message" rows="4" required class="catalog-modal__textarea"></textarea>

            <div class="catalog-modal__actions">
                <button type="button" class="btn-outline-petrol" data-contact-close>Cancel</button>
                <button type="submit" class="btn-cta">Send message</button>
            </div>
        </form>
    </div>
</div>

@include('partials.catalog-intro-script')

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
        if (e.key === 'Escape') {
            closeSheets();
            closeContactModal();
        }
    });

    var followBtn = document.getElementById('followBtn');
    var followText = document.getElementById('followText');
    var followUrl = @json(route('shops.follow', $shop->shop_slug ?? $shop->id));
    var loginUrl = @json(route('login'));
    var isGuest = @json(auth()->guest());

    function setFollowState(following) {
        if (!followBtn || !followText) return;
        followBtn.setAttribute('data-following', following ? '1' : '0');
        followText.textContent = following ? 'Unfollow' : 'Follow shop';
    }

    var shareBtn = document.getElementById('shareShopBtn');
    var shareUrl = @json($shopUrl);
    var shareTitle = @json($shop->shop_name . ' on Bluprinter');

    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            if (navigator.share) {
                navigator.share({ title: shareTitle, url: shareUrl }).catch(function () {});
                return;
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(shareUrl).then(function () {
                    showNotification('Shop link copied to clipboard.', 'success');
                }).catch(function () {
                    showNotification('Could not copy link.', 'error');
                });
                return;
            }
            showNotification(shareUrl, 'info');
        });
    }

    document.querySelectorAll('a[href="#shop-products"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var target = document.getElementById('shop-products');
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    var shopProfile = document.getElementById('shop-profile');
    var stickyCta = document.getElementById('shop-sticky-cta');
    var stickyContactBtn = document.getElementById('stickyContactBtn');

    function setStickyCtaVisible(visible) {
        if (!stickyCta) return;
        stickyCta.classList.toggle('is-visible', visible);
        stickyCta.setAttribute('aria-hidden', visible ? 'false' : 'true');
        document.body.classList.toggle('shop-sticky-cta-open', visible);
    }

    if (shopProfile && stickyCta && 'IntersectionObserver' in window) {
        var stickyObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                setStickyCtaVisible(!entry.isIntersecting && entry.boundingClientRect.top < 0);
            });
        }, { threshold: 0, rootMargin: '-8px 0px 0px 0px' });
        stickyObserver.observe(shopProfile);
    }

    if (followBtn) {
        followBtn.addEventListener('click', function () {
            if (isGuest) {
                window.location.href = loginUrl;
                return;
            }
            var currentlyFollowing = followBtn.getAttribute('data-following') === '1';
            var action = currentlyFollowing ? 'unfollow' : 'follow';
            followBtn.disabled = true;

            fetch(followUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: action })
            })
            .then(function (response) {
                if (response.status === 401) {
                    window.location.href = loginUrl;
                    return null;
                }
                return response.json().then(function (data) {
                    if (!response.ok) throw new Error(data.message || 'Request failed');
                    return data;
                });
            })
            .then(function (data) {
                if (!data) return;
                if (data.success) {
                    setFollowState(action === 'follow');
                    var followersEl = document.querySelector('[data-followers]');
                    if (followersEl && typeof data.followers_count !== 'undefined') {
                        followersEl.textContent = Number(data.followers_count).toLocaleString();
                    }
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message || 'Something went wrong.', 'error');
                }
            })
            .catch(function (error) {
                showNotification(error.message || 'An error occurred. Please try again.', 'error');
            })
            .finally(function () {
                followBtn.disabled = false;
            });
        });
    }

    var contactModal = document.getElementById('contactModal');
    var contactForm = document.getElementById('contactForm');
    var contactOpen = document.getElementById('contactShopBtn');

    function openContactModal() {
        if (!contactModal) return;
        contactModal.classList.remove('hidden');
    }
    function closeContactModal() {
        if (!contactModal) return;
        contactModal.classList.add('hidden');
    }

    if (contactOpen) contactOpen.addEventListener('click', openContactModal);
    if (stickyContactBtn) stickyContactBtn.addEventListener('click', openContactModal);
    document.querySelectorAll('[data-contact-close]').forEach(function (btn) {
        btn.addEventListener('click', closeContactModal);
    });
    if (contactModal) {
        contactModal.addEventListener('click', function (e) {
            if (e.target === contactModal) closeContactModal();
        });
    }

    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = contactForm.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch(@json(route('shops.contact', $shop)), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    subject: document.getElementById('subject').value,
                    message: document.getElementById('message').value
                })
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeContactModal();
                    contactForm.reset();
                } else {
                    showNotification(data.message || 'Could not send message.', 'error');
                }
            })
            .catch(function () {
                showNotification('An error occurred. Please try again.', 'error');
            })
            .finally(function () {
                if (submitBtn) submitBtn.disabled = false;
            });
        });
    }

    function showNotification(message, type) {
        var notification = document.createElement('div');
        var bg = type === 'success' ? '#16a34a' : (type === 'error' ? '#e2150c' : '#2563eb');
        notification.setAttribute('role', 'status');
        notification.style.cssText = 'position:fixed;top:16px;right:16px;z-index:60;padding:12px 16px;border-radius:8px;color:#fff;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.12);background:' + bg + ';';
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(function () { notification.remove(); }, 3000);
    }
});
</script>
@endsection
