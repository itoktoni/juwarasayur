<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductMaster;
use Modules\Catalog\Models\Satuan;
use Modules\So\Models\SoDetail;

class VegetableProductSeeder2026 extends Seeder
{
    /**
     * [nama, harga_jual]
     */
    public static array $products = [
        ['KENTANG DIENG ABC 1 KG', 16500],
        ['KENTANG DIENG ABC 500 gr', 8250],
        ['KENTANG DIENG ABC 250 gr', 4675],
        ['KENTANG DIENG ABC 100 gr', 1980],
        ['KENTANG DIENG ABC 50 gr', 1045],
        ['KENTANG DIENG BC 1 kg', 18000],
        ['KENTANG DIENG BC 500 gr', 9000],
        ['KENTANG DIENG BC 250 gr', 5100],
        ['KENTANG DIENG BC 100 gr', 2160],
        ['KENTANG DIENG BC 50 gr', 1140],
        ['KENTANG DIENG PL 1 KG', 12000],
        ['KENTANG DIENG PL 500 gr', 6000],
        ['KENTANG DIENG PL 250 gr', 3400],
        ['KENTANG DIENG PL 100 gr', 1440],
        ['KENTANG DIENG PL 50 gr', 760],
        ['KENTANG TO RENDANG 1 KG', 10500],
        ['KENTANG TO RENDANG 500 gr', 5250],
        ['KENTANG TO RENDANG 250 gr', 2975],
        ['KENTANG TO RENDANG 100 gr', 1260],
        ['KENTANG TO RENDANG 50 gr', 665],
        ['TOMAT BUAH 1 KG', 7500],
        ['TOMAT BUAH 500 gr', 3750],
        ['TOMAT BUAH 250 gr', 2125],
        ['TOMAT BUAH 100 gr', 900],
        ['TOMAT BUAH 50 gr', 475],
        ['BAWANG MERAH DAERAH SC 1 kg', 37500],
        ['BAWANG MERAH DAERAH SC 500 gr', 18750],
        ['BAWANG MERAH DAERAH SC 250 gr', 10625],
        ['BAWANG MERAH DAERAH SC 100 gr', 4500],
        ['BAWANG MERAH DAERAH SC 50 gr', 2375],
        ['BAWANG MERAH DAERAH ABC 1 kg', 45000],
        ['BAWANG MERAH DAERAH ABC 500 gr', 22500],
        ['BAWANG MERAH DAERAH ABC 250 gr', 12750],
        ['BAWANG MERAH DAERAH ABC 100 gr', 5400],
        ['BAWANG MERAH DAERAH ABC 50 gr', 2850],
        ['RAWIT MERAH ORI DAERAH 1 KG', 75000],
        ['RAWIT MERAH ORI DAERAH 500 gr', 37500],
        ['RAWIT MERAH ORI DAERAH 250 gr', 21250],
        ['RAWIT MERAH ORI DAERAH 100 gr', 9000],
        ['RAWIT MERAH ORI DAERAH 50 gr', 4750],
        ['KERITING MERAH JAWA 1 KG', 45000],
        ['KERITING MERAH JAWA 500 gr', 22500],
        ['KERITING MERAH JAWA 250 gr', 12750],
        ['KERITING MERAH JAWA 100 gr', 5400],
        ['KERITING MERAH JAWA 50 gr', 2850],
        ['RAWIT HIJAU CAPLAK 1 KG', 52500],
        ['RAWIT HIJAU CAPLAK 500 gr', 26250],
        ['RAWIT HIJAU CAPLAK 250 gr', 14875],
        ['RAWIT HIJAU CAPLAK 100 gr', 6300],
        ['RAWIT HIJAU CAPLAK 50 gr', 3325],
        ['CABE TW HIJAU 1 KG', 37500],
        ['CABE TW HIJAU 500 gr', 18750],
        ['CABE TW HIJAU 250 gr', 10625],
        ['CABE TW HIJAU 100 gr', 4500],
        ['CABE TW HIJAU 50 gr', 2375],
        ['KERITING HIJAU 1 KG', 37500],
        ['KERITING HIJAU 500 gr', 18750],
        ['KERITING HIJAU 250 gr', 10625],
        ['KERITING HIJAU 100 gr', 4500],
        ['KERITING HIJAU 50 gr', 2375],
        ['MELINJO 1 KG', 34500],
        ['MELINJO 500 gr', 17250],
        ['MELINJO 250 gr', 9775],
        ['MELINJO 100 gr', 4140],
        ['MELINJO 50 gr', 2185],
        ['DAUN MELINJO 1 KG', 22500],
        ['DAUN MELINJO 500 gr', 11250],
        ['DAUN MELINJO 250 gr', 6375],
        ['DAUN MELINJO 100 gr', 2700],
        ['DAUN MELINJO 50 gr', 1425],
        ['KOL DAERAH 1 KG', 7500],
        ['KOL DAERAH 500 gr', 3750],
        ['KOL DAERAH 250 gr', 2125],
        ['KOL MEDAN BULKY 50 KG', 900],
        ['KOL MEDAN 1 KG', 475],
        ['TERONG UNGU 1 KG', 12000],
        ['TERONG UNGU 500 gr', 5625],
        ['TERONG UNGU 250 gr', 3188],
        ['TIMUN 1 KG', 18000],
        ['TIMUN 500 gr', 9000],
        ['TIMUN 250 gr', 5100],
        ['TIMUN ACAR 1 KG', 13500],
        ['TIMUN ACAR 500 gr', 6750],
        ['TIMUN ACAR 250 gr', 3825],
        ['BUNCIS 1 KG', 33000],
        ['BUNCIS 500 gr', 16500],
        ['BUNCIS 250 gr', 9350],
        ['LABU ACAR 1KG', 15000],
        ['LABU ACAR 500 gr', 7500],
        ['LABU ACAR 250 gr', 4250],
        ['LABU BABY 1 KG', 19500],
        ['LABU BABY 500 gr', 9750],
        ['LABU BABY 250 gr', 5525],
        ['LABU DN SEDANG 1 KG', 12000],
        ['LABU DN SEDANG 500 gr', 6000],
        ['LABU DN SEDANG 250 gr', 3400],
        ['LABU GEDE 1 KG', 10500],
        ['LABU GEDE 500 gr', 5250],
        ['LABU GEDE 250 gr', 2975],
        ['KEMBANG KOL 1 KG', 22500],
        ['KEMBANG KOL 500 gr', 11250],
        ['KEMBANG KOL 250 gr', 6375],
        ['KACANG PANJANG 1 KG', 21000],
        ['KACANG PANJANG 500 gr', 10500],
        ['KACANG PANJANG 250 gr', 5950],
        ['KACANG PANJANG 100 gr', 2380],
        ['WORTEL BRASTAGY 1 KG', 18000],
        ['WORTEL BRASTAGY 500 GR', 9000],
        ['WORTEL BRASTAGY 250 gr', 5100],
        ['WORTEL BRASTAGY 100 gr', 2040],
        ['WORTEL DAERAH 1 KG', 15000],
        ['WORTEL DAERAH 500 GR', 7500],
        ['WORTEL DAERAH 250 gr', 4250],
        ['WORTEL DAERAH 100 gr', 1700],
        ['SAWI PUTIH 1 KG', 7500],
        ['SAWI PUTIH 500 gr', 3750],
        ['SAWI ASIN / SESIM 1 KG', 4500],
        ['SAWI ASIN / SESIM 500 gr', 2250],
        ['SAWI ASIN / SESIM 100 gr', 540],
        ['SELADA KERITING 1 KG', 30000],
        ['SELADA KERITING 500 gr', 15000],
        ['SELADA KERITING 250 gr', 8500],
        ['DAUN JERUK 1 KG', 45000],
        ['DAUN JERUK 50 gr', 2000],
        ['DAUN JERUK 25 gr', 1000],
        ['BAWANG DAUN 1 KG', 22500],
        ['BAWANG DAUN 500 gr', 11250],
        ['BAWANG DAUN 250 gr', 6375],
        ['BAWANG DAUN 100 gr', 2550],
        ['PHOKCOY 1 KG', 7500],
        ['PHOKCOY 500 gr', 3750],
        ['PHOKCOY 250 gr', 2125],
        ['PHOKCOY 100 gr', 850],
        ['KANGKUNG DARAT 1 IKET', 4025],
        ['SEREH 1 KG', 19500],
        ['SEREH 500 gr', 9750],
        ['SEREH 250 gr', 5525],
        ['SEREH 100 gr', 2210],
        ['LENGKUAS 1 KG', 7500],
        ['LENGKUAS 500 gr', 3750],
        ['LENGKUAS 250 gr', 2125],
        ['LENGKUAS 100 gr', 900],
        ['LENGKUAS 50 gr', 475],
        ['KUNYIT 1 KG', 15000],
        ['KUNYIT 500 gr', 7500],
        ['KUNYIT 250 gr', 4250],
        ['KUNYIT 100 gr', 1800],
        ['KUNYIT 50 gr', 950],
        ['KENCUR 1 KG', 63000],
        ['KENCUR 100 gr', 6300],
        ['ASEM 1 KG', 27000],
        ['ASEM 50 gr', 1500],
        ['JAHE GAJAH 1 KG', 19500],
        ['JAHE GAJAH 500 gr', 9750],
        ['JAHE GAJAH 250 gr', 5525],
        ['JAHE GAJAH 100 gr', 2340],
        ['BAYAM 1 IKET', 5400],
        ['BAWANG PUTIH HONAN 1 KG', 45000],
        ['BAWANG PUTIH HONAN 500 gr', 22500],
        ['BAWANG PUTIH HONAN 250 gr', 12750],
        ['BAWANG PUTIH HONAN 100 gr', 5400],
        ['BAWANG PUTIH HONAN 50 gr', 2850],
        ['BAWANG PUTIH KUTING 1 KG', 52500],
        ['BAWANG PUTIH KUTING 500 gr', 26250],
        ['BAWANG PUTIH KUTING 250 gr', 14875],
        ['BAWANG PUTIH KUTING 100 gr', 6300],
        ['BAWANG PUTIH KUTING 50 gr', 3325],
        ['BOMBAY CN 1 KG', 37500],
        ['BOMBAY CN 500 gr', 18750],
        ['BOMBAY CN 250 gr', 10625],
        ['BOMBAY CN 100 gr', 4500],
        ['BOMBAY CN 50 gr', 2375],
        ['SELEDRY LOKAL 1 KG', 22500],
        ['SELEDRY LOKAL 500 gr', 11250],
        ['SELEDRY LOKAL 250 gr', 6375],
        ['SELEDRY LOKAL 100 gr', 2700],
        ['SELEDRY LOKAL 50 gr', 1425],
        ['JAGUNG MANIS 1 KG', 13500],
        ['JAGUNG MANIS 1 PCS', 4000],
        ['JAGUNG BABY 1 KG', 21000],
        ['JAGUNG BABY 500 gr', 10500],
        ['JAGUNG BABY 250 gr', 5950],
        ['JAGUNG BABY 100 gr', 2520],
        ['SINGKONG 1 KG', 4500],
        ['SINGKONG 500 gr', 2250],
        ['SINGKONG 250 gr', 1275],
        ['SINGKONG 100 gr', 540],
    ];

    /**
     * Mapping keyword di nama produk → nama kategori.
     */
    public static array $categoryMap = [
        'KENTANG' => 'Kentang',
        'TOMAT' => 'Tomat',
        'BAWANG MERAH' => 'Bawang Merah',
        'BAWANG PUTIH' => 'Bawang Putih & Bombay',
        'BOMBAY' => 'Bawang Putih & Bombay',
        'RAWIT MERAH' => 'Cabai',
        'KERITING MERAH' => 'Cabai',
        'RAWIT HIJAU' => 'Cabai',
        'CABE' => 'Cabai',
        'KERITING HIJAU' => 'Cabai',
        'MELINJO' => 'Melinjo',
        'DAUN MELINJO' => 'Melinjo',
        'KOL' => 'Kol & Kembang Kol',
        'KEMBANG KOL' => 'Kol & Kembang Kol',
        'TERONG' => 'Terong',
        'TIMUN' => 'Timun',
        'BUNCIS' => 'Buncis & Kacang',
        'KACANG PANJANG' => 'Buncis & Kacang',
        'LABU' => 'Labu',
        'WORTEL' => 'Wortel',
        'SAWI' => 'Sawi & Phokcoy',
        'PHOKCOY' => 'Sawi & Phokcoy',
        'SELADA' => 'Selada',
        'KANGKUNG' => 'Kangkung & Bayam',
        'BAYAM' => 'Kangkung & Bayam',
        'BAWANG DAUN' => 'Daun & Rempah',
        'DAUN JERUK' => 'Daun & Rempah',
        'SEREH' => 'Daun & Rempah',
        'LENGKUAS' => 'Daun & Rempah',
        'KUNYIT' => 'Daun & Rempah',
        'KENCUR' => 'Daun & Rempah',
        'JAHE' => 'Daun & Rempah',
        'ASEM' => 'Daun & Rempah',
        'SELEDRY' => 'Daun & Rempah',
        'JAGUNG' => 'Jagung',
        'SINGKONG' => 'Singkong & Umbi',
    ];

    public function run(): void
    {
        $this->command?->info('Menghapus data produk lama...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('catalog_product_tag')->truncate();
        SoDetail::query()->delete();
        Product::truncate();
        ProductMaster::truncate();
        Category::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command?->info('Membuat kategori...');

        $categories = $this->seedCategories();

        $this->command?->info('Membuat satuan...');
        $satuans = $this->seedSatuans();

        $this->command?->info('Menyimpan produk...');

        $count = 0;
        $seen = [];
        $masters = [];

        foreach (self::$products as $i => [$nama, $harga]) {
            $nama = trim($nama);
            if ($nama === '' || isset($seen[$nama])) {
                continue;
            }
            $seen[$nama] = true;

            $parsed = $this->parseItem($nama);
            $categoryId = $this->resolveCategory($nama, $categories);

            // Master product
            $masterSlug = Str::slug($parsed['master_nama']);
            if (! isset($masters[$masterSlug])) {
                $masters[$masterSlug] = ProductMaster::updateOrCreate(
                    ['product_master_slug' => $masterSlug],
                    [
                        'product_master_nama' => $parsed['master_nama'],
                        'product_master_deskripsi' => 'Master product '.$parsed['master_nama'],
                        'is_active' => true,
                        'sort_order' => $i,
                    ]
                );
            }

            Product::updateOrCreate(
                ['product_slug' => Str::slug($nama)],
                [
                    'product_nama' => $nama,
                    'product_kode' => 'PRD-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'product_harga' => $harga,
                    'product_harga_modal' => (int) round($harga * 0.65),
                    'reseller_fee_percent' => 10.0,
                    'affiliator_fee_percent' => 5.0,
                    'product_stok' => 0,
                    'product_status' => 'active',
                    'is_active' => true,
                    'sort_order' => $i,
                    'product_id_product_master' => $masters[$masterSlug]->id ?? null,
                    'product_id_satuan' => $satuans[$parsed['unit']]?->id ?? null,
                    'product_id_category' => $categoryId,
                ]
            );

            $count++;
        }

        $this->command?->info("Selesai: {$count} produk, ".count($categories).' kategori, '.count($masters).' master.');
    }

    private function seedCategories(): array
    {
        $names = array_unique(self::$categoryMap);
        sort($names);
        $categories = [];
        $order = 0;

        foreach ($names as $nama) {
            $categories[$nama] = Category::updateOrCreate(
                ['category_slug' => Str::slug($nama)],
                [
                    'category_nama' => $nama,
                    'is_active' => true,
                    'sort_order' => $order++,
                ]
            );
        }

        return $categories;
    }

    private function resolveCategory(string $nama, array $categories): ?int
    {
        $namaUpper = Str::upper($nama);

        foreach (self::$categoryMap as $keyword => $catName) {
            if (str_contains($namaUpper, $keyword)) {
                return $categories[$catName]?->id;
            }
        }

        return null;
    }

    private function parseItem(string $nama): array
    {
        $item = ['master_nama' => $nama, 'unit' => 'PCS'];

        if (preg_match('/^(.*?)\s+(\d+(?:\.\d+)?)\s*(KG|GR|PCS|IKET)$/i', $nama, $m)) {
            $item['master_nama'] = trim($m[1]);
            $item['unit'] = strtoupper($m[3]);
            if ($item['unit'] === 'IKET') {
                $item['unit'] = 'PCS';
            }
        }

        return $item;
    }

    private function seedSatuans(): array
    {
        $data = [
            ['satuan_nama' => 'Kilogram', 'satuan_kode' => 'KG', 'satuan_simbol' => 'kg'],
            ['satuan_nama' => 'Gram', 'satuan_kode' => 'GR', 'satuan_simbol' => 'gr'],
            ['satuan_nama' => 'Pieces', 'satuan_kode' => 'PCS', 'satuan_simbol' => 'pcs'],
        ];

        foreach ($data as $i => $row) {
            Satuan::updateOrCreate(
                ['satuan_kode' => $row['satuan_kode']],
                array_merge($row, ['is_active' => true, 'sort_order' => $i])
            );
        }

        return Satuan::whereIn('satuan_kode', collect($data)->pluck('satuan_kode'))
            ->get()
            ->keyBy('satuan_kode')
            ->all();
    }
}
