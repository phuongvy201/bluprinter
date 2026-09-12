@php $recentId = 'product-show-recently-viewed'; @endphp

<section
    class="product-show-rec product-show-rec--recent scroll-reveal hidden"
    id="{{ $recentId }}-section"
    aria-labelledby="{{ $recentId }}-heading"
>
    <header class="product-show-rec__head product-show-rec__head--compact">
        <div class="product-show-rec__heading">
            <p class="product-show-rec__eyebrow">Continue browsing</p>
            <h2 id="{{ $recentId }}-heading" class="product-show-rec__title product-show-rec__title--sm">
                Recently <span class="gradient-text">Viewed</span>
            </h2>
        </div>
    </header>

    <div class="product-show-rec__track-shell product-show-rec__track-shell--mini hidden" id="{{ $recentId }}-wrapper">
        <button
            type="button"
            id="{{ $recentId }}-prev"
            class="product-show-rec__nav product-show-rec__nav--prev"
            aria-label="Previous products"
            disabled
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <div id="{{ $recentId }}-wrap" class="product-show-rec__track-wrap mobile-scroll-hide">
            <div id="{{ $recentId }}-track" class="product-show-rec__track product-show-rec__track--mini"></div>
        </div>

        <button
            type="button"
            id="{{ $recentId }}-next"
            class="product-show-rec__nav product-show-rec__nav--next"
            aria-label="Next products"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sectionId = @json($recentId);
    var cardsUrl = @json(route('products.recently-viewed-cards'));
    var currentProductId = {{ $product->id }};
    var section = document.getElementById(sectionId + '-section');
    var container = document.getElementById(sectionId + '-track');
    var wrapper = document.getElementById(sectionId + '-wrapper');
    var wrap = document.getElementById(sectionId + '-wrap');
    var prevBtn = document.getElementById(sectionId + '-prev');
    var nextBtn = document.getElementById(sectionId + '-next');

    if (!container || !section) return;

    function scrollTrack(direction) {
        if (!wrap) return;
        var pageWidth = wrap.clientWidth;
        if (!pageWidth) return;
        wrap.scrollBy({ left: direction === 'next' ? pageWidth : -pageWidth, behavior: 'smooth' });
    }

    function updateNav() {
        if (!wrap || !prevBtn || !nextBtn) return;
        var hasOverflow = wrap.scrollWidth > wrap.clientWidth + 2;
        prevBtn.disabled = wrap.scrollLeft <= 1;
        nextBtn.disabled = wrap.scrollLeft >= wrap.scrollWidth - wrap.clientWidth - 2;
        prevBtn.classList.toggle('product-show-rec__nav--visible', hasOverflow);
        nextBtn.classList.toggle('product-show-rec__nav--visible', hasOverflow);
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { scrollTrack('prev'); });
    if (nextBtn) nextBtn.addEventListener('click', function () { scrollTrack('next'); });
    if (wrap) wrap.addEventListener('scroll', updateNav, { passive: true });
    window.addEventListener('resize', updateNav);

    async function loadRecentlyViewed() {
        var recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
        var ids = recentlyViewed
            .filter(function (p) { return p.id !== currentProductId; })
            .slice(0, 6)
            .map(function (item) { return item.id; })
            .filter(Boolean);

        if (ids.length === 0) {
            section.classList.add('hidden');
            return;
        }

        try {
            var params = new URLSearchParams();
            params.append('variant', 'mini');
            ids.forEach(function (id) { params.append('ids[]', id); });
            var response = await fetch(cardsUrl + '?' + params.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error('Failed');
            var data = await response.json();
            if (!data.html) throw new Error('Empty');
            container.innerHTML = data.html;
            section.classList.remove('hidden');
            if (wrapper) wrapper.classList.remove('hidden');
            updateNav();
        } catch (e) {
            section.classList.add('hidden');
        }
    }

    loadRecentlyViewed();
});
</script>
