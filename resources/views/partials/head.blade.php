<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="theme-color" content="#000000" />

@php
    $wsName = ($websiteSettings ?? [])['name'] ?? config('app.name', 'Laravel');
    $rawFavicon = ($websiteSettings ?? [])['favicon'] ?? null;
    $faviconUrl = \App\Models\WebsiteSetting::fileUrl($rawFavicon) ?? '/favicon.ico';
    // Cache-bust agar browser tidak cache favicon lama setelah upload di Settings → Website
    if ($rawFavicon && ! str_starts_with($faviconUrl, 'http')) {
        $faviconPath = public_path(ltrim($faviconUrl, '/'));
        if (is_file($faviconPath)) {
            $faviconUrl .= '?v='.filemtime($faviconPath);
        }
    }
@endphp

<title>{{ filled($title ?? null) ? $title.' - '.$wsName : $wsName }}</title>

<link rel="icon" href="{{ $faviconUrl }}" sizes="any">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/manifest.json">

@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Dynamic theme color dari website settings (sama dengan layouts/head) --}}
@php
    $primaryPalette = \App\Models\WebsiteSetting::palette();
@endphp
<style>
    :root {
        --color-primary: {{ \App\Models\WebsiteSetting::primaryColor() }};
        --color-on-primary: {{ $primaryPalette['on_primary'] }};
        --color-primary-container: {{ $primaryPalette['primary_container'] }};
        --color-on-primary-container: {{ $primaryPalette['on_primary_container'] }};
    }
</style>
