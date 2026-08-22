<?php

namespace App\Http\Controllers;

use App\Models\WebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class WebsiteSettingController extends Controller
{
    public function index(): View
    {
        $settings = WebsiteSetting::merged();

        return view('pages.settings.website', [
            'settings' => $settings,
            'shipping' => $this->shippingConfig(),
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

        $merged = array_merge($existing, $validated, ['colors' => $colors]);

        WebsiteSetting::persist($merged);

        flash()->success('Website settings saved.');

        return Redirect::route('settings.website');
    }

    /**
     * Simpan konfigurasi pengiriman SO langsung ke file .env.
     */
    public function saveShipping(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_name' => ['required', 'string', 'max:255'],
            'warehouse_address' => ['nullable', 'string'],
            'warehouse_lat' => ['required', 'numeric', 'between:-90,90'],
            'warehouse_lng' => ['required', 'numeric', 'between:-180,180'],
            'price_per_km' => ['required', 'numeric', 'min:0'],
            'min_fee' => ['required', 'numeric', 'min:0'],
            'max_radius_km' => ['required', 'numeric', 'min:0'],
            'map_provider' => ['required', 'string', 'max:30'],
            'map_base_url' => ['required', 'url', 'max:255'],
            'map_api_key' => ['nullable', 'string', 'max:255'],
            'cod_name' => ['nullable', 'array'],
            'cod_lat' => ['nullable', 'array'],
            'cod_lng' => ['nullable', 'array'],
            'cod_fee' => ['nullable', 'array'],
        ]);

        // Susun lokasi COD dari baris dinamis (skip baris tanpa nama)
        $cod = [];
        foreach ((array) ($validated['cod_name'] ?? []) as $i => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $cod[] = [
                'name' => $name,
                'lat' => (float) ($validated['cod_lat'][$i] ?? 0),
                'lng' => (float) ($validated['cod_lng'][$i] ?? 0),
                'fee' => (float) ($validated['cod_fee'][$i] ?? 0),
            ];
        }

        $envPath = base_path('.env');
        if (! File::isWritable($envPath)) {
            flash()->error(__('The .env file is not writable.'));

            return Redirect::route('settings.website');
        }

        $this->updateEnv($envPath, [
            'SO_WAREHOUSE_NAME' => $validated['warehouse_name'],
            'SO_WAREHOUSE_ADDRESS' => (string) $validated['warehouse_address'],
            'SO_WAREHOUSE_LAT' => (string) $validated['warehouse_lat'],
            'SO_WAREHOUSE_LNG' => (string) $validated['warehouse_lng'],
            'SO_SHIPPING_PRICE_PER_KM' => (string) $validated['price_per_km'],
            'SO_SHIPPING_MIN_FEE' => (string) $validated['min_fee'],
            'SO_SHIPPING_MAX_RADIUS_KM' => (string) $validated['max_radius_km'],
            'SO_COD_LOCATIONS' => json_encode($cod),
            'SO_MAP_PROVIDER' => $validated['map_provider'],
            'SO_MAP_BASE_URL' => $validated['map_base_url'],
            'SO_MAP_API_KEY' => (string) ($validated['map_api_key'] ?? ''),
        ]);

        flash()->success('Pengaturan pengiriman SO berhasil disimpan.');

        return Redirect::route('settings.website');
    }

    private function shippingConfig(): array
    {
        return [
            'warehouse_name' => config('so.shipping.warehouse.name'),
            'warehouse_address' => config('so.shipping.warehouse.address'),
            'warehouse_lat' => config('so.shipping.warehouse.lat'),
            'warehouse_lng' => config('so.shipping.warehouse.lng'),
            'price_per_km' => config('so.shipping.price_per_km'),
            'min_fee' => config('so.shipping.min_fee'),
            'max_radius_km' => config('so.shipping.max_radius_km'),
            'cod_locations' => collect(config('so.shipping.cod_locations', []))->values()->all(),
            'map_provider' => config('so.map.provider'),
            'map_base_url' => config('so.map.base_url'),
            'map_api_key' => config('so.map.api_key'),
        ];
    }

    /**
     * Upsert KEY=value di .env (quote nilai berisi spasi/karakter khusus).
     */
    private function updateEnv(string $path, array $values): void
    {
        $content = File::exists($path) ? File::get($path) : '';

        foreach ($values as $key => $value) {
            if (preg_match('/[\s"\'#]/', (string) $value)) {
                $value = '"'.str_replace(['\\', '"'], ['\\\\', '\"'], (string) $value).'"';
            }

            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            $line = $key.'='.$value;

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content);
            } else {
                $content = rtrim($content, PHP_EOL).PHP_EOL.PHP_EOL.$line.PHP_EOL;
            }
        }

        File::put($path, $content);

        // Reset config ter-cache agar perubahan langsung terbaca
        if (File::exists(base_path('bootstrap/cache/config.php'))) {
            \Artisan::call('config:clear');
        }
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
