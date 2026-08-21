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
