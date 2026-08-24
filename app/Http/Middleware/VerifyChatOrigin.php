<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Proteksi ringan endpoint AJAX chat tanpa session (menghindari session lock
 * saat permintaan AI berjalan lama). POST diverifikasi asal requestnya.
 */
class VerifyChatOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            return $next($request);
        }

        $origin = $request->header('Origin');

        // Non-browser client (tanpa Origin) boleh lanjut; browser selalu mengirim Origin pada POST.
        if ($origin !== null && parse_url($origin, PHP_URL_HOST) !== $request->getHost()) {
            abort(403, 'Origin tidak diizinkan.');
        }

        return $next($request);
    }
}
