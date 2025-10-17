<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Default Shop Configuration
    |--------------------------------------------------------------------------
    |
    | This value is used as the default shop ID when creating products via API
    | if no shop_id is specified in the request and the API token doesn't have
    | a default_shop_id configured.
    |
    */

    'default_shop_id' => env('API_DEFAULT_SHOP_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API requests
    |
    */

    'rate_limit' => [
        'enabled' => env('API_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('API_RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('API_RATE_LIMIT_DECAY_MINUTES', 1),
    ],

];
