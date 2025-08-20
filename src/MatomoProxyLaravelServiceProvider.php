<?php

namespace MattitjaAB\MatomoProxyLaravel;

use Illuminate\Contracts\Http\Kernel as HttpKernel;
use MattitjaAB\MatomoProxyLaravel\Middleware\MatomoProxyLaravelMiddleware;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class MatomoProxyLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('matomo-proxy-laravel')
            ->hasConfigFile('matomo-proxy-laravel');
    }

    public function packageBooted(): void
    {
        $this->app->make(HttpKernel::class)
            ->pushMiddleware(MatomoProxyLaravelMiddleware::class);
    }
}
