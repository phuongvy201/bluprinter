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
            'admin.studio-products.*',
            'admin.studio-designs.*',
            'admin.studio-ai.*',
            'admin.reviews.*',
            'admin.collections.*'
        ) || $productsCatalogActive,
        'sales' => request()->routeIs(
            'admin.orders.*',
            'admin.returns.*',
            'seller.orders.*',
            'admin.flash-deals.*',
            'admin.promo-codes.*'
        ),
        'shipping' => request()->routeIs('admin.shipping-zones.*', 'admin.shipping-rates.*'),
        'content' => request()->routeIs(
            'admin.settings.header.*',
            'admin.settings.home.*',
            'admin.settings.product-show.*',
            'admin.settings.checkout.*',
            'admin.pages.*',
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

<nav class="flex-1 px-3 py-4 space-y-0.5" x-data="{ open: @js($openGroup) }">
    @if($isAdmin || $isSeller)
        <x-admin.sidebar-group id="overview" label="Overview" :active="$active['overview']">
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.analytics.index')" :active="request()->routeIs('admin.analytics.*')">Google Analytics</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.settings.analytics.edit')" :active="request()->routeIs('admin.settings.analytics.*')">Analytics settings</x-admin.sidebar-link>
            @else
                <x-admin.sidebar-link :href="route('admin.seller.dashboard')" :active="request()->routeIs('admin.seller.dashboard')">Dashboard</x-admin.sidebar-link>
            @endif
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin || $isSeller)
        <x-admin.sidebar-group id="users" label="Users & shops" :active="$active['users']">
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Users</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">Roles</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.seller-applications.index')" :active="request()->routeIs('admin.seller-applications.*')">Seller applications</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.shops.index')" :active="request()->routeIs('admin.shops.*')">Shops</x-admin.sidebar-link>
            @else
                <x-admin.sidebar-link :href="route('seller.shop.dashboard')" :active="request()->routeIs('seller.shop.*')">Shop profile</x-admin.sidebar-link>
            @endif
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin || $isSeller)
        <x-admin.sidebar-group id="products" label="Products" :active="$active['products']">
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">Categories</x-admin.sidebar-link>
            @endif
            <x-admin.sidebar-link :href="route('admin.product-templates.index')" :active="request()->routeIs('admin.product-templates.*')">
                {{ $isAdmin ? 'Templates' : 'My templates' }}
            </x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.products.index')" :active="$productsCatalogActive">
                {{ $isAdmin ? 'Products' : 'My products' }}
            </x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.collections.index')" :active="request()->routeIs('admin.collections.*')">
                {{ $isAdmin ? 'Collections' : 'My collections' }}
            </x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.reviews.index')" :active="request()->routeIs('admin.reviews.*')">Reviews</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.studio-designs.index')" :active="request()->routeIs('admin.studio-designs.*')">
                {{ $isAdmin ? 'Studio designs' : 'My designs' }}
            </x-admin.sidebar-link>
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.studio-products.index')" :active="request()->routeIs('admin.studio-products.*')">Studio garments</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.studio-ai.index')" :active="request()->routeIs('admin.studio-ai.index', 'admin.studio-ai.hide', 'admin.studio-ai.promote', 'admin.studio-ai.destroy')">Studio AI</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.studio-ai.settings')" :active="request()->routeIs('admin.studio-ai.settings*')">AI settings</x-admin.sidebar-link>
            @endif
        </x-admin.sidebar-group>
    @endif

    <x-admin.sidebar-group id="sales" label="Sales" :active="$active['sales']">
        @if($isAdmin || $isAdPartner)
            <x-admin.sidebar-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">
                <span class="flex items-center">
                    Orders
                    @if(isset($sidebarPendingOrders) && $sidebarPendingOrders > 0)
                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">{{ $sidebarPendingOrders }}</span>
                    @endif
                </span>
            </x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.returns.index')" :active="request()->routeIs('admin.returns.*')">
                <span class="flex items-center">
                    Returns
                    @if(isset($sidebarPendingReturns) && $sidebarPendingReturns > 0)
                        <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">{{ $sidebarPendingReturns }}</span>
                    @endif
                </span>
            </x-admin.sidebar-link>
        @elseif($isSeller)
            <x-admin.sidebar-link :href="route('seller.orders.index')" :active="request()->routeIs('seller.orders.*')">My orders</x-admin.sidebar-link>
        @endif
        @if($isAdmin)
            <x-admin.sidebar-link :href="route('admin.flash-deals.index')" :active="request()->routeIs('admin.flash-deals.*')">Flash deals</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.promo-codes.index')" :active="request()->routeIs('admin.promo-codes.*')">Promo codes</x-admin.sidebar-link>
        @endif
    </x-admin.sidebar-group>

    @if($isAdmin)
        <x-admin.sidebar-group id="shipping" label="Shipping" :active="$active['shipping']">
            <x-admin.sidebar-link :href="route('admin.shipping-zones.index')" :active="request()->routeIs('admin.shipping-zones.*')">Shipping zones</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.shipping-rates.index')" :active="request()->routeIs('admin.shipping-rates.*')">Shipping rates</x-admin.sidebar-link>
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin || $isSeller)
        <x-admin.sidebar-group id="content" label="Content" :active="$active['content']">
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.settings.header.edit')" :active="request()->routeIs('admin.settings.header.*')">Header layout</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.settings.home.edit')" :active="request()->routeIs('admin.settings.home.*')">Homepage</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.settings.product-show.edit')" :active="request()->routeIs('admin.settings.product-show.*')">Product page</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.settings.checkout.edit')" :active="request()->routeIs('admin.settings.checkout.*')">Checkout</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.pages.index')" :active="request()->routeIs('admin.pages.*')">Pages</x-admin.sidebar-link>
            @endif
            <x-admin.sidebar-link :href="route('admin.posts.index')" :active="request()->routeIs('admin.posts.*')">
                {{ $isAdmin ? 'Blog posts' : 'My posts' }}
            </x-admin.sidebar-link>
            @if($isAdmin)
                <x-admin.sidebar-link :href="route('admin.post-categories.index')" :active="request()->routeIs('admin.post-categories.*')">Post categories</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.post-tags.index')" :active="request()->routeIs('admin.post-tags.*')">Post tags</x-admin.sidebar-link>
            @endif
        </x-admin.sidebar-group>
    @endif

    @if($isAdmin)
        <x-admin.sidebar-group id="tools" label="Tools" :active="$active['tools']">
            <x-admin.sidebar-link :href="route('admin.settings.gmc-config.index')" :active="request()->routeIs('admin.settings.gmc-config.*')">GMC config</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.settings.domain-config.index')" :active="request()->routeIs('admin.settings.domain-config.*')">Domain config</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.products.show-delete-from-gmc')" :active="request()->routeIs('admin.products.show-delete-from-gmc')" :danger="true">Remove product GMC</x-admin.sidebar-link>
            <x-admin.sidebar-link :href="route('admin.api-token')" :active="request()->routeIs('admin.api-token')">API token</x-admin.sidebar-link>
        </x-admin.sidebar-group>
    @endif
</nav>
