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
            'warehouse' => [
                'name' => config('so.shipping.warehouse.name'),
                'address' => config('so.shipping.warehouse.address'),
                'lat' => config('so.shipping.warehouse.lat'),
                'lng' => config('so.shipping.warehouse.lng'),
            ],
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

        // Gudang utama disimpan ke .env agar terbaca config('so.shipping.warehouse.*')
        $envPath = base_path('.env');
        if (File::isWritable($envPath)) {
            $this->updateEnv($envPath, [
                'SO_WAREHOUSE_NAME' => $validated['warehouse_name'],
                'SO_WAREHOUSE_ADDRESS' => (string) $validated['warehouse_address'],
                'SO_WAREHOUSE_LAT' => (string) $validated['warehouse_lat'],
                'SO_WAREHOUSE_LNG' => (string) $validated['warehouse_lng'],
                'STRUK_PAPER_WIDTH' => (string) $validated['struk_paper_width'],
                'RESELLER_COMMISSION_RATE' => (string) $validated['commission_rate'],
                'RESELLER_MIN_WITHDRAW' => (string) $validated['min_withdraw'],
            ]);
        } else {
            flash()->warning(__('The .env file is not writable. Warehouse location was not saved.'));
        }

        flash()->success('Website settings saved.');

        return Redirect::route('settings.website');
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
