<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Halaman admin (/admin/*) hanya untuk role internal (admin/developer/editor).
     * User bertipe customer, reseller, atau affiliator dialihkan ke home publik
     * karena area admin adalah back-office (user bertipe user dengan role internal).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ($user->isCustomer() || $user->isReseller() || $user->isAffiliator())) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
