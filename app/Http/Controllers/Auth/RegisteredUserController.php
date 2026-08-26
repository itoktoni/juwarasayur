<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Http\Controllers\RegisteredUserController as FortifyRegisteredUserController;

class RegisteredUserController extends FortifyRegisteredUserController
{
    /**
     * Registrasi (register & register/reseller) wajib lolos captcha
     * sama seperti form /contact.
     */
    public function store(Request $request, CreatesNewUsers $creator): RegisterResponse
    {
        $request->validate([
            'captcha' => ['required', 'numeric'],
            'captcha_key' => ['required', 'string'],
        ]);

        $key = (string) $request->input('captcha_key');
        $answer = (int) $request->input('captcha');

        if (! $request->session()->has("captcha_$key") || (int) $request->session()->get("captcha_$key") !== $answer) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'captcha' => 'Captcha salah.',
            ]);
        }

        $request->session()->forget("captcha_$key");

        return parent::store($request, $creator);
    }
}
