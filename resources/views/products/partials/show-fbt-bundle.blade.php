@php
    $product = $product ?? null;
    $fbtProducts = ($fbtProducts ?? collect())->take(3);
    $bundleItems = collect([$product])->filter()->merge($fbtProducts);

    if ($fbtProducts->isEmpty() || !$product) {
        return;
    }

    $bundleTotal = $bundleItems->sum(fn ($item) => (float) $item->getEffectivePrice());

    $bundlePayload = $bundleItems->map(function ($item) use ($product) {
        $media = $item->getEffectiveMedia();
        $imageUrl = null;
        if ($media && count($media) > 0) {
            if (is_string($media[0])) {
                $imageUrl = $media[0];
            } elseif (is_array($media[0])) {
                $imageUrl = $media[0]['url'] ?? $media[0]['path'] ?? reset($media[0]) ?? null;
            }
        }

        return [
            'id' => $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'price' => (float) $item->getEffectivePrice(),
            'image' => $imageUrl,
            'hasVariants' => (int) ($item->variants_count ?? 0) > 0,
            'isCurrent' => $item->id === $product->id,
        ];
    })->values();
@endphp

<section
    class="product-show-rec product-show-rec--fbt scroll-reveal"
    id="productShowFbt"
    aria-labelledby="product-show-fbt-heading"
>
    <header class="product-show-rec__head">
        <div class="product-show-rec__heading">
            <p class="product-show-rec__eyebrow">Complete your look</p>
            <h2 id="product-show-fbt-heading" class="product-show-rec__title">
                Frequently Bought <span class="gradient-text">Together</span>
            </h2>
            <p class="product-show-rec__sub">Pair your favorites and create a complete set.</p>
        </div>
        <a href="{{ route('products.index') }}" class="product-show-rec__link">
            View all
            <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </header>

    <div class="fbt-bundle">
        <div class="fbt-bundle__grid">
            @foreach ($bundleItems as $index => $bundleProduct)
                @if ($index > 0)
                    <div class="fbt-bundle__plus" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                @endif
                <div class="fbt-bundle__slot">
                    <x-product-card-pdp
                        :product="$bundleProduct"
                        size="bundle"
                        :show-quick-add="false"
                        :is-current="$bundleProduct->id === $product->id"
                    />
                </div>
            @endforeach
        </div>

        <aside class="fbt-bundle__checkout">
            <p class="fbt-bundle__checkout-label">Bundle price</p>
            <p class="fbt-bundle__checkout-price" id="fbt-bundle-total">{{ format_price_usd($bundleTotal) }}</p>
            <p class="fbt-bundle__checkout-note">{{ $bundleItems->count() }} items · Save time with one click</p>
            <button
                type="button"
                class="fbt-bundle__checkout-btn"
                id="fbt-bundle-add-btn"
                onclick="addFbtBundleToCart()"
            >
                Add bundle to cart
            </button>
        </aside>
    </div>

    <script type="application/json" id="fbt-bundle-data">{!! $bundlePayload->toJson() !!}</script>
</section>
