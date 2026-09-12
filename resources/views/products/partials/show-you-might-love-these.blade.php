@php
    $collectionProducts = ($collectionProducts ?? collect())->take(6);
    $product = $product ?? null;
    $primaryCollection = null;

    if ($product) {
        $primaryCollection = $product->collections()
            ->where('status', 'active')
            ->where('admin_approved', true)
            ->orderByDesc('featured')
            ->first();
    }

    $collectionLink = $primaryCollection
        ? route('collections.show', $primaryCollection->slug)
        : route('collections.index');

    if ($collectionProducts->isEmpty()) {
        return;
    }
@endphp

<section
    class="product-show-rec product-show-rec--ymlt scroll-reveal"
    id="productShowYmlt"
    aria-labelledby="product-show-ymlt-heading"
>
    <header class="product-show-rec__head">
        <div class="product-show-rec__heading">
            <p class="product-show-rec__eyebrow">{{ $primaryCollection ? 'You may also like' : 'Curated for you' }}</p>
            <h2 id="product-show-ymlt-heading" class="product-show-rec__title">
                You Might <span class="gradient-text">Love These</span>
            </h2>
            <p class="product-show-rec__sub">
                {{ $primaryCollection
                    ? 'Hand-picked products that pair perfectly with your selection.'
                    : 'Discover more products related to this item.' }}
            </p>
        </div>
        <a href="{{ $collectionLink }}" class="product-show-rec__link">
            View all
            <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </header>

    <div class="product-show-rec__track-shell product-show-rec__track-shell--large" data-related-wrap="productShowYmlt">
        <button
            type="button"
            class="product-show-rec__nav product-show-rec__nav--prev"
            data-related-prev="productShowYmlt"
            aria-label="Previous products"
            disabled
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <div class="product-show-rec__track-wrap mobile-scroll-hide">
            <div class="product-show-rec__track product-show-rec__track--large" data-related-track="productShowYmlt">
                @foreach ($collectionProducts as $relatedProduct)
                    <x-product-card-pdp :product="$relatedProduct" size="large" />
                @endforeach
            </div>
        </div>

        <button
            type="button"
            class="product-show-rec__nav product-show-rec__nav--next"
            data-related-next="productShowYmlt"
            aria-label="Next products"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</section>

<script>
(function () {
    var sectionId = 'productShowYmlt';
    var wrap = document.querySelector('[data-related-wrap="' + sectionId + '"] .product-show-rec__track-wrap');
    var prevBtn = document.querySelector('[data-related-prev="' + sectionId + '"]');
    var nextBtn = document.querySelector('[data-related-next="' + sectionId + '"]');
    if (!wrap) return;

    function updateNav() {
        if (!prevBtn || !nextBtn) return;
        var hasOverflow = wrap.scrollWidth > wrap.clientWidth + 2;
        prevBtn.disabled = wrap.scrollLeft <= 1;
        nextBtn.disabled = wrap.scrollLeft >= wrap.scrollWidth - wrap.clientWidth - 2;
        prevBtn.classList.toggle('product-show-rec__nav--visible', hasOverflow);
        nextBtn.classList.toggle('product-show-rec__nav--visible', hasOverflow);
    }

    function scrollBy(direction) {
        var pageWidth = wrap.clientWidth;
        if (!pageWidth) return;
        wrap.scrollBy({ left: direction === 'next' ? pageWidth : -pageWidth, behavior: 'smooth' });
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { scrollBy('prev'); });
    if (nextBtn) nextBtn.addEventListener('click', function () { scrollBy('next'); });
    wrap.addEventListener('scroll', updateNav, { passive: true });
    window.addEventListener('resize', updateNav);
    updateNav();
})();
</script>
