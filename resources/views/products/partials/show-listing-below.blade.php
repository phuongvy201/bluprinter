{{-- Shipping facts + Description / Materials / Sizing tabs — purchase column, below Add to Cart --}}
@php
    $deliverFrom = now()->addWeekdays(10);
    $deliverTo = now()->addWeekdays(18);
    $deliveryWindow = $deliverFrom->month === $deliverTo->month
        ? $deliverFrom->format('M j') . '–' . $deliverTo->format('j')
        : $deliverFrom->format('M j') . '–' . $deliverTo->format('M j');

    $names = $countryNamesMap ?? [];
    $shopCountryRaw = trim((string) ($product->shop->shop_country ?? 'US'));
    $shopCountryKey = strtoupper($shopCountryRaw);
    $shipsFromCountry = ($names[$shopCountryKey] ?? null)
        ?: ($shopCountryRaw !== '' ? $shopCountryRaw : 'United States');

    $materialsCare = 'Printed to order on a quality garment. Machine wash cold, inside out, with like colors. Tumble dry low. Do not iron the decoration. Do not dry clean.';
    $categoryName = strtolower((string) ($primaryCategory ?? ''));
    if (str_contains($categoryName, 'hoodie') || str_contains($categoryName, 'fleece')) {
        $materialsCare = 'Midweight fleece with a brushed interior, ribbed cuffs and hem, and a kangaroo pocket. Machine wash cold, inside out. Tumble dry low. Do not iron the print.';
    } elseif (str_contains($categoryName, 'shirt') || str_contains($categoryName, 'tee')) {
        $materialsCare = 'Soft cotton or cotton-blend fabric, printed to order. Machine wash cold, inside out, with like colors. Tumble dry low. Do not iron the decoration.';
    }
@endphp

<div class="product-show-listing">
    <ul class="product-show-facts" aria-label="Shipping and delivery">
        <li class="product-show-facts__row">
            <svg class="product-show-facts__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p>Order today to get by <strong>{{ $deliveryWindow }}</strong></p>
        </li>
        <li class="product-show-facts__row">
            <svg class="product-show-facts__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
            <p>
                Returns &amp; exchanges accepted
                <button type="button" class="product-show-facts__info" onclick="toggleReturnsInfo()" aria-label="Returns information">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
            </p>
        </li>
        <li class="product-show-facts__row product-show-facts__row--popup">
            <div id="returns-info-popup" class="product-show-facts__popup hidden">
                <p>Free returns are available for the shipping address you chose. You can return the item for any reason in new and unused condition: no return shipping charges.</p>
                <a href="{{ route('page.show', 'returns-exchanges-policy') }}">Read the full returns policy</a>
            </div>
        </li>
        <li class="product-show-facts__row">
            <svg class="product-show-facts__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v9h2m2 0h6m0 0h6l3-5h-9v5z"/>
            </svg>
            <p>Cost to ship: <strong>Calculated at checkout</strong></p>
        </li>
        <li class="product-show-facts__row">
            <svg class="product-show-facts__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
            <p>Ships from: <strong>{{ $shipsFromCountry }}</strong></p>
        </li>
        <li class="product-show-facts__row product-show-facts__row--deliver">
            <svg class="product-show-facts__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p>Deliver to: <strong id="customer-location">your location</strong></p>
        </li>
        <li class="product-show-facts__row">
            <svg class="product-show-facts__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p>
                <span id="delivery-estimate">{{ optional($defaultShippingRate ?? null)->description ?: 'Calculating delivery time...' }}</span>
            </p>
        </li>
    </ul>

    <div class="product-show-listing-tabs" data-product-show-tabs>
        <div class="product-show-listing-tabs__nav" role="tablist" aria-label="Product details">
            <button type="button" role="tab" id="product-show-tab-description" class="product-show-listing-tabs__tab product-show-listing-tabs__tab--active" aria-selected="true" aria-controls="product-show-panel-description" data-product-show-tab="description">Description</button>
            <button type="button" role="tab" id="product-show-tab-materials" class="product-show-listing-tabs__tab" aria-selected="false" aria-controls="product-show-panel-materials" data-product-show-tab="materials">Materials &amp; care</button>
            <button type="button" role="tab" id="product-show-tab-sizing" class="product-show-listing-tabs__tab" aria-selected="false" aria-controls="product-show-panel-sizing" data-product-show-tab="sizing">Sizing</button>
        </div>

        <div class="product-show-listing-tabs__panels">
            <div role="tabpanel" id="product-show-panel-description" class="product-show-listing-tabs__panel" aria-labelledby="product-show-tab-description" data-product-show-panel="description">
                @include('products.partials.show-description')
            </div>
            <div role="tabpanel" id="product-show-panel-materials" class="product-show-listing-tabs__panel hidden" aria-labelledby="product-show-tab-materials" data-product-show-panel="materials" hidden>
                <p class="product-show-listing-tabs__copy">{{ $materialsCare }}</p>
            </div>
            <div role="tabpanel" id="product-show-panel-sizing" class="product-show-listing-tabs__panel hidden" aria-labelledby="product-show-tab-sizing" data-product-show-panel="sizing" hidden>
                <p class="product-show-listing-tabs__copy">If you're in between sizes, order a size up as our items can shrink up to half a size in the wash.</p>
                <button type="button" class="product-show-listing-tabs__guide" onclick="openSizeGuide()">View size guide</button>
            </div>
        </div>
    </div>
</div>

@once
<script>
(function () {
    function initProductShowTabs(root) {
        const tabs = root.querySelectorAll('[data-product-show-tab]');
        const panels = root.querySelectorAll('[data-product-show-panel]');

        if (!tabs.length || !panels.length) {
            return;
        }

        function activateTab(tabName) {
            tabs.forEach(function (tab) {
                const isActive = tab.getAttribute('data-product-show-tab') === tabName;
                tab.classList.toggle('product-show-listing-tabs__tab--active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach(function (panel) {
                const isActive = panel.getAttribute('data-product-show-panel') === tabName;
                panel.classList.toggle('hidden', !isActive);
                panel.hidden = !isActive;
            });
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                activateTab(tab.getAttribute('data-product-show-tab'));
            });
        });
    }

    document.querySelectorAll('[data-product-show-tabs]').forEach(initProductShowTabs);
})();
</script>
@endonce
