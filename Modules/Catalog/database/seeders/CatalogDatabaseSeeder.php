<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Satuan;
use Modules\Catalog\Models\Tag;

class CatalogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->pruneLegacy();

        $brands = $this->seedBrands();
        $satuans = $this->seedSatuans();
        $categories = $this->seedCategories();
        $tags = $this->seedTags();
        $this->seedProducts($brands, $satuans, $categories, $tags);
    }

    private function pruneLegacy(): void
    {
        $legacyProductSlugs = [
            'xiaomi-redmi-note-13-pro', 'samsung-galaxy-a54', 'samsung-smart-tv-43-4k',
            'xiaomi-book-14-i5', 'xiaomi-buds-5-pro', 'uniqlo-supima-cotton-tee',
            'eiger-areta-waterproof', 'eiger-bodaga-trail', 'eiger-equator-45l',
            'polygon-cascade-3', 'informa-rak-dapur-3-tingkat', 'informa-lampu-rattan-standing',
            'hampers-mayur-lebaran-premium', 'xiaomi-powerbank-20000-33w',
            'casing-hp-transparan-premium', 'informa-meja-kerja-lipat',
        ];
        $legacyCategorySlugs = [
            'elektronik', 'fashion', 'rumah-tangga', 'olahraga-outdoor',
            'smartphone', 'laptop', 'aksesoris-hp', 'kaos', 'jaket', 'sepatu', 'dapur', 'dekorasi', 'tas-gunung', 'sepeda',
        ];
        $legacyBrandSlugs = ['samsung', 'xiaomi', 'uniqlo', 'eiger', 'informa'];
        $legacyTagSlugs = ['terbaru', 'limited', 'eco-friendly', 'garansi-resmi', 'gratis-ongkir'];

        DB::table('catalog_product_tag')->whereIn('product_id', Product::whereIn('product_slug', $legacyProductSlugs)->pluck('id'))->delete();
        Product::whereIn('product_slug', $legacyProductSlugs)->forceDelete();
        Category::whereIn('category_slug', $legacyCategorySlugs)->forceDelete();
        Brand::whereIn('brand_slug', $legacyBrandSlugs)->forceDelete();
        Tag::whereIn('tag_slug', $legacyTagSlugs)->forceDelete();
        Brand::where('brand_slug', 'mayur')->forceDelete();
        Category::where('category_slug', 'mayur')->forceDelete();
    }

    private function seedBrands(): Collection
    {
        $data = [
            ['brand_nama' => 'Mayur Fresh', 'brand_slug' => 'mayur-fresh', 'brand_deskripsi' => 'House brand sayur segar Mayur — petik hari ini, kirim hari ini'],
            ['brand_nama' => 'Hidroponik Segar', 'brand_slug' => 'hidroponik-segar', 'brand_deskripsi' => 'Sayur hidroponik bebas pestisida'],
            ['brand_nama' => 'Kebun Organik', 'brand_slug' => 'kebun-organik', 'brand_deskripsi' => 'Sertifikasi organik, tanpa pestisida kimia'],
            ['brand_nama' => 'Tani Lokal', 'brand_slug' => 'tani-lokal', 'brand_deskripsi' => 'Mitra petani lokal Jawa Barat & Lembang'],
            ['brand_nama' => 'Mitra Petani', 'brand_slug' => 'mitra-petani', 'brand_deskripsi' => 'Jaringan petani Dieng, Berastagi & Batu Malang'],
        ];

        foreach ($data as $i => $row) {
            Brand::updateOrCreate(
                ['brand_slug' => $row['brand_slug']],
                array_merge($row, ['is_active' => true, 'sort_order' => $i])
            );
        }

        return Brand::whereIn('brand_slug', collect($data)->pluck('brand_slug'))->get()->keyBy('brand_slug');
    }

    private function seedSatuans(): Collection
    {
        $data = [
            ['satuan_nama' => 'Kilogram', 'satuan_kode' => 'KG', 'satuan_simbol' => 'kg'],
            ['satuan_nama' => 'Ikat', 'satuan_kode' => 'IKAT', 'satuan_simbol' => 'ikat'],
            ['satuan_nama' => 'Pack', 'satuan_kode' => 'PACK', 'satuan_simbol' => 'pack'],
            ['satuan_nama' => 'Pieces', 'satuan_kode' => 'PCS', 'satuan_simbol' => 'pcs'],
            ['satuan_nama' => 'Karung', 'satuan_kode' => 'KRG', 'satuan_simbol' => 'karung'],
        ];

        foreach ($data as $i => $row) {
            Satuan::updateOrCreate(
                ['satuan_kode' => $row['satuan_kode']],
                array_merge($row, ['is_active' => true, 'sort_order' => $i])
            );
        }

        return Satuan::whereIn('satuan_kode', collect($data)->pluck('satuan_kode'))->get()->keyBy('satuan_kode');
    }

    private function seedCategories(): Collection
    {
        $parents = [
            ['category_nama' => 'Sayuran Daun', 'category_slug' => 'sayuran-daun', 'category_deskripsi' => 'Bayam, kangkung, sawi, selada, pakcoy', 'category_icon' => 'grass'],
            ['category_nama' => 'Sayuran Buah', 'category_slug' => 'sayuran-buah', 'category_deskripsi' => 'Tomat, terong, timun, pare, labu', 'category_icon' => 'nutrition'],
            ['category_nama' => 'Sayuran Akar & Umbi', 'category_slug' => 'sayuran-akar-umbi', 'category_deskripsi' => 'Wortel, kentang, ubi, lobak', 'category_icon' => 'psychiatry'],
            ['category_nama' => 'Bumbu Dapur', 'category_slug' => 'bumbu-dapur', 'category_deskripsi' => 'Cabai, bawang, jahe, kunyit, lengkuas', 'category_icon' => 'skillet'],
            ['category_nama' => 'Jamur', 'category_slug' => 'jamur', 'category_deskripsi' => 'Jamur tiram, kancing, enoki, shitake', 'category_icon' => 'forest'],
            ['category_nama' => 'Buah Segar', 'category_slug' => 'buah-segar', 'category_deskripsi' => 'Buah petik segar', 'category_icon' => 'nutrition'],
        ];

        foreach ($parents as $i => $row) {
            Category::updateOrCreate(
                ['category_slug' => $row['category_slug']],
                array_merge($row, ['parent_id' => null, 'is_active' => true, 'sort_order' => $i])
            );
        }

        $parentMap = Category::whereIn('category_slug', collect($parents)->pluck('category_slug'))->get()->keyBy('category_slug');

        $children = [
            ['category_nama' => 'Bayam', 'category_slug' => 'bayam', 'parent' => 'sayuran-daun'],
            ['category_nama' => 'Kangkung', 'category_slug' => 'kangkung', 'parent' => 'sayuran-daun'],
            ['category_nama' => 'Sawi & Pakcoy', 'category_slug' => 'sawi-pakcoy', 'parent' => 'sayuran-daun'],
            ['category_nama' => 'Selada', 'category_slug' => 'selada', 'parent' => 'sayuran-daun'],
            ['category_nama' => 'Tomat', 'category_slug' => 'tomat', 'parent' => 'sayuran-buah'],
            ['category_nama' => 'Terong & Timun', 'category_slug' => 'terong-timun', 'parent' => 'sayuran-buah'],
            ['category_nama' => 'Wortel', 'category_slug' => 'wortel', 'parent' => 'sayuran-akar-umbi'],
            ['category_nama' => 'Kentang', 'category_slug' => 'kentang', 'parent' => 'sayuran-akar-umbi'],
            ['category_nama' => 'Cabai', 'category_slug' => 'cabai', 'parent' => 'bumbu-dapur'],
            ['category_nama' => 'Bawang', 'category_slug' => 'bawang', 'parent' => 'bumbu-dapur'],
            ['category_nama' => 'Jahe & Rimpang', 'category_slug' => 'jahe-rimpang', 'parent' => 'bumbu-dapur'],
            ['category_nama' => 'Jamur Tiram', 'category_slug' => 'jamur-tiram', 'parent' => 'jamur'],
            ['category_nama' => 'Paket Sayur', 'category_slug' => 'paket-sayur', 'parent' => 'sayuran-daun'],
        ];

        foreach ($children as $i => $row) {
            Category::updateOrCreate(
                ['category_slug' => $row['category_slug']],
                [
                    'category_nama' => $row['category_nama'],
                    'category_icon' => 'label',
                    'parent_id' => $parentMap[$row['parent']]->id ?? null,
                    'is_active' => true,
                    'sort_order' => 100 + $i,
                ]
            );
        }

        return Category::all()->keyBy('category_slug');
    }

    private function seedTags(): Collection
    {
        $data = [
            ['tag_nama' => 'Segar', 'tag_slug' => 'segar', 'tag_warna' => '#16a34a'],
            ['tag_nama' => 'Organik', 'tag_slug' => 'organik', 'tag_warna' => '#059669'],
            ['tag_nama' => 'Hidroponik', 'tag_slug' => 'hidroponik', 'tag_warna' => '#0891b2'],
            ['tag_nama' => 'Petik Hari Ini', 'tag_slug' => 'petik-hari-ini', 'tag_warna' => '#2563eb'],
            ['tag_nama' => 'Best Seller', 'tag_slug' => 'best-seller', 'tag_warna' => '#ea580c'],
            ['tag_nama' => 'Promo', 'tag_slug' => 'promo', 'tag_warna' => '#dc2626'],
            ['tag_nama' => 'Paket Hemat', 'tag_slug' => 'paket-hemat', 'tag_warna' => '#9333ea'],
            ['tag_nama' => 'Preorder', 'tag_slug' => 'preorder', 'tag_warna' => '#4f46e5'],
        ];

        foreach ($data as $i => $row) {
            Tag::updateOrCreate(
                ['tag_slug' => $row['tag_slug']],
                array_merge($row, ['is_active' => true, 'sort_order' => $i])
            );
        }

        return Tag::whereIn('tag_slug', collect($data)->pluck('tag_slug'))->get()->keyBy('tag_slug');
    }

    private function seedProducts($brands, $satuans, $categories, $tags): void
    {
        $tagIds = $tags->pluck('id')->all();

        $items = [
            ['nama' => 'Bayam Hijau Segar Ikat', 'slug' => 'bayam-hijau-ikat', 'harga' => 8000, 'stok' => 120, 'brand' => 'tani-lokal', 'cat' => 'bayam', 'satuan' => 'IKAT', 'berat' => 0.25, 'featured' => true],
            ['nama' => 'Kangkung Cabut Segar Ikat', 'slug' => 'kangkung-cabut-ikat', 'harga' => 9000, 'stok' => 100, 'brand' => 'tani-lokal', 'cat' => 'kangkung', 'satuan' => 'IKAT', 'berat' => 0.25, 'featured' => true],
            ['nama' => 'Sawi Hijau Segar 500g', 'slug' => 'sawi-hijau-500g', 'harga' => 12000, 'stok' => 80, 'brand' => 'mayur-fresh', 'cat' => 'sawi-pakcoy', 'satuan' => 'PACK', 'berat' => 0.5, 'featured' => false],
            ['nama' => 'Pakcoy Hidroponik 250g', 'slug' => 'pakcoy-hidroponik-250g', 'harga' => 15000, 'stok' => 60, 'brand' => 'hidroponik-segar', 'cat' => 'sawi-pakcoy', 'satuan' => 'PACK', 'berat' => 0.25, 'featured' => true],
            ['nama' => 'Selada Keriting Hidroponik 200g', 'slug' => 'selada-keriting-hidroponik', 'harga' => 18000, 'stok' => 55, 'brand' => 'hidroponik-segar', 'cat' => 'selada', 'satuan' => 'PACK', 'berat' => 0.2, 'featured' => true],
            ['nama' => 'Tomat Merah Segar 1kg', 'slug' => 'tomat-merah-1kg', 'harga' => 16000, 'stok' => 90, 'brand' => 'mitra-petani', 'cat' => 'tomat', 'satuan' => 'KG', 'berat' => 1, 'featured' => false],
            ['nama' => 'Cabai Rawit Merah 250g', 'slug' => 'cabai-rawit-merah-250g', 'harga' => 35000, 'stok' => 40, 'brand' => 'mitra-petani', 'cat' => 'cabai', 'satuan' => 'PACK', 'berat' => 0.25, 'featured' => true],
            ['nama' => 'Cabai Merah Keriting 500g', 'slug' => 'cabai-merah-keriting-500g', 'harga' => 32000, 'stok' => 35, 'brand' => 'mitra-petani', 'cat' => 'cabai', 'satuan' => 'PACK', 'berat' => 0.5, 'featured' => false],
            ['nama' => 'Bawang Merah Brebes 1kg', 'slug' => 'bawang-merah-brebes-1kg', 'harga' => 28000, 'stok' => 70, 'brand' => 'mitra-petani', 'cat' => 'bawang', 'satuan' => 'KG', 'berat' => 1, 'featured' => true],
            ['nama' => 'Bawang Putih Kating 500g', 'slug' => 'bawang-putih-kating-500g', 'harga' => 30000, 'stok' => 45, 'brand' => 'mitra-petani', 'cat' => 'bawang', 'satuan' => 'PACK', 'berat' => 0.5, 'featured' => false],
            ['nama' => 'Kentang Dieng 1kg', 'slug' => 'kentang-dieng-1kg', 'harga' => 18000, 'stok' => 85, 'brand' => 'mitra-petani', 'cat' => 'kentang', 'satuan' => 'KG', 'berat' => 1, 'featured' => false],
            ['nama' => 'Wortel Berastagi 1kg', 'slug' => 'wortel-berastagi-1kg', 'harga' => 14000, 'stok' => 95, 'brand' => 'mitra-petani', 'cat' => 'wortel', 'satuan' => 'KG', 'berat' => 1, 'featured' => false],
            ['nama' => 'Brokoli Segar 500g', 'slug' => 'brokoli-segar-500g', 'harga' => 25000, 'stok' => 30, 'brand' => 'kebun-organik', 'cat' => 'sayuran-daun', 'satuan' => 'PACK', 'berat' => 0.5, 'featured' => true],
            ['nama' => 'Jamur Tiram Segar 250g', 'slug' => 'jamur-tiram-segar-250g', 'harga' => 22000, 'stok' => 50, 'brand' => 'kebun-organik', 'cat' => 'jamur-tiram', 'satuan' => 'PACK', 'berat' => 0.25, 'featured' => false],
            ['nama' => 'Jahe Gajah Segar 500g', 'slug' => 'jahe-gajah-500g', 'harga' => 28000, 'stok' => 38, 'brand' => 'tani-lokal', 'cat' => 'jahe-rimpang', 'satuan' => 'PACK', 'berat' => 0.5, 'featured' => false],
            ['nama' => 'Paket Sayur Sop Lengkap', 'slug' => 'paket-sayur-sop-lengkap', 'harga' => 25000, 'stok' => 60, 'brand' => 'mayur-fresh', 'cat' => 'paket-sayur', 'satuan' => 'PACK', 'berat' => 0.8, 'featured' => true],
            ['nama' => 'Paket Sayur Asem Komplit', 'slug' => 'paket-sayur-asem-komplit', 'harga' => 22000, 'stok' => 55, 'brand' => 'mayur-fresh', 'cat' => 'paket-sayur', 'satuan' => 'PACK', 'berat' => 0.8, 'featured' => true],
            ['nama' => 'Timun Segar 500g', 'slug' => 'timun-segar-500g', 'harga' => 12000, 'stok' => 75, 'brand' => 'tani-lokal', 'cat' => 'terong-timun', 'satuan' => 'PACK', 'berat' => 0.5, 'featured' => false],
        ];

        foreach ($items as $idx => $item) {
            $catId = $categories[$item['cat']]->id ?? null;
            $brandId = $brands[$item['brand']]->id ?? null;
            $satuanId = $satuans[$item['satuan']]->id ?? $satuans['KG']->id;
            $hargaModal = (int) round($item['harga'] * 0.65);

            $product = Product::updateOrCreate(
                ['product_slug' => $item['slug']],
                [
                    'product_nama' => $item['nama'],
                    'product_kode' => strtoupper(Str::slug($item['slug'], '').'-'.substr(md5($item['slug']), 0, 4)),
                    'product_sku' => 'SKU-'.strtoupper(Str::slug($item['slug'], '-')),
                    'product_deskripsi' => $item['nama'].' — sayur segar petik hari ini, cuci bersih, siap masak.',
                    'product_deskripsi_lengkap' => '<p>'.$item['nama'].' dipetik segar dari mitra petani pilihan Mayur. Tanpa pengawet, dikemas higienis dan dikirim dingin agar kesegaran terjaga sampai dapur.</p><ul><li>Petik hari ini</li><li>Tanpa pengawet</li><li>Siap masak & higienis</li><li>Pengiriman dingin</li></ul>',
                    'product_harga' => $item['harga'],
                    'product_harga_modal' => $hargaModal,
                    'product_harga_grosir' => $item['harga'] >= 20000 ? (int) round($item['harga'] * 0.88) : null,
                    'product_stok' => $item['stok'],
                    'product_stok_minimum' => 10,
                    'product_berat' => $item['berat'],
                    'product_status' => 'active',
                    'is_featured' => $item['featured'],
                    'is_active' => true,
                    'sort_order' => $idx,
                    'product_id_brand' => $brandId,
                    'product_id_satuan' => $satuanId,
                    'product_id_category' => $catId,
                ]
            );

            $pick = collect($tagIds)->shuffle()->take(rand(1, 3))->all();
            if (! empty($pick)) {
                $product->has_tags()->sync($pick);
            }
        }
    }
}
