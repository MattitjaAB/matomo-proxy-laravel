<?php

declare(strict_types=1);

namespace MattitjaAB\MatomoProxyLaravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

final class MatomoProxyLaravelMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Only track GET requests
        if (! $request->isMethod('GET')) {
            return $response;
        }

        // Global feature toggle + required config
        if (! $this->isEnabled()) {
            return $response;
        }

        // Only track HTML responses
        if (! $this->isHtmlResponse($response)) {
            return $response;
        }

        // Respect ignore patterns
        if ($this->isIgnoredPath($request)) {
            return $response;
        }

        // Skip non-success/redirect responses (e.g., 404, 5xx)
        if (! $this->isTrackableStatus($response)) {
            return $response;
        }

        // Ensure endpoint is present
        $endpoint = $this->buildEndpoint();
        if ($endpoint === null) {
            return $response;
        }

        // Build payload now; do not capture Request in any deferred closure
        $params = $this->buildParams($request);

        // Send synchronously (you can swap to app()->terminating() if desired)
        $this->send($endpoint, $params);

        return $response;
    }

    /**
     * Enabled flag and required keys.
     */
    private function isEnabled(): bool
    {
        return config('matomo-proxy-laravel.enabled') === true
            && filled(config('matomo-proxy-laravel.base_url'))
            && filled(config('matomo-proxy-laravel.site_id'));
    }

    /**
     * Only track HTML (also accept application/xhtml+xml).
     */
    private function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('content-type');

        return $contentType !== null
            && (str_starts_with($contentType, 'text/html')
                || str_starts_with($contentType, 'application/xhtml+xml'));
    }

    /**
     * Respect configurable ignore patterns.
     */
    private function isIgnoredPath(Request $request): bool
    {
        foreach (config('matomo-proxy-laravel.ignore', []) as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Count only successful (2xx) and redirect (3xx) responses.
     */
    private function isTrackableStatus(Response $response): bool
    {
        $code = $response->getStatusCode();

        return $code >= 200 && $code < 400;
    }

    /**
     * Endpoint must already include /matomo.php.
     */
    private function buildEndpoint(): ?string
    {
        $endpoint = config('matomo-proxy-laravel.base_url');

        return filled($endpoint) ? trim((string) $endpoint) : null;
    }

    /**
     * Build Matomo Tracking API payload.
     */
    private function buildParams(Request $request): array
    {
        $params = [
            'idsite' => (string) config('matomo-proxy-laravel.site_id', ''),
            'rec' => 1,
            'apiv' => 1,
            'url' => $request->fullUrl(),
            'action_name' => $request->route()?->getName() ?: $request->path(),
            'ua' => $request->userAgent(),
        ];

        if ($ref = $request->headers->get('referer')) {
            $params['urlref'] = $ref;
        }

        if ($lang = $request->headers->get('accept-language')) {
            $params['lang'] = $lang;
        }

        if ($ip = $request->ip()) {
            $params['cip'] = $ip;
        }

        if ($token = trim((string) config('matomo-proxy-laravel.token', ''))) {
            $params['token_auth'] = $token;
        }

        if ($uid = $request->user()->id ?? null) {
            $params['uid'] = (string) $uid;
        }

        return $params;
    }

    /**
     * Perform the HTTP request (synchronous).
     */
    private function send(string $endpoint, array $params): void
    {
        $timeout = (int) config('matomo-proxy-laravel.timeout', 2);
        $retryTimes = (int) config('matomo-proxy-laravel.retry.times', 0);
        $retrySleep = (int) config('matomo-proxy-laravel.retry.sleep.ms', 100);

        try {
            Http::asForm()
                ->timeout($timeout)
                ->retry($retryTimes, $retrySleep)
                ->post($endpoint, $params);
        } catch (\Throwable) {
            // Never let analytics affect the response lifecycle
        }
    }
}
