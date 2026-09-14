@php
    $user = auth()->user();
    $isAdmin = $user->hasRole('admin');
    $isSeller = $user->hasRole('seller');
    $isAdPartner = $user->hasRole('ad-partner');

    $productsCatalogActive = request()->routeIs('admin.products.*') && ! request()->routeIs('admin.products.show-delete-from-gmc');

    $active = [
        'overview' => $isAdmin
            ? request()->routeIs('admin.dashboard', 'admin.analytics.*', 'admin.settings.analytics.*')
            : request()->routeIs('admin.seller.dashboard'),
        'users' => $isAdmin
            ? request()->routeIs('admin.users.*', 'admin.roles.*', 'admin.seller-applications.*', 'admin.shops.*')
            : request()->routeIs('seller.shop.*'),
        'products' => request()->routeIs(
            'admin.categories.*',
            'admin.product-templates.*',
            'admin.reviews.*',
            'admin.collections.*'
        ) || $productsCatalogActive,
        'studio' => request()->routeIs(
            'admin.studio-products.*',
            'admin.studio-designs.*',
            'admin.studio-ai.*'
        ),
        'sales' => request()->routeIs(
            'admin.orders.*',
            'admin.returns.*',
            'seller.orders.*',
            'admin.flash-deals.*',
            'admin.promo-codes.*'
        ),
        'shipping' => request()->routeIs('admin.shipping-zones.*', 'admin.shipping-rates.*'),
        'storefront' => request()->routeIs(
            'admin.settings.header.*',
            'admin.settings.home.*',
            'admin.settings.product-show.*',
            'admin.settings.checkout.*',
            'admin.pages.*'
        ),
        'blog' => request()->routeIs(
            'admin.posts.*',
            'admin.post-categories.*',
            'admin.post-tags.*'
        ),
        'tools' => request()->routeIs(
            'admin.settings.gmc-config.*',
            'admin.settings.domain-config.*',
            'admin.products.show-delete-from-gmc',
            'admin.api-token'
        ),
    ];

    $openGroup = collect($active)->search(true) ?: ($isAdmin ? 'overview' : ($isSeller ? 'overview' : 'sales'));
@endphp

<nav class="flex-1 px-2.5 py-3 space-y-0.5"
     x-data="adminSidebarNav(@js($openGroup))"
     @keydown.window="onGlobalKeydown($event)">
    {{-- Search --}}
    <div class="mb-3 px-0.5 sticky top-0 z-10 bg-white pt-1 pb-2 border-b border-gray-100" x-show="!$store.adminUi.sidebarCollapsed" x-cloak>
        <label for="admin-sidebar-search" class="sr-only">Search menu</label>
        <div class="relative">
            <svg class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
            </svg>
            <input id="admin-sidebar-search"
                   type="search"
                   x-ref="search"
                   x-model="q"
                   @input.debounce.100ms="onSearch()"
                   placeholder="Search menu…"
                   autocomplete="off"
                   class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-8 pr-14 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20">
            <kbd class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 hidden sm:inline-flex items-center gap-0.5 rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-medium text-gray-400"
                 x-show="!q.trim()"
                 title="Press Ctrl+K or /">Ctrl K</kbd>
            <button type="button"
                    x-show="q.trim()"
                    x-cloak
                    @click="q = ''; onSearch()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-0.5 text-gray-400 hover:text-gray-600"
                    aria-label="Clear search">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="mt-1.5 px-0.5 text-[11px] text-gray-400" x-show="q.trim()" x-cloak>
            Highlighting matches · <span class="font-medium">Esc</span> to clear
        </p>
    </div>

    <button type="button"
            x-show="$store.adminUi.sidebarCollapsed"
            x-cloak
            @click="$store.adminUi.sidebarCollapsed = false; $nextTick(() => $refs.search?.focus())"
            class="mb-2 mx-auto flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-500 hover:bg-blue-50 hover:text-blue-700"
            title="Search menu (Ctrl+K)">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
    </button>

    {{-- Pinned --}}
    <div class="mb-2" x-show="!$store.adminUi.sidebarCollapsed && pins.length" x-cloak>
        <div class="flex items-center gap-2 px-2.5 py-1.5">
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </span>
            <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-gray-400">Pinned</span>
        </div>
        <div class="space-y-0.5 pl-3 border-l border-amber-100 ml-5">
            <template x-for="pin in visiblePins()" :key="pin.href">
                <div class="relative group/pin">
                    <a :href="pin.href"
                       class="flex items-center gap-2 pl-3 pr-8 py-2 text-sm rounded-lg text-gray-700 hover:bg-amber-50 hover:text-amber-900 leading-snug"
                       :class="matchClass(pin.search || pin.label)"
                       @click="$store.adminUi.sidebarOpen = false">
                        <span class="truncate" x-text="pin.label"></span>
                    </a>
                    <button type="button"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 p-1 rounded text-amber-500 opacity-70 hover:opacity-100"
                            @click.prevent.stop="togglePin(pin)"
                            title="Unpin">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    @if($isAdmin || $isSeller)
        <x-admin.sidebar-group id="overview" label="Overview" icon="overview" :active="$active['overview']">
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" keywords="home overview" label="Dashboard">Dashboard</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.analytics.index')" :active="request()->routeIs('admin.analytics.*')" keywords="ga traffic report" label="Google Analytics">Google Analytics</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.settings.analytics.edit')" :active="request()->routeIs('admin.settings.analytics.*')" keywords="pixel gtm tracking" label="Analytics settings">Analytics settings</x-admin.sidebar-link>
            @else
                <x-admin.sidebar-link :href="route('admin.seller.dashboard')" :active="request()->routeIs('admin.seller.dashboard')" keywords="home overview" label="Dashboard">Dashboard</x-admin.sidebar-link>
            @endif
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin || $isSeller)
        <x-admin.sidebar-group id="users" label="Users & shops" icon="users" :active="$active['users']">
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" keywords="accounts people" label="Users">Users</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')" keywords="permission access" label="Roles">Roles</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.seller-applications.index')" :active="request()->routeIs('admin.seller-applications.*')" keywords="approve seller request" label="Seller applications">Seller applications</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.shops.index')" :active="request()->routeIs('admin.shops.*')" keywords="store seller shop" label="Shops">Shops</x-admin.sidebar-link>
            @else
                <x-admin.sidebar-link :href="route('seller.shop.dashboard')" :active="request()->routeIs('seller.shop.*')" keywords="store profile" label="Shop profile">Shop profile</x-admin.sidebar-link>
            @endif
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin || $isSeller)
        <x-admin.sidebar-group id="products" label="Catalog" icon="products" :active="$active['products']">
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')" keywords="catalog category" label="Categories">Categories</x-admin.sidebar-link>
            @endif
            <x-admin.sidebar-link :href="route('admin.product-templates.index')" :active="request()->routeIs('admin.product-templates.*')" keywords="catalog blank base template" :label="$isAdmin ? 'Templates' : 'My templates'">
                {{ $isAdmin ? 'Templates' : 'My templates' }}
            </x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.products.index')" :active="$productsCatalogActive" keywords="catalog listing sku variants all products" :label="$isAdmin ? 'All products' : 'My products'">
                {{ $isAdmin ? 'All products' : 'My products' }}
            </x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.collections.index')" :active="request()->routeIs('admin.collections.*')" keywords="catalog curated series" :label="$isAdmin ? 'Collections' : 'My collections'">
                {{ $isAdmin ? 'Collections' : 'My collections' }}
            </x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.reviews.index')" :active="request()->routeIs('admin.reviews.*')" keywords="rating feedback" label="Reviews">Reviews</x-admin.sidebar-link>
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin || $isSeller)
        <x-admin.sidebar-group id="studio" label="Creator Studio" icon="studio" :active="$active['studio']">
            <x-admin.sidebar-link :href="route('admin.studio-designs.index')" :active="request()->routeIs('admin.studio-designs.*')" keywords="studio design artwork creator" :label="$isAdmin ? 'Designs' : 'My designs'">
                {{ $isAdmin ? 'Designs' : 'My designs' }}
            </x-admin.sidebar-link>
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.studio-products.index')" :active="request()->routeIs('admin.studio-products.*')" keywords="studio garment blank apparel" label="Garments">Garments</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.studio-ai.index')" :active="request()->routeIs('admin.studio-ai.index', 'admin.studio-ai.hide', 'admin.studio-ai.promote', 'admin.studio-ai.destroy')" keywords="studio ai generate image" label="AI designs">AI designs</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.studio-ai.settings')" :active="request()->routeIs('admin.studio-ai.settings*')" keywords="studio ai config prompt" label="AI settings">AI settings</x-admin.sidebar-link>
            @endif
        </x-admin.sidebar-group>
    @endif

    <x-admin.sidebar-group id="sales" label="Sales" icon="sales" :active="$active['sales']">
        @if($isAdmin || $isAdPartner)
            <x-admin.sidebar-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')" keywords="order checkout purchase" label="Orders">
                <span class="flex items-center min-w-0">
                    <span class="truncate">Orders</span>
                    @if(isset($sidebarPendingOrders) && $sidebarPendingOrders > 0)
                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 shrink-0">{{ $sidebarPendingOrders }}</span>
                    @endif
                </span>
            </x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.returns.index')" :active="request()->routeIs('admin.returns.*')" keywords="refund rma" label="Returns">
                <span class="flex items-center min-w-0">
                    <span class="truncate">Returns</span>
                    @if(isset($sidebarPendingReturns) && $sidebarPendingReturns > 0)
                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700 shrink-0">{{ $sidebarPendingReturns }}</span>
                    @endif
                </span>
            </x-admin.sidebar-link>
        @elseif($isSeller)
            <x-admin.sidebar-link :href="route('seller.orders.index')" :active="request()->routeIs('seller.orders.*')" keywords="order checkout" label="My orders">My orders</x-admin.sidebar-link>
        @endif
        @if($isAdmin)
            <x-admin.sidebar-link :href="route('admin.flash-deals.index')" :active="request()->routeIs('admin.flash-deals.*')" keywords="sale promotion deal" label="Flash deals">Flash deals</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.promo-codes.index')" :active="request()->routeIs('admin.promo-codes.*')" keywords="coupon discount voucher code" label="Promo codes">Promo codes</x-admin.sidebar-link>
        @endif
    </x-admin.sidebar-group>

    @if($isAdmin)
        <x-admin.sidebar-group id="shipping" label="Shipping" icon="shipping" :active="$active['shipping']">
            <x-admin.sidebar-link :href="route('admin.shipping-zones.index')" :active="request()->routeIs('admin.shipping-zones.*')" keywords="delivery country zone" label="Shipping zones">Shipping zones</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.shipping-rates.index')" :active="request()->routeIs('admin.shipping-rates.*')" keywords="delivery price freight rate" label="Shipping rates">Shipping rates</x-admin.sidebar-link>
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin)
        <x-admin.sidebar-group id="storefront" label="Storefront" icon="content" :active="$active['storefront']">
            <x-admin.sidebar-link :href="route('admin.settings.header.edit')" :active="request()->routeIs('admin.settings.header.*')" keywords="storefront header nav announcement content" label="Header layout">Header layout</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.settings.home.edit')" :active="request()->routeIs('admin.settings.home.*')" keywords="storefront homepage landing content" label="Homepage">Homepage</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.settings.product-show.edit')" :active="request()->routeIs('admin.settings.product-show.*')" keywords="storefront pdp volume free shipping content" label="Product page">Product page</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.settings.checkout.edit')" :active="request()->routeIs('admin.settings.checkout.*')" keywords="storefront payment content" label="Checkout">Checkout</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.pages.index')" :active="request()->routeIs('admin.pages.*')" keywords="cms static page content" label="Pages">Pages</x-admin.sidebar-link>
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin || $isSeller)
        <x-admin.sidebar-group id="blog" label="Blog" icon="blog" :active="$active['blog']">
            <x-admin.sidebar-link :href="route('admin.posts.index')" :active="request()->routeIs('admin.posts.*')" keywords="blog article post content" :label="$isAdmin ? 'Blog posts' : 'My posts'">
                {{ $isAdmin ? 'Blog posts' : 'My posts' }}
            </x-admin.sidebar-link>
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.post-categories.index')" :active="request()->routeIs('admin.post-categories.*')" keywords="blog category content" label="Post categories">Post categories</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.post-tags.index')" :active="request()->routeIs('admin.post-tags.*')" keywords="blog tag content" label="Post tags">Post tags</x-admin.sidebar-link>
            @endif
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin)
        <x-admin.sidebar-group id="tools" label="Tools" icon="tools" :active="$active['tools']">
            <x-admin.sidebar-link :href="route('admin.settings.gmc-config.index')" :active="request()->routeIs('admin.settings.gmc-config.*')" keywords="google merchant center feed" label="GMC config">GMC config</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.settings.domain-config.index')" :active="request()->routeIs('admin.settings.domain-config.*')" keywords="domain currency locale" label="Domain config">Domain config</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.products.show-delete-from-gmc')" :active="request()->routeIs('admin.products.show-delete-from-gmc')" :danger="true" keywords="google merchant delete remove feed" label="Remove product GMC">Remove product GMC</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.api-token')" :active="request()->routeIs('admin.api-token')" keywords="api key token integration" label="API token">API token</x-admin.sidebar-link>
        </x-admin.sidebar-group>
    @endif

    <p class="px-3 py-3 text-xs text-gray-400" x-show="!$store.adminUi.sidebarCollapsed && q.trim() && !hasMatches()" x-cloak>
        No menu items match “<span x-text="q.trim()"></span>”
    </p>
</nav>

<script>
function adminSidebarNav(defaultOpen) {
    const PIN_KEY = 'bluprinter_admin_sidebar_pins';
    let pins = [];
    try { pins = JSON.parse(localStorage.getItem(PIN_KEY) || '[]'); } catch (e) { pins = []; }

    return {
        open: defaultOpen || 'overview',
        q: '',
        pins,
        get sidebarCollapsed() {
            return Alpine.store('adminUi')?.sidebarCollapsed ?? false;
        },
        set sidebarCollapsed(value) {
            const store = Alpine.store('adminUi');
            if (!store) return;
            store.sidebarCollapsed = !!value;
            localStorage.setItem('bluprinter_admin_sidebar_collapsed', store.sidebarCollapsed ? '1' : '0');
        },
        get sidebarOpen() {
            return Alpine.store('adminUi')?.sidebarOpen ?? false;
        },
        set sidebarOpen(value) {
            const store = Alpine.store('adminUi');
            if (store) store.sidebarOpen = !!value;
        },
        normalizedQuery() {
            return this.q.trim().toLowerCase();
        },
        isGroupOpen(id) {
            return !!this.normalizedQuery() || this.open === id;
        },
        toggleGroup(id) {
            if (Alpine.store('adminUi')?.sidebarCollapsed) {
                Alpine.store('adminUi').sidebarCollapsed = false;
                localStorage.setItem('bluprinter_admin_sidebar_collapsed', '0');
                this.open = id;
                return;
            }
            this.open = this.open === id ? '' : id;
        },
        linkVisible(text) {
            const query = this.normalizedQuery();
            if (!query) return true;
            return String(text || '').toLowerCase().includes(query);
        },
        matchClass(text) {
            const query = this.normalizedQuery();
            if (!query) return '';
            return String(text || '').toLowerCase().includes(query)
                ? 'ring-2 ring-amber-300/80 bg-amber-50 text-amber-950 font-medium'
                : '';
        },
        groupVisible(id) {
            const query = this.normalizedQuery();
            if (!query) return true;
            const root = Array.from(this.$el.querySelectorAll('[data-sidebar-group]')).find((el) => el.getAttribute('data-sidebar-group') === id);
            if (!root) return true;
            return Array.from(root.querySelectorAll('[data-sidebar-link]')).some((link) => {
                return (link.getAttribute('data-search') || '').includes(query);
            });
        },
        hasMatches() {
            const query = this.normalizedQuery();
            if (!query) return true;
            return Array.from(this.$el.querySelectorAll('[data-sidebar-link]')).some((link) => {
                return (link.getAttribute('data-search') || '').includes(query);
            });
        },
        onSearch() {
            // Groups auto-expand via isGroupOpen when q is set
        },
        isPinned(href) {
            return this.pins.some((p) => p.href === href);
        },
        togglePin(item) {
            if (!item?.href) return;
            if (this.isPinned(item.href)) {
                this.pins = this.pins.filter((p) => p.href !== item.href);
            } else {
                this.pins = [...this.pins, { href: item.href, label: item.label, search: item.search || item.label }];
            }
            localStorage.setItem(PIN_KEY, JSON.stringify(this.pins));
        },
        visiblePins() {
            const query = this.normalizedQuery();
            if (!query) return this.pins;
            return this.pins.filter((p) => String(p.search || p.label || '').toLowerCase().includes(query));
        },
        focusSearch() {
            const store = Alpine.store('adminUi');
            if (store?.sidebarCollapsed) {
                store.sidebarCollapsed = false;
                localStorage.setItem('bluprinter_admin_sidebar_collapsed', '0');
            }
            this.$nextTick(() => {
                const input = this.$refs.search || document.getElementById('admin-sidebar-search');
                input?.focus();
                input?.select?.();
            });
        },
        onGlobalKeydown(e) {
            // Avoid double-handling when both desktop + mobile nav exist in DOM
            if (!this.$el.getClientRects().length) return;

            const tag = (e.target?.tagName || '').toLowerCase();
            const typing = tag === 'input' || tag === 'textarea' || e.target?.isContentEditable;

            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                this.focusSearch();
                return;
            }

            if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey && !e.altKey) {
                e.preventDefault();
                this.focusSearch();
                return;
            }

            if (e.key === 'Escape' && this.q) {
                this.q = '';
                this.onSearch();
            }
        },
    };
}
</script>
