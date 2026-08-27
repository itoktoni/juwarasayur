<?php

namespace App\Http\Responses;

use App\Enums\RoleEnum;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Redirect setelah login:
     * - Affiliator → dashboard (/account/dashboard)
     * - Reseller   → home publik (/)
     * - Customer   → home publik (/)
     * - Role tidak dikenal di RoleEnum → home publik (/)
     * - Role internal (admin/developer/editor/user) → admin (/admin/dashboard)
     *
     * Jika email belum terverifikasi, route tujuan yang ber-middleware
     * 'verified' otomatis mengarahkan ke halaman verifikasi.
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user && $user->isAffiliator()) {
            return redirect()->intended(route('account.dashboard'));
        }

        if ($user && ($user->isReseller() || $user->isCustomer())) {
            return redirect()->intended(route('home'));
        }

        // Role di luar whitelist RoleEnum (atau null/kosong) → home publik.
        if ($user && ! RoleEnum::hasValue((string) $user->role)) {
            return redirect()->intended(route('home'));
        }

        return redirect()->intended(config('fortify.home'));
    }
}
