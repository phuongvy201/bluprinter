@php
    $recentlyViewedId = $recentlyViewedId ?? 'recently-viewed';
    $recentlyViewedHeadingId = $recentlyViewedId . '-heading';
    $hideWhenEmpty = $hideWhenEmpty ?? true;
@endphp
<div class="catalog-recently-viewed scroll-reveal hidden" id="{{ $recentlyViewedId }}-section" aria-labelledby="{{ $recentlyViewedHeadingId }}">
    <div class="catalog-recently-viewed__head section-heading section-heading--catalog">
        <p class="section-heading__eyebrow">{{ $eyebrow ?? 'Continue browsing' }}</p>
        <h2 id="{{ $recentlyViewedHeadingId }}" class="section-heading__title">
            Recently <span class="gradient-text">Viewed</span>
        </h2>
        <p class="section-heading__sub">{{ $subtitle ?? 'Pick up where you left off with products you have explored' }}</p>
        <span class="section-heading__accent" aria-hidden="true"></span>
    </div>

    <div class="recently-viewed">
        <div class="recently-viewed__slider hidden" id="{{ $recentlyViewedId }}-wrapper">
            <button type="button"
                    id="{{ $recentlyViewedId }}-prev"
                    class="recently-viewed__nav recently-viewed__nav--prev"
                    aria-label="Previous products"
                    disabled>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <div id="{{ $recentlyViewedId }}-wrap" class="recently-viewed__track-wrap mobile-scroll-hide">
                <div id="{{ $recentlyViewedId }}-track" class="recently-viewed__track"></div>
            </div>

            <button type="button"
                    id="{{ $recentlyViewedId }}-next"
                    class="recently-viewed__nav recently-viewed__nav--next"
                    aria-label="Next products">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <div id="{{ $recentlyViewedId }}-empty" class="recently-viewed__empty hidden">
            <svg class="recently-viewed__empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <p class="recently-viewed__empty-title">No products viewed yet</p>
            <p class="recently-viewed__empty-sub">Products you browse will appear here for quick access</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sectionId = @json($recentlyViewedId);
    var hideWhenEmpty = @json($hideWhenEmpty);
    var cardsUrl = @json(route('products.recently-viewed-cards'));
    var section = document.getElementById(sectionId + '-section');
    var container = document.getElementById(sectionId + '-track');
    var emptyState = document.getElementById(sectionId + '-empty');
    var wrapper = document.getElementById(sectionId + '-wrapper');
    var wrap = document.getElementById(sectionId + '-wrap');
    var prevBtn = document.getElementById(sectionId + '-prev');
    var nextBtn = document.getElementById(sectionId + '-next');

    if (!container || !section) return;

    function hideSection() {
        section.classList.add('hidden');
    }

    function showSection() {
        section.classList.remove('hidden');
    }

    function getMetrics() {
        if (!wrap || !container || !container.children.length) return null;
        var firstItem = container.children[0];
        var gap = parseFloat(getComputedStyle(container).columnGap || getComputedStyle(container).gap || '16');
        return { wrap: wrap, step: firstItem.offsetWidth + gap };
    }

    function updateNav() {
        var metrics = getMetrics();
        if (!metrics || !prevBtn || !nextBtn) return;
        var hasOverflow = metrics.wrap.scrollWidth > metrics.wrap.clientWidth + 2;
        prevBtn.disabled = metrics.wrap.scrollLeft <= 1;
        nextBtn.disabled = metrics.wrap.scrollLeft >= metrics.wrap.scrollWidth - metrics.wrap.clientWidth - 2;
        prevBtn.classList.toggle('recently-viewed__nav--visible', hasOverflow);
        nextBtn.classList.toggle('recently-viewed__nav--visible', hasOverflow);
    }

    function scrollTrack(direction) {
        var metrics = getMetrics();
        if (!metrics) return;
        metrics.wrap.scrollBy({ left: direction === 'next' ? metrics.step : -metrics.step, behavior: 'smooth' });
    }

    if (prevBtn) prevBtn.addEventListener('click', function () { scrollTrack('prev'); });
    if (nextBtn) nextBtn.addEventListener('click', function () { scrollTrack('next'); });
    if (wrap) wrap.addEventListener('scroll', updateNav, { passive: true });

    async function loadRecentlyViewed() {
        var recentlyViewed = JSON.parse(localStorage.getItem('recentlyViewed') || '[]');
        var ids = recentlyViewed.slice(0, 12).map(function (product) { return product.id; }).filter(Boolean);

        if (ids.length === 0) {
            if (hideWhenEmpty) {
                hideSection();
                return;
            }
            showSection();
            if (wrapper) wrapper.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        showSection();
        if (wrapper) wrapper.classList.remove('hidden');
        if (emptyState) emptyState.classList.add('hidden');

        try {
            var params = new URLSearchParams();
            ids.forEach(function (id) { params.append('ids[]', id); });
            var response = await fetch(cardsUrl + '?' + params.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error('Failed');
            var data = await response.json();
            if (!data.html) throw new Error('Empty');
            container.innerHTML = data.html;
            showSection();
            updateNav();
        } catch (e) {
            if (hideWhenEmpty) {
                hideSection();
                return;
            }
            showSection();
            if (wrapper) wrapper.classList.add('hidden');
            if (emptyState) emptyState.classList.remove('hidden');
        }
    }

    loadRecentlyViewed();
});
</script>
