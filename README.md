# Matomo Proxy for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mattitjaab/matomo-proxy-laravel.svg?style=flat-square)](https://packagist.org/packages/mattitjaab/matomo-proxy-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mattitjaab/matomo-proxy-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mattitjaab/matomo-proxy-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/mattitjaab/matomo-proxy-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/mattitjaab/matomo-proxy-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mattitjaab/matomo-proxy-laravel.svg?style=flat-square)](https://packagist.org/packages/mattitjaab/matomo-proxy-laravel)

This package provides a plug-and-play **server-side Matomo tracking middleware** for Laravel.
Instead of embedding JavaScript trackers in your frontend, requests are tracked directly from your Laravel app to your Matomo instance, ensuring better privacy, ad-blocker resilience, and simplified integration.

## Installation

Install the package via Composer:

```bash
composer require mattitjaab/matomo-proxy-laravel
```

Publish the config file:

```bash
php artisan vendor:publish --tag="matomo-proxy-laravel-config"
```

This will create a `config/matomo-proxy-laravel.php` file. Example:

```php
return [
    'enabled' => env('MATOMO_ENABLED', true),

    // Full Matomo tracking endpoint (must include /matomo.php)
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
```

## Usage

Register the middleware globally in your `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->append(\MattitjaAB\MatomoProxyLaravel\Middleware\MatomoProxyLaravelMiddleware::class);
})
```

2. Configure your `.env` file:

```dotenv
MATOMO_ENABLED=true
MATOMO_BASE_URL=https://analytics.example.com/matomo.php
MATOMO_SITE_ID=1
MATOMO_TOKEN=null
MATOMO_TIMEOUT=2
MATOMO_RETRY_TIMES=0
MATOMO_RETRY_SLEEP_MS=100
```

✅ That’s it. All requests will now be tracked server-side in Matomo without requiring any frontend JS snippet.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

* [MattitjaAB](https://github.com/MattitjaAB)
* [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
