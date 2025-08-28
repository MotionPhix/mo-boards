<?php

return [
    // Which billing provider integration is active
    'provider' => env('BILLING_PROVIDER', 'paychangu'),

    // Trial and grace period policies
    'trial_days' => (int) env('BILLING_TRIAL_DAYS', 7),
    'grace_days' => (int) env('BILLING_GRACE_DAYS', 7),

    // Whether to force checkout immediately at signup for paid plans
    'enforce_at_signup' => (bool) env('BILLING_ENFORCE_AT_SIGNUP', false),

    // Default currency for pricing (override per plan as needed)
    'currency' => env('BILLING_CURRENCY', 'MWK'),

    // Route names used by middleware/UI for redirects and links
    'routes' => [
        'checkout' => 'billing.checkout',
        'portal' => 'billing.portal',
        'webhook' => 'billing.webhook',
    ],

    // PayChangu integration (Modern online payments for Malawi)
    // See: https://paychangu.com (adjust base URL per their API docs/env)
    'paychangu' => [
        'api_key' => env('PAYCHANGU_API_KEY'),
        'webhook_secret' => env('PAYCHANGU_WEBHOOK_SECRET'),
        'base_url' => env('PAYCHANGU_BASE_URL', 'https://api.paychangu.com'),
        'timeout' => (int) env('PAYCHANGU_TIMEOUT', 15),

        // Optional: map internal plan keys to external price IDs if PayChangu expects them
        'prices' => [
            // 'free' => null,
            // 'pro' => env('PAYCHANGU_PRICE_PRO'),
            // 'business' => env('PAYCHANGU_PRICE_BUSINESS'),
        ],
    ],
];
