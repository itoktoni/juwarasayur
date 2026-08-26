<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect setelah login:
     * - Reseller  → dashboard reseller (/account/dashboard)
     * - Customer  → home publik (/)
     * - Selainnya → admin (/admin/dashboard)
     *
     * Jika email belum terverifikasi, route tujuan yang ber-middleware
     * 'verified' otomatis mengarahkan ke halaman verifikasi.
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user && $user->isReseller()) {
            return redirect()->intended(route('account.dashboard'));
        }

        if ($user && $user->isCustomer()) {
            return redirect()->intended(route('home'));
        }

        return redirect()->intended(config('fortify.home'));
    }
}
