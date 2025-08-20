<?php

namespace MattitjaAB\MatomoProxyLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MattitjaAB\MatomoProxyLaravel\MatomoProxyLaravel
 */
class MatomoProxyLaravel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MattitjaAB\MatomoProxyLaravel\MatomoProxyLaravel::class;
    }
}
