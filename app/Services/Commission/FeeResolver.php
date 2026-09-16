<?php

namespace App\Services\Commission;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Modules\Catalog\Models\Product;

class FeeResolver
{
    public function resolve(Product $product, ?User $user, int $qty, float $harga): FeeResult
    {
        $role = $user?->type;

        if ($role === UserTypeEnum::AFFILIATOR) {
            if ($product->affiliator_fee_percent !== null) {
                $pct = (float) $product->affiliator_fee_percent;
                $src = 'product';
            } elseif ($user?->fee !== null) {
                $pct = (float) $user->fee;
                $src = 'user';
            } else {
                $pct = (float) config('commission.rate', 2);
                $src = 'config';
            }
            $pct = max(0, min(100, $pct));
            $amount = $harga * $qty * $pct / 100;

            return new FeeResult($pct, $amount, $src, 'affiliator', $harga);
        }

        if ($role === UserTypeEnum::RESELLER) {
            // Reseller (grosir) pakai harga grosir langsung dari CSV,
            // bukan harga jual x persentase. Fallback ke harga jual jika grosir kosong.
            $grosir = (float) ($product->product_harga_grosir ?? 0);
            $hargaEfektif = $grosir > 0 ? $grosir : $harga;

            return new FeeResult(0, 0, $grosir > 0 ? 'grosir' : null, 'reseller', $hargaEfektif);
        }

        return new FeeResult(0, 0, null, $role, $harga);
    }
}
