<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Controller;
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

        // Produk terbaru untuk grid tambahan
        $showLatest = filter_var($settings['show_latest'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $latestProducts = collect();
        if ($showLatest) {
            $latestProducts = Product::query()
                ->where('is_active', true)
                ->where('product_status', 'active')
                ->when($flashSaleProducts->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $flashSaleProducts->pluck('id')))
                ->with(['has_category'])
                ->orderByDesc('id')
                ->limit(12)
                ->get();
        }

        // Timer flash sale: durasi dari CMS (jam), fallback akhir hari ini
        $hours = (int) ($settings['flash_sale_hours'] ?? 0);
        $flashSaleEndsAt = $hours > 0 ? now()->addHours($hours) : now()->endOfDay();

        return view('ecommerce::pages.home', [
            'flashSaleProducts' => $flashSaleProducts,
            'latestProducts' => $latestProducts,
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
        ], $entry?->meta ?? []);
    }
}
