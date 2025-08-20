<?php

return [
    // Enable/disable tracking
    'enabled' => env('MATOMO_ENABLED', true),

    // Full Matomo tracking endpoint (must include /matomo.php)
    // Example: https://analytics.example.com/matomo.php
    'base_url' => env('MATOMO_BASE_URL', ''),

    // Matomo site ID
    'site_id' => env('MATOMO_SITE_ID', null),

    // Optional token_auth (can be null for anonymous tracking)
    'token' => env('MATOMO_TOKEN', null),

    // Paths that should never be tracked
    'ignore' => [
        'telescope/*', 'horizon/*', 'health', '_debugbar/*',
    ],

    // HTTP client behavior
    'timeout' => env('MATOMO_TIMEOUT', 2),
    'retry' => [
        'times' => env('MATOMO_RETRY_TIMES', 0),
        'sleep' => env('MATOMO_RETRY_SLEEP_MS', 100), // milliseconds
    ],
];
