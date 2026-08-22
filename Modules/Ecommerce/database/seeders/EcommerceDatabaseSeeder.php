<?php

namespace Modules\Ecommerce\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Brand;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

class EcommerceDatabaseSeeder extends Seeder
{
    /**
     * Demo data toko: kategori, brand, dan produk
     * (9 produk featured untuk flash sale homepage).
     */
    public function run(): void
    {
        if (Product::count() > 0) {
            $this->command->info('Produk sudah ada, skip seeding Ecommerce.');

            return;
        }

        $categories = [
            ['name' => 'Sayuran', 'icon' => 'grass'],
            ['name' => 'Buah-buahan', 'icon' => 'nutrition'],
            ['name' => 'Sembako', 'icon' => 'rice_bowl'],
            ['name' => 'Bumbu Dapur', 'icon' => 'restaurant'],
        ];

        $categoryIds = [];
        foreach ($categories as $i => $cat) {
            $existing = Category::where('category_slug', Str::slug($cat['name']))->first();
            if ($existing) {
                $categoryIds[$i] = $existing->id;

                continue;
            }

            $categoryIds[$i] = Category::create([
                'category_nama' => $cat['name'],
                'category_slug' => Str::slug($cat['name']),
                'category_icon' => $cat['icon'],
                'is_active' => true,
                'sort_order' => $i,
            ])->id;
        }

        $brand = Brand::first() ?? Brand::create([
            'brand_nama' => 'Mayur Fresh',
            'brand_slug' => 'mayur-fresh',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        // 9 produk flash sale (featured) + 6 reguler
        $products = [
            // --- Flash Sale (featured) ---
            ['Bayam Hijau', 0, 3500, 100, true],
            ['Kangkung Segar', 0, 3000, 120, true],
            ['Tomat Merah', 0, 8000, 80, true],
            ['Wortel Indonesia', 0, 9000, 90, true],
            ['Bawang Merah', 3, 28000, 150, true],
            ['Cabai Rawit Merah', 3, 45000, 60, true],
            ['Beras Pandan Wangi 5kg', 2, 72000, 40, true],
            ['Gula Pasir 1kg', 2, 16500, 200, true],
            ['Minyak Goreng 2L', 2, 36000, 75, true],
            // --- Reguler ---
            ['Kentang Grendel', 0, 12000, 70, false],
            ['Kol / Kubis', 0, 6000, 85, false],
            ['Pisang Cavendish', 1, 22000, 50, false],
            ['Jeruk Manis Baby', 1, 25000, 65, false],
            ['Tepung Terigu 1kg', 2, 13000, 110, false],
            ['Kecap Manis Refill 500ml', 3, 18000, 45, false],
        ];

        foreach ($products as $i => [$nama, $catIndex, $harga, $stok, $featured]) {
            Product::create([
                'product_nama' => $nama,
                'product_slug' => Str::slug($nama),
                'product_kode' => 'PRD-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'product_deskripsi' => $nama.' segar berkualitas, harga grosir.',
                'product_harga' => $harga,
                'product_stok' => $stok,
                'product_status' => 'active',
                'is_featured' => $featured,
                'is_active' => true,
                'sort_order' => $i,
                'product_id_brand' => $brand->id,
                'product_id_category' => $categoryIds[$catIndex],
            ]);
        }

        $this->command->info('Ecommerce seeder selesai: '.count($products).' produk dibuat (9 flash sale).');
    }
}
