<?php

namespace Modules\So\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\So\Models\SoDiscount;

class SoDiscountSeeder extends Seeder
{
    /**
     * Master kode diskon checkout.
     * Idempotent: aman dijalankan berulang (updateOrCreate by discount_code).
     */
    public function run(): void
    {
        $discounts = [
            [
                'discount_code' => 'WELCOME10',
                'discount_nama' => 'Promo Pelanggan Baru 10%',
                'discount_type' => SoDiscount::TYPE_PERCENT,
                'discount_value' => 10,
                'discount_min_purchase' => 0,
                'is_active' => true,
            ],
            [
                'discount_code' => 'SAYUR15',
                'discount_nama' => 'Diskon Sayur Segar 15% (min. 50rb)',
                'discount_type' => SoDiscount::TYPE_PERCENT,
                'discount_value' => 15,
                'discount_min_purchase' => 50000,
                'is_active' => true,
            ],
            [
                'discount_code' => 'HEMAT25K',
                'discount_nama' => 'Potongan Rp 25.000 (min. 150rb)',
                'discount_type' => SoDiscount::TYPE_NOMINAL,
                'discount_value' => 25000,
                'discount_min_purchase' => 150000,
                'is_active' => true,
            ],
            [
                'discount_code' => 'NEWBIE5K',
                'discount_nama' => 'Cashback Rp 5.000 pembelian pertama',
                'discount_type' => SoDiscount::TYPE_NOMINAL,
                'discount_value' => 5000,
                'discount_min_purchase' => 30000,
                'is_active' => true,
            ],
            // Contoh kode non-aktif → ditolak saat redeem ("tidak ditemukan atau tidak aktif")
            [
                'discount_code' => 'EXPIRED50',
                'discount_nama' => 'Promo lama sudah berakhir',
                'discount_type' => SoDiscount::TYPE_PERCENT,
                'discount_value' => 50,
                'discount_min_purchase' => 0,
                'is_active' => false,
            ],
        ];

        foreach ($discounts as $discount) {
            SoDiscount::updateOrCreate(
                ['discount_code' => $discount['discount_code']],
                $discount
            );
        }

        $this->command->info('SoDiscountSeeder selesai: '.count($discounts).' kode diskon disiapkan.');
    }
}
