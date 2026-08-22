<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Catalog\Models\Product;
use Modules\Cms\Models\Content;

/**
 * Homepage ecommerce: hero, flash sale (default 6 item + timer), produk terbaru.
 * Teks & jumlah item bisa dikustom via CMS (/cms/content → Homepage Toko).
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $settings = $this->homepageSettings();

        $flashCount = max(1, min(12, (int) ($settings['flash_sale_count'] ?? 6)));

        // Flash sale: produk unggulan (featured) aktif
        $flashSaleProducts = Product::query()
            ->where('is_active', true)
            ->where('product_status', 'active')
            ->where('is_featured', true)
            ->with(['has_category'])
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit($flashCount)
            ->get();

        // Produk terbaru untuk grid tambahan (maks 6)
        $showLatest = filter_var($settings['show_latest'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $latestProducts = collect();
        if ($showLatest) {
            $latestProducts = Product::query()
                ->where('is_active', true)
                ->where('product_status', 'active')
                ->when($flashSaleProducts->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $flashSaleProducts->pluck('id')))
                ->with(['has_category'])
                ->orderByDesc('id')
                ->limit(6)
                ->get();
        }

        // Produk paling laris: berdasarkan total qty terjual dari Sales Order
        $soldRows = DB::table('so_order_details')
            ->join('catalog_products', 'catalog_products.id', '=', 'so_order_details.so_detail_id_product')
            ->where('catalog_products.is_active', true)
            ->where('catalog_products.product_status', 'active')
            ->whereNull('catalog_products.deleted_at')
            ->select('so_order_details.so_detail_id_product')
            ->selectRaw('SUM(so_order_details.so_detail_qty) as total_sold')
            ->groupBy('so_order_details.so_detail_id_product')
            ->orderByDesc('total_sold')
            ->limit(6)
            ->get();

        $totalSoldById = $soldRows->pluck('total_sold', 'so_detail_id_product');

        $bestSellingProducts = Product::query()
            ->with(['has_category'])
            ->whereIn('id', $totalSoldById->keys())
            ->get()
            ->map(function ($p) use ($totalSoldById) {
                $p->total_sold = (int) ($totalSoldById[$p->id] ?? 0);

                return $p;
            })
            ->sortByDesc('total_sold')
            ->values();

        // Timer flash sale: durasi dari CMS (jam), fallback akhir hari ini
        $hours = (int) ($settings['flash_sale_hours'] ?? 0);
        $flashSaleEndsAt = $hours > 0 ? now()->addHours($hours) : now()->endOfDay();

        return view('ecommerce::pages.home', [
            'flashSaleProducts' => $flashSaleProducts,
            'latestProducts' => $latestProducts,
            'bestSellingProducts' => $bestSellingProducts,
            'flashSaleEndsAt' => $flashSaleEndsAt,
            'settings' => $settings,
        ]);
    }

    /**
     * Ambil pengaturan homepage dari CMS Content type 'homepage' (meta flat).
     */
    private function homepageSettings(): array
    {
        $entry = Content::query()
            ->whereHas('has_type', fn ($q) => $q->where('slug', 'homepage'))
            ->published()
            ->first();

        return array_merge([
            'hero_title' => 'Sayur & Sembako Segar, Langsung dari Gudang',
            'hero_subtitle' => 'Harga grosir untuk semua. Pesan mudah, ambil di gudang atau kirim ke lokasi Anda.',
            'hero_cta_text' => 'Mulai Belanja',
            'flash_sale_title' => 'Flash Sale',
            'flash_sale_count' => 6,
            'flash_sale_hours' => 0,
            'show_latest' => true,
            'latest_title' => 'Produk Terbaru',
            'best_selling_title' => 'Paling Laris',
        ], $entry?->meta ?? []);
    }
}
