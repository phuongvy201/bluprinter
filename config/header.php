<?php

return [
    'tagline' => 'spice up your life',
    'app_label' => 'Get the Bluprinter App',
    'app_url' => '/',
    'promo_label' => 'PROMO CODES',
    'promo_url' => '/promo-code',
    'search_placeholders' => [
        'Tumbler',
        'Custom T-Shirt',
        'Personalized Mug',
        'Phone Case',
        'Wall Art',
    ],
    'trending_searches' => [
        'backpack',
        'seahawks',
        'bts',
        'handbag',
        'christmas ornaments',
    ],
    'announcement_slides' => [
        [
            'primary' => '3,500,000+ Happy Customers',
            'secondary' => 'Since 2021',
            'show_stars' => true,
        ],
        [
            'primary' => 'Free Shipping Worldwide',
            'secondary' => 'On orders over $100',
            'show_stars' => false,
        ],
        [
            'primary' => 'Custom Print On Demand',
            'secondary' => 'Design it. We print it.',
            'show_stars' => false,
        ],
        [
            'primary' => 'Trusted by Creators',
            'secondary' => 'Sell & fulfill with ease',
            'show_stars' => true,
        ],
    ],
    'nav_links' => [
        ['label' => 'Create Your Own', 'url' => '/create-your-own', 'accent' => true, 'icon' => 'create'],
        ['label' => 'Order Tracking', 'url' => '/track-order', 'accent' => false, 'icon' => 'pin'],
        ['label' => 'Back To School', 'url' => '/products?q=back+to+school', 'accent' => false, 'icon' => null],
        ['label' => 'Products', 'url' => '/products', 'accent' => false, 'icon' => null, 'dropdown' => 'products'],
        ['label' => 'Collections', 'url' => '/collections', 'accent' => false, 'icon' => null, 'dropdown' => 'collections'],
        ['label' => 'Explore Design', 'url' => '/explore-design', 'accent' => false, 'icon' => null],
        ['label' => 'Blog', 'url' => '/blog', 'accent' => false, 'icon' => null, 'dropdown' => 'blog'],
    ],
    'user_menu' => [
        'guest_title' => 'Welcome back',
        'guest_subtitle' => 'Sign in to manage orders, wishlist,...',
        'auth_title' => 'Welcome back',
        'auth_subtitle' => 'Manage your account and orders',
        'items' => [
            ['key' => 'login', 'label' => 'Login', 'url' => '/login', 'guest_only' => true],
            ['key' => 'wishlist', 'label' => 'Wishlist', 'url' => '/wishlist', 'guest_only' => false],
            ['key' => 'tracking', 'label' => 'Order Tracking', 'url' => '/track-order', 'guest_only' => false],
            ['key' => 'orders', 'label' => 'My Orders', 'url' => '/my/orders', 'auth_only' => true],
            ['key' => 'profile', 'label' => 'Profile', 'url' => '/customer/profile', 'auth_only' => true],
            ['key' => 'logout', 'label' => 'Logout', 'url' => '/logout', 'auth_only' => true, 'method' => 'POST'],
        ],
    ],
    'creator_studio' => [
        'title' => 'Creator Studio',
        'subtitle' => 'Your all-in-one design workspace',
        'cards' => [
            [
                'title' => 'Create Your Own',
                'description' => 'Build products your way',
                'url' => '/create-your-own',
                'bg' => '#e8f7fb',
                'image' => '',
            ],
            [
                'title' => 'AI Design Gen',
                'description' => 'Generate unique designs',
                'url' => '/ai-design',
                'bg' => '#fff4e8',
                'image' => '',
            ],
            [
                'title' => 'Virtual Try-On',
                'description' => 'AI try-on before you buy',
                'url' => '/virtual-try-on',
                'bg' => '#f3eefc',
                'image' => '',
            ],
        ],
    ],
    'categories_limit' => 12,
    'collections_limit' => 12,
];
