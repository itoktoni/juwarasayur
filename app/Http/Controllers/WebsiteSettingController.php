<?php

namespace App\Http\Controllers;

use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class WebsiteSettingController extends Controller
{
    public function index(): View
    {
        $settings = WebsiteSetting::merged();

        return view('pages.settings.website', [
            'settings' => $settings,
            'warehouse' => [
                'name' => config('so.shipping.warehouse.name'),
                'address' => config('so.shipping.warehouse.address'),
                'lat' => config('so.shipping.warehouse.lat'),
                'lng' => config('so.shipping.warehouse.lng'),
            ],
            'payment' => [
                'qris_expiry' => (int) env('QRIS_EXPIRY_MINUTES', 5),
                'unique_digits' => (int) env('QRIS_UNIQUE_DIGITS', 2),
                'notifyhook_secret' => env('NOTIFYHOOK_SECRET', ''),
            ],
            'frontend' => config('frontend'),
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'alamat' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'footer_text' => ['nullable', 'string'],
            // Gudang utama (dipakai modul SO/ecommerce via config so.shipping.warehouse)
            'warehouse_name' => ['required', 'string', 'max:255'],
            'warehouse_address' => ['nullable', 'string'],
            'warehouse_lat' => ['required', 'numeric', 'between:-90,90'],
            'warehouse_lng' => ['required', 'numeric', 'between:-180,180'],
            // Ukuran kertas struk print continues (58mm / 80mm)
            'struk_paper_width' => ['required', 'integer', 'in:58,80'],
            // Komisi reseller dalam persen
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            // Minimal pencairan komisi (Rp)
            'min_withdraw' => ['required', 'numeric', 'min:0'],
            // Payment settings
            'qris_expiry' => ['required', 'integer', 'min:1', 'max:60'],
            'unique_digits' => ['required', 'integer', 'min:1', 'max:6'],
            'notifyhook_secret' => ['nullable', 'string', 'max:255'],
            // CSV import
            'csv_delimiter' => ['required', 'string', 'in:,;'],
            // Homepage / Frontend
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_subtitle' => ['nullable', 'string'],
            'hero_cta_text' => ['nullable', 'string', 'max:100'],
            'flash_sale_title' => ['nullable', 'string', 'max:100'],
            'flash_sale_count' => ['required', 'integer', 'min:1', 'max:20'],
            'flash_sale_hours' => ['required', 'integer', 'min:1', 'max:48'],
            'show_latest' => ['required', 'boolean'],
            'latest_title' => ['nullable', 'string', 'max:100'],
            // Footer
            'footer_tagline' => ['nullable', 'string'],
            'footer_alamat' => ['nullable', 'string'],
            'footer_telepon' => ['nullable', 'string', 'max:50'],
            'footer_email' => ['nullable', 'email', 'max:255'],
            // Shipping methods
            'shipping_pickup' => ['required', 'boolean'],
            'shipping_cod' => ['required', 'boolean'],
            'shipping_delivery' => ['required', 'boolean'],
            // Delivery pricing
            'delivery_free_km' => ['required', 'numeric', 'min:0', 'max:100'],
            'delivery_price_per_km' => ['required', 'numeric', 'min:0'],
            'delivery_min_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $existing = WebsiteSetting::raw();

        $dir = public_path('storage/website');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($request->hasFile('logo')) {
            $this->deleteOld($existing['logo'] ?? null);
            $validated['logo'] = 'storage/website/'.$this->storeFile($request->file('logo'), $dir);
        } elseif (! empty($validated['remove_logo'])) {
            $this->deleteOld($existing['logo'] ?? null);
            $validated['logo'] = null;
        } else {
            unset($validated['logo']);
        }

        if ($request->hasFile('favicon')) {
            $this->deleteOld($existing['favicon'] ?? null);
            $validated['favicon'] = 'storage/website/'.$this->storeFile($request->file('favicon'), $dir);
        } elseif (! empty($validated['remove_favicon'])) {
            $this->deleteOld($existing['favicon'] ?? null);
            $validated['favicon'] = null;
        } else {
            unset($validated['favicon']);
        }

        $colors = $existing['colors'] ?? [];
        if (! empty($validated['primary_color'])) {
            $colors['primary'] = $validated['primary_color'];
        }
        unset($validated['primary_color'], $validated['remove_logo'], $validated['remove_favicon']);

        // Frontend / Homepage settings
        $frontend = $existing['frontend'] ?? [];
        $frontend['hero'] = [
            'title' => $validated['hero_title'] ?? '',
            'subtitle' => $validated['hero_subtitle'] ?? '',
            'cta_text' => $validated['hero_cta_text'] ?? 'Mulai Belanja',
        ];
        $frontend['flash_sale'] = [
            'title' => $validated['flash_sale_title'] ?? 'Flash Sale',
            'count' => (int) ($validated['flash_sale_count'] ?? 6),
            'hours' => (int) ($validated['flash_sale_hours'] ?? 12),
        ];
        $frontend['latest'] = [
            'show' => (bool) ($validated['show_latest'] ?? true),
            'title' => $validated['latest_title'] ?? 'Produk Terbaru',
        ];
        $frontend['footer'] = [
            'tagline' => $validated['footer_tagline'] ?? '',
            'alamat' => $validated['footer_alamat'] ?? '',
            'telepon' => $validated['footer_telepon'] ?? '',
            'email' => $validated['footer_email'] ?? '',
        ];
        $frontend['shipping'] = [
            'pickup' => (bool) ($validated['shipping_pickup'] ?? true),
            'cod' => (bool) ($validated['shipping_cod'] ?? true),
            'delivery' => (bool) ($validated['shipping_delivery'] ?? true),
        ];
        $frontend['delivery'] = [
            'free_km' => (float) ($validated['delivery_free_km'] ?? 10),
            'price_per_km' => (float) ($validated['delivery_price_per_km'] ?? 2500),
            'min_fee' => (float) ($validated['delivery_min_fee'] ?? 10000),
        ];
        unset($validated['hero_title'], $validated['hero_subtitle'], $validated['hero_cta_text'],
            $validated['flash_sale_title'], $validated['flash_sale_count'], $validated['flash_sale_hours'],
            $validated['show_latest'], $validated['latest_title'],
            $validated['footer_tagline'], $validated['footer_alamat'], $validated['footer_telepon'], $validated['footer_email'],
            $validated['shipping_pickup'], $validated['shipping_cod'], $validated['shipping_delivery'],
            $validated['delivery_free_km'], $validated['delivery_price_per_km'], $validated['delivery_min_fee']);

        $merged = array_merge($existing, $validated, ['colors' => $colors, 'frontend' => $frontend]);

        // Write frontend + footer settings to .env
        $envMap = [
            'FRONTEND_HERO_TITLE' => $frontend['hero']['title'],
            'FRONTEND_HERO_SUBTITLE' => $frontend['hero']['subtitle'],
            'FRONTEND_HERO_CTA_TEXT' => $frontend['hero']['cta_text'],
            'FRONTEND_FLASH_SALE_TITLE' => $frontend['flash_sale']['title'],
            'FRONTEND_FLASH_SALE_COUNT' => $frontend['flash_sale']['count'],
            'FRONTEND_FLASH_SALE_HOURS' => $frontend['flash_sale']['hours'],
            'FRONTEND_SHOW_LATEST' => $frontend['latest']['show'] ? 'true' : 'false',
            'FRONTEND_LATEST_TITLE' => $frontend['latest']['title'],
            'FRONTEND_FOOTER_TAGLINE' => $frontend['footer']['tagline'] ?? '',
            'FRONTEND_FOOTER_ALAMAT' => $frontend['footer']['alamat'] ?? '',
            'FRONTEND_FOOTER_TELEPON' => $frontend['footer']['telepon'] ?? '',
            'FRONTEND_FOOTER_EMAIL' => $frontend['footer']['email'] ?? '',
            'FRONTEND_SHIPPING_PICKUP' => ($frontend['shipping']['pickup'] ?? true) ? 'true' : 'false',
            'FRONTEND_SHIPPING_COD' => ($frontend['shipping']['cod'] ?? true) ? 'true' : 'false',
            'FRONTEND_SHIPPING_DELIVERY' => ($frontend['shipping']['delivery'] ?? true) ? 'true' : 'false',
            'FRONTEND_DELIVERY_FREE_KM' => $frontend['delivery']['free_km'] ?? 10,
            'FRONTEND_DELIVERY_PRICE_PER_KM' => $frontend['delivery']['price_per_km'] ?? 2500,
            'FRONTEND_DELIVERY_MIN_FEE' => $frontend['delivery']['min_fee'] ?? 10000,
        ];
        $this->updateEnv($envMap);

        WebsiteSetting::persist($merged);

        flash()->success('Website settings saved.');

        return Redirect::route('settings.website');
    }

    private function updateEnv(array $data): void
    {
        $path = base_path('.env');
        if (! file_exists($path)) {
            return;
        }

        $env = file_get_contents($path);

        foreach ($data as $key => $value) {
            $value = (string) $value;
            if (str_contains($value, ' ')) {
                $value = '"'.$value.'"';
            }

            if (preg_match('/^'.$key.'=.*/m', $env)) {
                $env = preg_replace('/^'.$key.'=.*/m', $key.'='.$value, $env);
            } else {
                $env .= "\n".$key.'='.$value;
            }
        }

        file_put_contents($path, $env);
    }

    private function storeFile($file, string $dir): string
    {
        $name = uniqid().'_'.preg_replace('/[^a-zA-Z0-9.]/', '', $file->getClientOriginalName());
        $file->move($dir, $name);

        return $name;
    }

    private function deleteOld(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $file = public_path($path);
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
