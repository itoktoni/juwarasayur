<?php

namespace Database\Seeders;

use App\Models\User;
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
 * dan sample Sales Order untuk uji produksi.
 */
class SayurBusinessSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedStocks();
        $this->seedCodLocations();
        $this->seedSampleOrders();
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
     * Beberapa SO sample agar modul Produksi-dari-SO bisa langsung diuji.
     */
    private function seedSampleOrders(): void
    {
        if (So::count() > 0) {
            return;
        }

        $customer = User::where('type', 'customer')->first();
        $reseller = User::where('type', 'reseller')->first();

        $paketSop = Product::where('product_slug', 'paket-sayur-sop-lengkap')->first();
        $paketAsem = Product::where('product_slug', 'paket-sayur-asem-komplit')->first();
        $bayam = Product::where('product_slug', 'bayam-hijau-ikat')->first();
        $kentang = Product::where('product_slug', 'kentang-dieng-1kg')->first();
        $wortel = Product::where('product_slug', 'wortel-berastagi-1kg')->first();

        $orders = [
            [
                'customer_name' => 'Warung Bu Sari',
                'customer_phone' => '081300000001',
                'items' => [[$bayam?->id, 6], [$kentang?->id, 4], [$wortel?->id, 5]],
            ],
            [
                'customer_name' => 'Budi Santoso',
                'customer_phone' => '081400000001',
                'items' => [[$paketSop?->id, 2], [$paketAsem?->id, 3]],
            ],
            [
                'customer_name' => 'Siti Aminah',
                'customer_phone' => '081400000002',
                'items' => [[$bayam?->id, 4], [$paketSop?->id, 1]],
            ],
        ];

        foreach ($orders as $i => $order) {
            $so = So::create([
                'so_tanggal' => now()->subDays(2 - $i),
                'so_id_reseller' => $reseller?->id,
                'so_id_customer' => $customer?->id,
                'so_customer_name' => $order['customer_name'],
                'so_customer_phone' => $order['customer_phone'],
                'so_status' => SoStatusEnum::CONFIRMED,
                'so_shipping_method' => ShippingMethodEnum::PICKUP,
                'so_subtotal' => 0,
                'so_grand_total' => 0,
            ]);

            $subtotal = 0;
            $seq = 1;

            foreach ($order['items'] as [$productId, $qty]) {
                if (! $productId) {
                    continue;
                }

                $harga = (float) (Product::find($productId)?->product_harga ?? 0);

                SoDetail::create([
                    'so_detail_code' => sprintf('%s-%03d', $so->so_code, $seq),
                    'so_detail_id_so' => $so->id,
                    'so_detail_id_product' => $productId,
                    'so_detail_qty' => $qty,
                    'so_detail_harga' => $harga,
                ]);

                $subtotal += $qty * $harga;
                $seq++;
            }

            $so->update(['so_subtotal' => $subtotal, 'so_grand_total' => $subtotal]);
        }
    }
}
