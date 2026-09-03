<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Envato Item ID
    |--------------------------------------------------------------------------
    |
    | The unique Envato item ID associated with this product.
    |
    */
    'item_id' => env('LICENSE_ITEM_ID', '61977497'),

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | These routes will bypass the licensing check middleware. By default,
    | the license activation route and typical login/logout/static asset
    | routes should be excluded to prevent infinite redirection loops.
    |
    */
    'excluded_routes' => [
        'license/*',
        'api/v1/license/*',
        'login',
        'logout',
        'css/*',
        'js/*',
        'images/*',
        'favicon.ico',
        '_debugbar/*',
    ],
];
