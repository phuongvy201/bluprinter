{{--
  Product detail — related products carousel (home-style section heading + horizontal track)
  @param string $sectionId
  @param string $eyebrow
  @param string $titleHtml  Raw HTML, may include <span class="gradient-text">
  @param string $subtitle
  @param \Illuminate\Support\Collection $products
  @param string|null $linkUrl
  @param string $linkLabel
  @param string $background  Section background hex
--}}
@php
    $sectionId = $sectionId ?? 'product-show-related';
    $headingId = $headingId ?? ($sectionId . '-heading');
    $products = $products ?? collect();
    $linkLabel = $linkLabel ?? 'See more';
    $background = $background ?? '#ffffff';

    if ($products->isEmpty()) {
        return;
    }
@endphp

<section
    class="product-show-related scroll-reveal"
    id="{{ $sectionId }}"
    style="background: {{ $background }};"
    aria-labelledby="{{ $headingId }}"
>
    <div class="product-show-related__head">
        <div class="section-heading section-heading--catalog section-heading--compact">
            @if(!empty($eyebrow))
                <p class="section-heading__eyebrow">{{ $eyebrow }}</p>
            @endif
            <h2 id="{{ $headingId }}" class="section-heading__title">{!! $titleHtml !!}</h2>
            @if(!empty($subtitle))
                <p class="section-heading__sub">{{ $subtitle }}</p>
            @endif
            <span class="section-heading__accent" aria-hidden="true"></span>
        </div>

        @if(!empty($linkUrl))
            <a href="{{ $linkUrl }}" class="product-show-related__link">
                {{ $linkLabel }}
                <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @endif
    </div>

    <div class="recently-viewed product-show-related__carousel">
        <div class="recently-viewed__slider" data-related-carousel="{{ $sectionId }}">
            <button
                type="button"
                class="recently-viewed__nav recently-viewed__nav--prev"
                data-related-prev="{{ $sectionId }}"
                aria-label="Previous products"
                disabled
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <div class="recently-viewed__track-wrap mobile-scroll-hide" data-related-wrap="{{ $sectionId }}">
                <div class="recently-viewed__track" data-related-track="{{ $sectionId }}">
                    @foreach($products as $relatedProduct)
                        <x-product-card
                            :product="$relatedProduct"
                            :show-wishlist="false"
                            :show-cart="false"
                            :show-try-on="false"
                        />
                    @endforeach
                </div>
            </div>

            <button
                type="button"
                class="recently-viewed__nav recently-viewed__nav--next"
                data-related-next="{{ $sectionId }}"
                aria-label="Next products"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
</section>

<script>
(function () {
    var sectionId = @json($sectionId);
    var wrap = document.querySelector('[data-related-wrap="' + sectionId + '"]');
    var track = document.querySelector('[data-related-track="' + sectionId + '"]');
    var prevBtn = document.querySelector('[data-related-prev="' + sectionId + '"]');
    var nextBtn = document.querySelector('[data-related-next="' + sectionId + '"]');
    if (!wrap || !track) return;

    function getPageWidth() {
        return wrap.clientWidth;
    }

    function updateNav() {
        if (!prevBtn || !nextBtn) return;
        var hasOverflow = wrap.scrollWidth > wrap.clientWidth + 2;
        prevBtn.disabled = wrap.scrollLeft <= 1;
        nextBtn.disabled = wrap.scrollLeft >= wrap.scrollWidth - wrap.clientWidth - 2;
        prevBtn.classList.toggle('recently-viewed__nav--visible', hasOverflow);
        nextBtn.classList.toggle('recently-viewed__nav--visible', hasOverflow);
    }

    function scrollBy(direction) {
        var pageWidth = getPageWidth();
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
