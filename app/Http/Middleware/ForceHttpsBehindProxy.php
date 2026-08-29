<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ForceHttpsBehindProxy
{
    public function handle(Request $request, Closure $next): mixed
    {
        // TrustProxies middleware has already run by now, so request()->secure()
        // reflects the X-Forwarded-Proto header when the proxy sends it.
        // Some tunnels (e.g. free pinggy) or local reverse proxies do NOT forward
        // that header, so also force HTTPS when the request is served on the host
        // of an https APP_URL — that host is only reachable over HTTPS anyway.
        $appUrl = trim((string) config('app.url'));
        $appHost = $appUrl !== '' ? parse_url($appUrl, PHP_URL_HOST) : null;
        $appScheme = $appUrl !== '' ? (parse_url($appUrl, PHP_URL_SCHEME) ?? 'http') : 'http';

        $shouldForce = $request->secure()
            || ($appHost !== null && $appScheme === 'https' && $request->getHost() === $appHost);

        if ($shouldForce) {
            // ponytail: only append a non-default port when the request itself was
            // secure (real forwarded port). When we force the scheme via host match,
            // getPort() is the LOCAL port (e.g. 8000) which must never leak into
            // the public URL.
            $root = 'https://' . $request->getHost();
            if ($request->secure()) {
                $port = $request->getPort();
                if ($port !== 80 && $port !== 443) {
                    $root .= ':' . $port;
                }
            }

            URL::forceScheme('https');
            URL::forceRootUrl($root);
        }

        return $next($request);
    }
}
