<?php

return [
    'welcome' => [
        'percent_min' => 10,
        'percent_max' => 15,
        'default_percent' => 12,
        'expires_days' => 30,
        'min_order_amount' => 0,
    ],

    'thank_you' => [
        'percent' => 10,
        'expires_days' => 60,
        'min_order_amount' => 0,
    ],

    'win_back' => [
        'percent' => 18,
        'expires_days' => 14,
        'min_order_amount' => 0,
        'inactive_days' => [30, 60, 90],
    ],

    'newsletter' => [
        'percent' => 10,
        'expires_days' => 30,
        'min_order_amount' => 0,
    ],

    'vip' => [
        'min_completed_orders' => 3,
        'default_percent' => 15,
        'expires_days' => 90,
    ],

    // Promo code OR volume discount — not both
    'mutually_exclusive_with_volume' => true,

    // Urgency countdown under cart/checkout Total (MM:SS)
    'checkout_hold_seconds' => 600,

    'fixed_codes' => [
        'cart' => 'CART5',
        'wishlist' => 'FAVORITE5',
        'welcome' => 'FIRSTSALE',
        'thank_you' => 'THANKYOU10',
        'signup' => 'SIGNUP10',
        'newsletter' => 'NEWSLETTER10',
    ],

    'action_codes' => [
        'cart' => [
            'code' => 'CART5',
            'action' => 'Add to cart',
            'hint' => 'Add any product to your cart to unlock this code.',
            'cta' => 'Shop products',
            'route' => 'products.index',
        ],
        'wishlist' => [
            'code' => 'FAVORITE5',
            'action' => 'Add to wishlist',
            'hint' => 'Tap the heart on a product to save it and unlock this code.',
            'cta' => 'Browse products',
            'route' => 'products.index',
        ],
        'signup' => [
            'code' => 'SIGNUP10',
            'action' => 'Create an account',
            'hint' => 'Sign up to enjoy 10% off your first order.',
            'cta' => 'Sign up',
            'route' => 'register',
        ],
    ],
];
