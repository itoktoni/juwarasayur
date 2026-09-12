<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CaptureAffiliateRef
{
    public const COOKIE_NAME = 'aff_ref';

    public const COOKIE_MINUTES = 60 * 24 * 30; // 30 hari

    public const SESSION_KEY = 'aff_ref';

    public function handle(Request $request, Closure $next): Response
    {
        $code = $this->resolveCode($request);

        if ($code !== null) {
            $affiliator = User::where('referral_code', $code)->first(['id', 'referral_code']);

            if ($affiliator) {
                // Simpan ke session + cookie (encrypted otomatis via EncryptCookies)
                session([self::SESSION_KEY => $code]);
                Cookie::queue(self::COOKIE_NAME, encrypt($code), self::COOKIE_MINUTES);

                // Increment tracking — tahan fail supaya tidak block request
                try {
                    DB::table('referral_hits')->insert([
                        'referral_code' => $code,
                        'affiliator_id' => $affiliator->id,
                        'ip' => $request->ip(),
                        'user_agent' => substr((string) $request->userAgent(), 0, 500),
                        'landing_url' => substr($request->fullUrl(), 0, 1000),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // tabel belum migrate di env lama → silent
                }
            }
        }

        return $next($request);
    }

    private function resolveCode(Request $request): ?string
    {
        // Prioritas: /r/{code} > ?ref=CODE > ?referral=CODE
        $code = $request->route('code');
        if (! empty($code)) {
            return strtoupper(trim((string) $code));
        }

        foreach (['ref', 'referral', 'r'] as $key) {
            $val = $request->query($key);
            if (! empty($val)) {
                return strtoupper(trim((string) $val));
            }
        }

        return null;
    }

    /**
     * Resolve affiliator id dari cookie/session saat register/checkout.
     */
    public static function resolvedAffiliatorId(Request $request): ?int
    {
        $code = null;

        // Cookie terenkripsi
        try {
            $cookie = $request->cookie(self::COOKIE_NAME);
            if (! empty($cookie)) {
                $code = decrypt($cookie);
            }
        } catch (\Throwable $e) {
        }

        if (empty($code)) {
            $code = session(self::SESSION_KEY);
        }

        // Fallback: query param di request yang sama (register POST dengan ?ref=CODE)
        if (empty($code)) {
            $code = $request->query('ref') ?? $request->input('ref');
        }

        if (empty($code)) {
            return null;
        }

        $code = strtoupper(trim((string) $code));

        return User::where('referral_code', $code)->value('id');
    }
}
