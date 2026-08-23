<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Halaman admin (/admin/*) tidak boleh diakses user bertipe
     * customer & reseller — langsung dialihkan ke routing publik.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ($user->isCustomer() || $user->isReseller())) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
