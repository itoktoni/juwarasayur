<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        // Gate verifikasi via env REQUIRE_VERIFIED=true
        if (! env('REQUIRE_VERIFIED', false)) {
            return $next($request);
        }

        if ($request->user() && ! $request->user()->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akun belum terverifikasi. Silakan verifikasi terlebih dahulu.',
                    'needs_verification' => true,
                ], 403);
            }

            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
