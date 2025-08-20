<?php

use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use MattitjaAB\MatomoProxyLaravel\Middleware\MatomoProxyLaravelMiddleware;

it('sends a tracking hit and includes user id when authenticated', function () {
    config()->set('matomo-proxy-laravel.enabled', true);
    config()->set('matomo-proxy-laravel.base_url', 'https://matomo.example/matomo.php');
    config()->set('matomo-proxy-laravel.site_id', '1');
    config()->set('matomo-proxy-laravel.token', 'secret-token');

    Http::preventStrayRequests();
    Http::fake([
        'https://matomo.example/matomo.php' => Http::response('OK', 200),
    ]);

    $user = new class implements \Illuminate\Contracts\Auth\Authenticatable
    {
        public int $id = 123;

        public function getAuthIdentifierName()
        {
            return 'id';
        }

        public function getAuthIdentifier()
        {
            return $this->id;
        }

        public function getAuthPasswordName()
        {
            return 'password';
        }

        public function getAuthPassword()
        {
            return '';
        }

        public function getRememberToken()
        {
            return null;
        }

        public function setRememberToken($value): void {}

        public function getRememberTokenName()
        {
            return '';
        }
    };

    Route::get('/page', fn () => response('ok', 200, ['Content-Type' => 'text/html']))
        ->name('test.route');

    $this->be($user);

    $this->get('/page', [
        'Accept-Language' => 'sv-SE',
        'User-Agent' => 'PHPUnit',
        'Referer' => 'https://ref.example',
    ])->assertOk();

    Http::assertSent(function (HttpClientRequest $request) {
        $params = $request->data();

        return $request->url() === 'https://matomo.example/matomo.php'
            && $request->method() === 'POST'
            && ($params['idsite'] ?? null) === '1'
            && ($params['uid'] ?? null) === '123'
            && ($params['token_auth'] ?? null) === 'secret-token'
            && ($params['action_name'] ?? null) === 'test.route'
            && ($params['urlref'] ?? null) === 'https://ref.example'
            && ($params['lang'] ?? null) === 'sv-SE'
            && ($params['ua'] ?? null) === 'PHPUnit'
            && ($params['rec'] ?? null) === 1
            && ($params['apiv'] ?? null) === 1;
    });

    Http::assertSentCount(1);
});

it('does nothing when disabled', function () {
    config()->set('matomo-proxy-laravel.enabled', false);
    config()->set('matomo-proxy-laravel.base_url', 'https://matomo.example/matomo.php');
    config()->set('matomo-proxy-laravel.site_id', '1');

    Http::fake();

    Route::get('/disabled', fn () => 'ok')
        ->middleware(MatomoProxyLaravelMiddleware::class);

    $this->get('/disabled')->assertOk();

    Http::assertNothingSent();
});

it('never breaks the response when exceptions occur during tracking', function () {
    config()->set('matomo-proxy-laravel.enabled', true);
    config()->set('matomo-proxy-laravel.base_url', 'https://matomo.example/matomo.php');
    config()->set('matomo-proxy-laravel.site_id', '1');

    Http::fake(function () {
        throw new Exception('network error');
    });

    Route::get('/exception', fn () => 'ok')
        ->middleware(MatomoProxyLaravelMiddleware::class);

    $this->get('/exception')->assertOk();
});

it('does not send tracking for non-get requests', function () {
    config()->set('matomo-proxy-laravel.enabled', true);
    config()->set('matomo-proxy-laravel.base_url', 'https://matomo.example/matomo.php');
    config()->set('matomo-proxy-laravel.site_id', '1');

    Http::fake();

    Route::post('/post', fn () => 'ok')
        ->middleware(MatomoProxyLaravelMiddleware::class);

    $this->post('/post')->assertOk();

    Http::assertNothingSent();
});

it('does not send tracking for ignored paths', function () {
    config()->set('matomo-proxy-laravel.enabled', true);
    config()->set('matomo-proxy-laravel.base_url', 'https://matomo.example/matomo.php');
    config()->set('matomo-proxy-laravel.site_id', '1');
    config()->set('matomo-proxy-laravel.ignore', ['ignored*']);

    Http::fake();

    Route::get('/ignored/page', fn () => 'ok')
        ->middleware(MatomoProxyLaravelMiddleware::class);

    $this->get('/ignored/page')->assertOk();

    Http::assertNothingSent();
});

it('does not send tracking for non-html responses', function () {
    config()->set('matomo-proxy-laravel.enabled', true);
    config()->set('matomo-proxy-laravel.base_url', 'https://matomo.example/matomo.php');
    config()->set('matomo-proxy-laravel.site_id', '1');

    Http::fake();

    Route::get('/json', fn () => response()->json(['ok' => true]))
        ->middleware(MatomoProxyLaravelMiddleware::class);

    $this->get('/json')->assertOk();

    Http::assertNothingSent();
});

it('does not send tracking for 404 responses', function () {
    config()->set('matomo-proxy-laravel.enabled', true);
    config()->set('matomo-proxy-laravel.base_url', 'https://matomo.example/matomo.php');
    config()->set('matomo-proxy-laravel.site_id', '1');

    Http::fake();

    $this->get('/nonexistent')->assertNotFound();

    Http::assertNothingSent();
});
