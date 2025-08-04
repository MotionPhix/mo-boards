<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayChangu Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PayChangu payment gateway integration
    |
    */

    'base_url' => env('PAYCHANGU_BASE_URL', 'https://api.paychangu.com'),

    'client_id' => env('PAYCHANGU_CLIENT_ID'),

    'client_secret' => env('PAYCHANGU_CLIENT_SECRET'),

    'webhook_secret' => env('PAYCHANGU_WEBHOOK_SECRET'),

    'callback_url' => env('PAYCHANGU_CALLBACK_URL', '/contract-templates/payment/callback'),

    'currency' => env('PAYCHANGU_CURRENCY', 'MWK'),

    'environment' => env('PAYCHANGU_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'
];
