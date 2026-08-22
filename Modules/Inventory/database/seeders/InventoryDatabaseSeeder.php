<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\Gudang;
use Modules\Inventory\Models\Lokasi;

class InventoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $gudangPusat = Gudang::updateOrCreate(
            ['gudang_kode' => 'GDG-PST-01'],
            ['gudang_nama' => 'Gudang Pusat', 'gudang_alamat' => 'Jl. Raya Pusat No. 1', 'is_active' => true, 'sort_order' => 0]
        );

        $gudangCabang = Gudang::updateOrCreate(
            ['gudang_kode' => 'GDG-CBG-01'],
            ['gudang_nama' => 'Gudang Cabang', 'gudang_alamat' => 'Jl. Cabang No. 2', 'is_active' => true, 'sort_order' => 1]
        );

        $lokasi = [
            ['lokasi_kode' => 'RAK-A', 'lokasi_nama' => 'Rak A', 'gudang' => $gudangPusat],
            ['lokasi_kode' => 'RAK-B', 'lokasi_nama' => 'Rak B', 'gudang' => $gudangPusat],
            ['lokasi_kode' => 'RAK-C', 'lokasi_nama' => 'Rak C', 'gudang' => $gudangCabang],
        ];

        foreach ($lokasi as $i => $row) {
            Lokasi::updateOrCreate(
                ['lokasi_kode' => $row['lokasi_kode']],
                ['lokasi_nama' => $row['lokasi_nama'], 'lokasi_id_gudang' => $row['gudang']->id, 'is_active' => true, 'sort_order' => $i]
            );
        }
    }
}
