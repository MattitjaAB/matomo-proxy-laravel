<?php

namespace MattitjaAB\MatomoProxyLaravel;

use MattitjaAB\MatomoProxyLaravel\Commands\MatomoProxyLaravelCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MatomoProxyLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('matomo-proxy-laravel')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_matomo_proxy_laravel_table')
            ->hasCommand(MatomoProxyLaravelCommand::class);
    }
}
