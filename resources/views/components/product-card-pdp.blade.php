@props([
    'product',
    'size' => 'default',
    'showQuickAdd' => true,
    'isCurrent' => false,
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

    $averageRating = $product->getAverageRating();
    $totalReviews = $product->getTotalReviews();
    $hasVariants = (int) ($product->variants_count ?? 0) > 0;

    $sizeClass = match ($size) {
        'large' => 'product-card-pdp--large',
        'mini' => 'product-card-pdp--mini',
        'bundle' => 'product-card-pdp--bundle',
        default => '',
    };

    $productUrl = route('products.show', $product->slug);
    $cardClass = trim('product-card-pdp ' . $sizeClass . ($isCurrent ? ' product-card-pdp--current' : ''));
@endphp

<article class="{{ $cardClass }}" data-product-id="{{ $product->id }}">
    <a href="{{ $productUrl }}" class="product-card-pdp__media" aria-label="{{ $product->name }}">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
        @else
            <span class="product-card-pdp__placeholder" aria-hidden="true">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
        @endif
        @if ($hasDiscount && $discountPct >= 4)
            <span class="product-card-pdp__badge">{{ $discountPct }}% OFF</span>
        @endif
        @if ($isCurrent)
            <span class="product-card-pdp__badge product-card-pdp__badge--current">This item</span>
        @endif
    </a>

    <div class="product-card-pdp__body">
        <a href="{{ $productUrl }}" class="product-card-pdp__title">{{ $product->name }}</a>

        @if ($size !== 'mini')
            @if ($totalReviews > 0)
                <p class="product-card-pdp__rating" aria-label="{{ number_format($averageRating, 1) }} out of 5, {{ $totalReviews }} reviews">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg viewBox="0 0 20 20" fill="{{ $i <= round($averageRating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                    <span>({{ $totalReviews }})</span>
                </p>
            @else
                <p class="product-card-pdp__rating product-card-pdp__rating--empty" aria-hidden="true"></p>
            @endif
        @endif

        <div class="product-card-pdp__footer">
            <p class="product-card-pdp__price-row">
                <span class="product-card-pdp__price">{{ format_price_usd($salePrice) }}</span>
                @if ($hasDiscount)
                    <span class="product-card-pdp__price-was">{{ format_price_usd($compareAt) }}</span>
                @endif
            </p>

            @if ($showQuickAdd && $size !== 'bundle')
                @if ($hasVariants)
                    <a href="{{ $productUrl }}" class="product-card-pdp__cta product-card-pdp__cta--link">View options</a>
                @else
                    <button
                        type="button"
                        class="product-card-pdp__cta"
                        data-pdp-quick-add
                        data-product-id="{{ $product->id }}"
                        data-product-name="{{ $product->name }}"
                        data-product-price="{{ $salePrice }}"
                        data-product-slug="{{ $product->slug }}"
                        data-product-image="{{ $imageUrl ?? '' }}"
                    >Quick Add</button>
                @endif
            @endif
        </div>
    </div>
</article>
