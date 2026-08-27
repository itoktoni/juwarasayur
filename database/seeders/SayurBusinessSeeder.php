<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Withdrawal;
use App\Services\Commission\FeeResolver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Models\Product;
use Modules\Ecommerce\Models\CodLocation;
use Modules\Inventory\Models\Lokasi;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;

/**
 * Data operasional bisnis sayur: stok gudang, titik COD,
 * sample Sales Order (dengan fee snapshot) dan withdrawal demo
 * agar dashboard affiliator terisi.
 */
class SayurBusinessSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedStocks();
        $this->seedCodLocations();
        $this->seedSampleOrders();
        $this->seedSampleWithdrawals();
    }

    private function seedStocks(): void
    {
        $rakA = Lokasi::where('lokasi_kode', 'RAK-A')->first();
        $rakB = Lokasi::where('lokasi_kode', 'RAK-B')->first();

        if (! $rakA || ! $rakB) {
            return; // jalankan InventoryDatabaseSeeder dulu
        }

        $products = Product::where('product_status', 'active')->get();
        $i = 0;

        foreach ($products as $product) {
            // Paket disimpan di Rak B, bahan mentah di Rak A
            $isPaket = str_contains($product->product_slug, 'paket-');
            $lokasiId = $isPaket ? $rakB->id : $rakA->id;

            DB::table('inv_stocks')->updateOrInsert(
                ['stock_code' => 'STK-'.str_pad((string) ($product->id), 4, '0', STR_PAD_LEFT)],
                [
                    'stock_id_product' => $product->id,
                    'stock_id_lokasi' => $lokasiId,
                    'stock_qty' => $product->product_stok ?? 50,
                    'stock_batch' => 'BATCH-'.now()->format('Ym'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $i++;
        }

        if (app()->environment('local', 'testing')) {
            $this->command?->info("  Stocks seeded: {$i} products.");
        }
    }

    private function seedCodLocations(): void
    {
        $data = [
            ['location_name' => 'Berbah', 'address' => 'Alun-alun Berbah, Sleman', 'lat' => -7.8396, 'lng' => 110.4003, 'fee' => 5000],
            ['location_name' => 'Rejoso', 'address' => 'Pasar Rejoso, Pasuruan', 'lat' => -7.6842, 'lng' => 112.9286, 'fee' => 8000],
            ['location_name' => 'Pasuruan Kota', 'address' => 'Jl. Raya Pasuruan', 'lat' => -7.6453, 'lng' => 112.9077, 'fee' => null],
        ];

        foreach ($data as $row) {
            CodLocation::updateOrCreate(
                ['location_name' => $row['location_name']],
                array_merge($row, ['is_active' => true])
            );
        }
    }

    /**
     * Sample Sales Order + fee snapshot untuk dashboard affiliator.
     * - 4 SO untuk affiliator (customer bawahan) → komisi & order terbaru
     * - 1 SO reseller (untuk diri sendiri, harga diskon)
     * - 1 SO customer publik (referensi_id=null)
     * - Status bervariasi: pending/paid/confirmed/delivered
     * - Tanggal tersebar 7 hari terakhir (chart tren dashboard)
     */
    private function seedSampleOrders(): void
    {
        if (So::count() > 0) {
            return;
        }

        $affiliator = User::where('email', 'affiliator@sayur.test')->first();
        $affiliator2 = User::where('email', 'affiliator2@sayur.test')->first();
        $reseller = User::where('email', 'reseller@sayur.test')->first();
        $customer1 = User::where('email', 'customer1@sayur.test')->first();
        $customer2 = User::where('email', 'customer2@sayur.test')->first();
        $customer3 = User::where('email', 'customer3@sayur.test')->first();
        $budi = User::where('email', 'budi@sayur.test')->first();

        $bayam = Product::where('product_slug', 'bayam-hijau-ikat')->first();
        $kangkung = Product::where('product_slug', 'kangkung-cabut-ikat')->first();
        $paketSop = Product::where('product_slug', 'paket-sayur-sop-lengkap')->first();
        $paketAsem = Product::where('product_slug', 'paket-sayur-asem-komplit')->first();
        $wortel = Product::where('product_slug', 'wortel-berastagi-1kg')->first();
        $kentang = Product::where('product_slug', 'kentang-dieng-1kg')->first();
        $bawang = Product::where('product_slug', 'bawang-merah-brebes-1kg')->first();
        $cabai = Product::where('product_slug', 'cabai-rawit-merah-250g')->first();
        $tomat = Product::where('product_slug', 'tomat-merah-1kg')->first();
        $brokoli = Product::where('product_slug', 'brokoli-segar-500g')->first();
        $selada = Product::where('product_slug', 'selada-keriting-hidroponik')->first();

        // Tiap entri: reseller (attributor) | customer | items[][productId, qty] | status | daysAgo | name | phone
        $orders = [
            // -- Order untuk affiliator (komisi muncul) --
            ['aff' => $affiliator, 'cust' => $customer1, 'items' => [[$paketSop, 5], [$bayam, 12], [$wortel, 6], [$kentang, 5]], 'status' => SoStatusEnum::DELIVERED, 'days' => 6, 'name' => 'Ibu Wati', 'phone' => '081600000001'],
            ['aff' => $affiliator, 'cust' => $customer2, 'items' => [[$paketAsem, 6], [$cabai, 4], [$bawang, 3], [$tomat, 5]], 'status' => SoStatusEnum::DELIVERED, 'days' => 5, 'name' => 'Pak Darmo', 'phone' => '081600000002'],
            ['aff' => $affiliator, 'cust' => $customer1, 'items' => [[$bayam, 8], [$kangkung, 8], [$tomat, 4], [$wortel, 3]], 'status' => SoStatusEnum::DELIVERED, 'days' => 4, 'name' => 'Ibu Wati', 'phone' => '081600000001'],
            ['aff' => $affiliator, 'cust' => $customer2, 'items' => [[$paketSop, 4], [$paketAsem, 4], [$bawang, 2]], 'status' => SoStatusEnum::CONFIRMED, 'days' => 3, 'name' => 'Pak Darmo', 'phone' => '081600000002'],
            ['aff' => $affiliator, 'cust' => $customer1, 'items' => [[$bayam, 6], [$kangkung, 6], [$tomat, 3], [$cabai, 2]], 'status' => SoStatusEnum::PAID, 'days' => 2, 'name' => 'Ibu Wati', 'phone' => '081600000001'],
            ['aff' => $affiliator, 'cust' => $customer2, 'items' => [[$kentang, 5], [$wortel, 4], [$bawang, 2], [$tomat, 3]], 'status' => SoStatusEnum::PENDING, 'days' => 0, 'name' => 'Pak Darmo', 'phone' => '081600000002'],
            // -- Order affiliator kedua --
            ['aff' => $affiliator2, 'cust' => $customer3, 'items' => [[$brokoli, 4], [$selada, 3], [$paketSop, 2]], 'status' => SoStatusEnum::DELIVERED, 'days' => 5, 'name' => 'Ibu Lestari', 'phone' => '081600000003'],
            // -- Order reseller (order untuk dirinya sendiri, harga diskon) --
            ['aff' => null, 'reseller_self' => $reseller, 'items' => [[$bayam, 8], [$kentang, 4], [$wortel, 3]], 'status' => SoStatusEnum::CONFIRMED, 'days' => 3, 'name' => 'Pak Hari', 'phone' => '081300000001'],
            // -- Order customer publik --
            ['aff' => null, 'cust' => $budi, 'items' => [[$paketSop, 2], [$bayam, 5], [$kentang, 2]], 'status' => SoStatusEnum::PENDING, 'days' => 0, 'name' => 'Budi Santoso', 'phone' => '081400000001'],
        ];

        $feeResolver = app(FeeResolver::class);

        foreach ($orders as $row) {
            $seller = $row['aff'] ?? $row['reseller_self'] ?? null;
            if (! $seller) {
                continue;
            }

            $so = So::create([
                'so_tanggal' => now()->subDays((int) $row['days']),
                'so_id_reseller' => $seller->id,
                'so_id_customer' => ($row['cust'] ?? null)?->id,
                'so_customer_name' => $row['name'],
                'so_customer_phone' => $row['phone'],
                'so_status' => $row['status'],
                'so_shipping_method' => ShippingMethodEnum::PICKUP,
                'so_subtotal' => 0,
                'so_grand_total' => 0,
            ]);

            $subtotal = 0;
            $seq = 1;

            foreach ($row['items'] as [$product, $qty]) {
                if (! $product) {
                    continue;
                }

                $harga = (float) $product->product_harga;

                // Fee snapshot via FeeResolver (sama dengan alur checkout).
                $fee = $feeResolver->resolve($product, $seller, (int) $qty, $harga);
                $hargaEfektif = (float) $fee->hargaEfektif;
                $feePercent = $seller->isAffiliator() ? (float) $fee->percent : null;
                $feeAmount = $seller->isAffiliator() ? (float) $fee->amount : 0;
                $feeSource = $fee->source;
                $appliedRole = $fee->role;

                SoDetail::create([
                    'so_detail_code' => sprintf('%s-%03d', $so->so_code, $seq),
                    'so_detail_id_so' => $so->id,
                    'so_detail_id_product' => $product->id,
                    'so_detail_qty' => (int) $qty,
                    'so_detail_harga' => $hargaEfektif,
                    'fee_percent' => $feePercent,
                    'fee_amount' => $feeAmount,
                    'fee_source' => $feeSource,
                    'applied_role' => $appliedRole,
                ]);

                $subtotal += $qty * $hargaEfektif;
                $seq++;
            }

            $so->update(['so_subtotal' => $subtotal, 'so_grand_total' => $subtotal]);
        }
    }

    /**
     * Sample withdrawal demo untuk affiliator utama.
     * 1 pending (Rp 50.000) + 1 paid (Rp 100.000) — agar commissionBalance > 0
     * dan tab Riwayat Withdraw tidak kosong.
     */
    private function seedSampleWithdrawals(): void
    {
        if (Withdrawal::count() > 0) {
            return;
        }

        $affiliator = User::where('email', 'affiliator@sayur.test')->first();
        if (! $affiliator) {
            return;
        }

        Withdrawal::create([
            'user_id' => $affiliator->id,
            'amount' => 25000,
            'bank_name' => $affiliator->bank_name,
            'bank_account_name' => $affiliator->bank_account_name,
            'bank_account_no' => $affiliator->bank_account_no,
            'status' => Withdrawal::STATUS_PAID,
            'note' => 'Sudah ditransfer via BCA',
            'processed_at' => now()->subDays(10),
            'created_at' => now()->subDays(12),
        ]);
    }
}
