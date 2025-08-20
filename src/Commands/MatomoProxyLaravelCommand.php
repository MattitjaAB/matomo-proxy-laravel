<?php

namespace MattitjaAB\MatomoProxyLaravel\Commands;

use Illuminate\Console\Command;

class MatomoProxyLaravelCommand extends Command
{
    public $signature = 'matomo-proxy-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
