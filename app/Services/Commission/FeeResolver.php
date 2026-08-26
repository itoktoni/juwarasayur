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
            $pct = $product->reseller_fee_percent !== null ? (float) $product->reseller_fee_percent : 0;
            $pct = max(0, min(100, $pct));
            $hargaEfektif = $harga * (1 - $pct / 100);

            return new FeeResult($pct, 0, $product->reseller_fee_percent !== null ? 'product' : null, 'reseller', $hargaEfektif);
        }

        return new FeeResult(0, 0, null, $role, $harga);
    }
}
