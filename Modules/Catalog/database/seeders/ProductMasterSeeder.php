<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Catalog\Models\ProductMaster;
use Modules\Po\Models\Supplier;

class ProductMasterSeeder extends Seeder
{
    /**
     * Master ↔ supplier rekomendasi (kode supplier dari PoDatabaseSeeder).
     * Urutan array = prioritas; index pertama menjadi recommended.
     */
    public static array $supplierMap = [
        'wortel' => ['SUP-BRT-01', 'SUP-DIG-01'],
        'brokoli' => ['SUP-BRT-01'],
        'kentang' => ['SUP-DIG-01', 'SUP-BRT-01'],
        'pakcoy' => ['SUP-BTU-01', 'SUP-LBG-01'],
        'selada' => ['SUP-BTU-01'],
        'tomat' => ['SUP-LBG-01', 'SUP-DIG-01'],
        'bayam' => ['SUP-LBG-01'],
        'kangkung' => ['SUP-LBG-01'],
        'sawi' => ['SUP-LBG-01'],
        'timun' => ['SUP-LBG-01'],
        'jamur-tiram' => ['SUP-LBG-01'],
        'paket-sayur' => ['SUP-LBG-01'],
        'cabai' => ['SUP-BMB-01'],
        'bawang-merah' => ['SUP-BMB-01', 'SUP-LBG-01'],
        'bawang-putih' => ['SUP-BMB-01'],
        'jahe' => ['SUP-BMB-01'],
    ];

    public function run(): Collection
    {
        $data = [
            ['product_master_nama' => 'Wortel', 'slug' => 'wortel', 'deskripsi' => 'Wortel segar dataran tinggi — Berastagi & Dieng'],
            ['product_master_nama' => 'Bayam Hijau', 'slug' => 'bayam', 'deskripsi' => 'Bayam hijau hijau segar petik pagi'],
            ['product_master_nama' => 'Kangkung', 'slug' => 'kangkung', 'deskripsi' => 'Kangkung darat & air segar'],
            ['product_master_nama' => 'Sawi Hijau', 'slug' => 'sawi', 'deskripsi' => 'Sawi hijau / caisim segar'],
            ['product_master_nama' => 'Pakcoy', 'slug' => 'pakcoy', 'deskripsi' => 'Pakcoy hidroponik & konvensional'],
            ['product_master_nama' => 'Selada Keriting', 'slug' => 'selada', 'deskripsi' => 'Selada keriting hidroponik'],
            ['product_master_nama' => 'Tomat Merah', 'slug' => 'tomat', 'deskripsi' => 'Tomat merah masak pohon'],
            ['product_master_nama' => 'Cabai', 'slug' => 'cabai', 'deskripsi' => 'Cabai rawit & cabai merah keriting'],
            ['product_master_nama' => 'Bawang Merah', 'slug' => 'bawang-merah', 'deskripsi' => 'Bawang merah Brebes kering'],
            ['product_master_nama' => 'Bawang Putih', 'slug' => 'bawang-putih', 'deskripsi' => 'Bawang putih kating single garlic'],
            ['product_master_nama' => 'Kentang', 'slug' => 'kentang', 'deskripsi' => 'Kentang Dieng granola'],
            ['product_master_nama' => 'Brokoli', 'slug' => 'brokoli', 'deskripsi' => 'Brokoli hijau segar organik'],
            ['product_master_nama' => 'Jamur Tiram', 'slug' => 'jamur-tiram', 'deskripsi' => 'Jamur tiram segar panen harian'],
            ['product_master_nama' => 'Jahe Gajah', 'slug' => 'jahe', 'deskripsi' => 'Jahe gajah besar & pedas'],
            ['product_master_nama' => 'Timun', 'slug' => 'timun', 'deskripsi' => 'Timun surya & japanese segar'],
            ['product_master_nama' => 'Paket Sayur', 'slug' => 'paket-sayur', 'deskripsi' => 'Paket sayur kombinasi siap masak'],
        ];

        foreach ($data as $i => $row) {
            ProductMaster::updateOrCreate(
                ['product_master_slug' => $row['slug']],
                [
                    'product_master_nama' => $row['product_master_nama'],
                    'product_master_deskripsi' => $row['deskripsi'],
                    'is_active' => true,
                    'sort_order' => $i,
                ]
            );
        }

        $this->attachSuppliers();

        return ProductMaster::whereIn('product_master_slug', collect($data)->pluck('slug'))->get()->keyBy('product_master_slug');
    }

    /**
     * Sync pivot master↔supplier + tandai satu rekomendasi.
     * Idempotent — dipanggil ulang oleh PoDatabaseSeeder setelah supplier dibuat.
     */
    public function attachSuppliers(?Collection $suppliers = null): void
    {
        if (! Schema::hasTable('po_suppliers')) {
            return;
        }

        $suppliers ??= Supplier::whereIn('supplier_kode', collect(self::$supplierMap)->flatten()->unique())->get()->keyBy('supplier_kode');

        if ($suppliers->isEmpty()) {
            return;
        }

        foreach (self::$supplierMap as $masterSlug => $supplierCodes) {
            $master = ProductMaster::where('product_master_slug', $masterSlug)->first();
            if (! $master) {
                continue;
            }

            $sync = [];
            foreach ($supplierCodes as $i => $kode) {
                $supplier = $suppliers[$kode] ?? null;
                if ($supplier) {
                    $sync[$supplier->id] = ['is_recommended' => $i === 0];
                }
            }

            if (! empty($sync)) {
                $master->has_suppliers()->sync($sync);
            }
        }
    }
}
