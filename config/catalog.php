<?php

return [
    'products_index' => [
        'promo' => [
            'enabled' => true,
            'position' => 'after_toolbar',
            'title' => 'Design Your Own — Print On Demand',
            'subtitle' => 'Upload art, pick a product, we print & ship worldwide.',
            'cta_label' => 'Start customizing',
            'url' => '/create-your-own',
            'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=1200&q=80',
            'background' => 'linear-gradient(135deg, rgba(0,83,102,0.92) 0%, rgba(0,61,77,0.95) 55%, rgba(242,101,34,0.85) 100%)',
        ],
        'seo' => [
            'enabled' => true,
            'position' => 'bottom',
            'eyebrow' => 'Why Bluprinter',
            'title' => 'Custom Print-On-Demand Products',
            'intro' => 'A global marketplace where independent creators sell made-to-order apparel, accessories, home decor, and gifts — unique designs without bulk inventory or waste.',
            'highlights' => [
                [
                    'title' => 'Made to order',
                    'text' => 'Every item is printed after you order. Fresh designs from our seller community, updated daily.',
                    'tone' => 'petrol',
                    'icon' => 'spark',
                ],
                [
                    'title' => 'Browse your way',
                    'text' => 'Filter by category or shop, sort by price or newest, and switch between grid and list view.',
                    'tone' => 'orange',
                    'icon' => 'filter',
                ],
                [
                    'title' => 'Print & ship worldwide',
                    'text' => 'Professional printing, secure checkout, and global delivery on every product.',
                    'tone' => 'cta',
                    'icon' => 'globe',
                ],
            ],
        ],
    ],

    'collections_index' => [
        'hero' => [
            'eyebrow' => 'Curated',
            'title' => 'Our',
            'title_accent' => 'Collections',
            'subtitle' => 'Discover curated series of custom print-on-demand products — designed for every mood, aesthetic, and occasion.',
        ],
    ],

    'blog_index' => [
        'hero' => [
            'eyebrow' => 'Stories',
            'title' => 'Our',
            'title_accent' => 'Blog',
            'subtitle' => 'Stories, tips, and insights from our print-on-demand community.',
        ],
    ],

    'product_show' => [
        'sale_ends_date' => '2026-09-01',
        // USD subtotal (after discount) required to unlock free shipping
        'free_shipping_threshold_usd' => 100,
        'volume_discounts' => [
            ['min_quantity' => 2, 'discount_percent' => 10, 'is_popular' => false],
            ['min_quantity' => 3, 'discount_percent' => 20, 'is_popular' => true],
            ['min_quantity' => 5, 'discount_percent' => 30, 'is_popular' => false],
        ],
        'virtual_stats' => [
            'views_base' => 800,
            'views_multiplier' => 37,
            'in_cart_base' => 40,
            'in_cart_multiplier' => 1,
        ],
        'fbt_limit' => 4,
        'collection_related_limit' => 6,
        'recently_viewed_limit' => 6,
    ],

    'collection_ai' => [
        'enabled' => env('COLLECTION_AI_ENABLED', true),
        'max_collections_per_product' => 8,
        'product_chunk' => 25,
    ],
];
