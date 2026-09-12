@props([
    'product',
    'featured' => false,
    'showWishlist' => true,
    'showCart' => true,
    'showTryOn' => true,
    'showShop' => false,
    'showDescription' => true,
    'showRating' => true,
    'showSold' => true,
    'badge' => null,
    'class' => '',
])

@php
    $media = $product->getEffectiveMedia();
    $imageUrl = null;
    if ($media && count($media) > 0) {
        if (is_string($media[0])) {
            $imageUrl = $media[0];
        } elseif (is_array($media[0])) {
            $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? null;
        }
    }

    $compareAt = (float) $product->getCompareAtPrice();
    $salePrice = (float) $product->getEffectivePrice();
    $hasDiscount = $compareAt > 0 && $salePrice < $compareAt;
    $discountPct = $hasDiscount ? (int) round((($compareAt - $salePrice) / $compareAt) * 100) : 0;

    $description = trim(html_entity_decode(strip_tags($product->getEffectiveDescription() ?? ''), ENT_QUOTES, 'UTF-8'));
    $averageRating = $product->getAverageRating();
    $totalReviews = $product->getTotalReviews();
    $soldCount = $product->getSoldCount();

    $cardClass = trim('product-card' . ($featured ? ' product-card--featured' : '') . ' ' . $class);
    $productUrl = route('products.show', $product->slug);
    $tryOnUrl = route('studio.try-on', ['product' => $product->slug]);
    $tryOnMedia = $product->tryOnImages();
@endphp

<div class="{{ $cardClass }}">
    <div class="product-card__media-shell">
        <a href="{{ $productUrl }}"
           class="product-card__media"
           aria-label="{{ $product->name }}">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <div class="product-card__placeholder" aria-hidden="true">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
        </a>

        @if ($hasDiscount && $discountPct >= 4)
            <span class="product-card__sale-badge">{{ $discountPct }}% OFF</span>
        @endif

        @if ($badge)
            <span class="product-card__media-badge">{{ $badge }}</span>
        @endif

        @if ($showTryOn)
            <button type="button"
               class="product-card__tryon"
               title="AI Virtual Try-On"
               aria-label="AI try-on {{ $product->name }}"
               data-tryon-open
               data-tryon-name="{{ $product->name }}"
               data-tryon-type="{{ $product->getDisplayCategoryName() }}"
               data-tryon-url="{{ $productUrl }}"
               data-tryon-image="{{ $tryOnMedia['image'] }}"
               data-tryon-back="{{ $tryOnMedia['back'] }}">
                <svg class="product-card__tryon-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span>Try-On</span>
            </button>
        @endif
    </div>

    <div class="product-card__body">
        <a href="{{ $productUrl }}"
           class="product-card__title-link"
           aria-label="{{ $product->name }}{{ $hasDiscount ? ', ' . $discountPct . '% off' : '' }}">
            <h3 class="product-card__title">{{ $product->name }}</h3>
        </a>

        @if ($showDescription && $description !== '')
            <p class="product-card__desc">{{ $description }}</p>
        @endif

        @if ($showShop && $product->shop)
            <p class="product-card__shop">{{ $product->shop->name }}</p>
        @endif

        @if (($showRating && $totalReviews > 0) || ($showSold && $soldCount > 0))
            <div class="product-card__meta">
                @if ($showRating && $totalReviews > 0)
                    <span class="product-card__rating" aria-label="{{ number_format($averageRating, 1) }} out of 5 stars, {{ $totalReviews }} reviews">
                        <svg class="product-card__rating-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span>{{ number_format($averageRating, 1) }}</span>
                        <span class="product-card__rating-count">({{ $totalReviews }})</span>
                    </span>
                @endif

                @if ($showSold && $soldCount > 0)
                    <span class="product-card__sold">{{ number_format($soldCount) }} sold</span>
                @endif
            </div>
        @endif
    </div>

    <div class="product-card__footer">
        <a href="{{ $productUrl }}" class="product-card__prices">
            <span class="product-card__price">{{ format_price_usd($salePrice) }}</span>
            @if ($hasDiscount)
                <span class="product-card__price-original">{{ format_price_usd($compareAt) }}</span>
            @endif
        </a>

        <div class="product-card__actions">
            @if ($showWishlist)
                <button type="button"
                        data-wishlist-toggle
                        data-product-id="{{ $product->id }}"
                        data-product-name="{{ $product->name }}"
                        data-product-price="{{ $salePrice }}"
                        data-product-slug="{{ $product->slug }}"
                        class="product-card__action-btn wishlist-btn not-in-wishlist"
                        aria-label="Add {{ $product->name }} to wishlist">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
            @endif

            @if ($showCart)
                <a href="{{ $productUrl }}"
                   class="product-card__action-btn product-card__action-btn--cart"
                   aria-label="View {{ $product->name }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </a>
            @endif
        </div>

        <a href="{{ $productUrl }}" class="product-card__list-cta">View product</a>
    </div>
</div>
