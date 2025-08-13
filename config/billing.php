<?php

return [
    'plans' => [
        [
            'key' => 'free',
            'name' => 'Free',
            'price' => 0,
            'currency' => env('PAYCHANGU_CURRENCY', 'MWK'),
            'interval' => 'month',
            'interval_count' => 1,
            'features' => ['Basic features'],
            'active' => true,
        ],
        [
            'key' => 'pro',
            'name' => 'Pro',
            'price' => 25000,
            'currency' => env('PAYCHANGU_CURRENCY', 'MWK'),
            'interval' => 'month',
            'interval_count' => 1,
            'features' => ['Everything in Free', 'Priority support', 'Advanced features'],
            'active' => true,
        ],
        [
            'key' => 'business',
            'name' => 'Business',
            'price' => 60000,
            'currency' => env('PAYCHANGU_CURRENCY', 'MWK'),
            'interval' => 'month',
            'interval_count' => 1,
            'features' => ['Everything in Pro', 'Team features', 'Billing automation'],
            'active' => true,
        ],
    ],
];
