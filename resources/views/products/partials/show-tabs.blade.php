{{-- Product detail tabs — expects $product, $defaultShippingRate --}}
<div class="product-show-tabs" data-product-show-tabs>
    <div class="product-show-tabs__nav border-b border-gray-200" role="tablist" aria-label="Product details">
        <nav class="product-show-tabs__nav-inner -mb-px flex flex-wrap gap-x-6 gap-y-2">
            <button
                type="button"
                role="tab"
                id="product-show-tab-description"
                class="product-show-tabs__tab product-show-tabs__tab--active border-b-2 border-[#005366] py-3 px-1 text-sm font-medium text-[#005366]"
                aria-selected="true"
                aria-controls="product-show-panel-description"
                data-product-show-tab="description"
            >
                Description
            </button>
            <button
                type="button"
                role="tab"
                id="product-show-tab-additional"
                class="product-show-tabs__tab border-b-2 border-transparent py-3 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                aria-selected="false"
                aria-controls="product-show-panel-additional"
                data-product-show-tab="additional"
            >
                Additional Information
            </button>
            <button
                type="button"
                role="tab"
                id="product-show-tab-reviews"
                class="product-show-tabs__tab border-b-2 border-transparent py-3 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                aria-selected="false"
                aria-controls="product-show-panel-reviews"
                data-product-show-tab="reviews"
            >
                Reviews
            </button>
        </nav>
    </div>

    <div class="product-show-tabs__panels mt-6">
        <div
            role="tabpanel"
            id="product-show-panel-description"
            class="product-show-tabs__panel product-show-tabs__panel--active"
            aria-labelledby="product-show-tab-description"
            data-product-show-panel="description"
        >
            @include('products.partials.show-description')
        </div>

        <div
            role="tabpanel"
            id="product-show-panel-additional"
            class="product-show-tabs__panel hidden"
            aria-labelledby="product-show-tab-additional"
            data-product-show-panel="additional"
            hidden
        >
            @include('products.partials.show-additional-info')
        </div>

        <div
            role="tabpanel"
            id="product-show-panel-reviews"
            class="product-show-tabs__panel hidden"
            aria-labelledby="product-show-tab-reviews"
            data-product-show-panel="reviews"
            hidden
        >
            @include('products.partials.show-reviews')
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

                tab.classList.toggle('product-show-tabs__tab--active', isActive);
                tab.classList.toggle('border-[#005366]', isActive);
                tab.classList.toggle('text-[#005366]', isActive);
                tab.classList.toggle('border-transparent', !isActive);
                tab.classList.toggle('text-gray-500', !isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach(function (panel) {
                const isActive = panel.getAttribute('data-product-show-panel') === tabName;

                panel.classList.toggle('product-show-tabs__panel--active', isActive);
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
