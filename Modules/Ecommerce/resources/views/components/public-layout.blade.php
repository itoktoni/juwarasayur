{{-- Layout publik ecommerce: sama dengan halaman /product --}}
@props(['title' => 'Toko'])
@php
    $siteName = config('app.name', 'Mayur');
    $cartCount = app(\Modules\Ecommerce\Services\CartService::class)->count();
    $txUrl = auth()->check() ? route('ecommerce.orders.index') : route('login');
    $profileUrl = auth()->check() ? route('account.profile') : route('login');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name', 'Mayur') }}</title>
    @php $faviconUrl = \App\Models\WebsiteSetting::fileUrl(\App\Models\WebsiteSetting::merged()['favicon'] ?? null) ?? asset('favicon.ico'); @endphp
    <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    @include('ecommerce::components.brand')
</head>
<body class="bg-surface text-on-surface antialiased pb-16 md:pb-0">

    {{-- Navbar --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-outline-variant">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 py-3">
                <a href="{{ route('home') }}" class="shrink-0" title="Juwara Sayur">
                    <img src="{{ asset('images/logo.png') }}" alt="Juwara Sayur" class="h-10 w-auto">
                </a>
                <form method="GET" action="{{ route('shop.index') }}" class="hidden md:flex flex-1 max-w-xl gap-2">
                    <div class="join flex-1">
                        <input type="search" name="q" placeholder="Cari produk, kode, atau SKU..." class="input input-sm join-item flex-1" />
                        <button type="submit" class="btn btn-primary btn-sm join-item px-4">Cari</button>
                    </div>
                </form>
                <nav class="ml-auto flex items-center gap-1 sm:gap-2">
                    <a href="{{ route('shop.index') }}" class="text-sm font-medium px-2 py-1 rounded-lg {{ request()->routeIs('shop.*') ? 'text-primary' : 'text-on-surface-variant hover:text-primary' }} transition-colors">Katalog</a>
                    <a href="{{ route('blog') }}" class="hidden sm:block text-sm font-medium px-2 py-1 rounded-lg text-on-surface-variant hover:text-primary transition-colors">Blog</a>
                    <a href="{{ route('cart.index') }}" class="relative p-2 rounded-full hover:bg-surface-container" title="Keranjang">
                        <span class="material-symbols-outlined">shopping_cart</span>
                        <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-white text-[10px] font-bold grid place-items-center {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                    </a>
                    @auth
                        @if(auth()->user()->isAdmin() || auth()->user()->isDeveloper())
                            <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex btn btn-soft btn-sm ml-1">Dashboard</a>
                        @else
                            <a href="{{ route('account.profile') }}" class="hidden sm:inline-flex btn btn-soft btn-sm ml-1">Akun</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm ml-1">Masuk</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    {{-- Konten --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 mt-4 lg:mt-0">
        {{ $slot }}
    </main>

    @include('cms::frontend.layouts.footer')

    {{-- Bottom bar navigasi mobile --}}
    <nav class="fixed bottom-0 inset-x-0 z-40 md:hidden bg-white border-t border-outline-variant pb-[env(safe-area-inset-bottom)]" aria-label="Navigasi utama">
        <div class="grid grid-cols-4 h-16 max-w-lg mx-auto">
            @php
                $bottomItems = [
                    ['href' => route('home'), 'label' => 'Home', 'icon' => 'home', 'active' => request()->routeIs('home')],
                    ['href' => route('cart.index'), 'label' => 'Keranjang', 'icon' => 'shopping_cart', 'active' => request()->routeIs('cart.*') || request()->routeIs('checkout.*') || request()->routeIs('payment.*')],
                    ['href' => $txUrl, 'label' => 'Transaksi', 'icon' => 'receipt_long', 'active' => request()->routeIs('ecommerce.orders.*')],
                    ['href' => $profileUrl, 'label' => 'Profile', 'icon' => 'person', 'active' => request()->routeIs('profile.*')],
                ];
            @endphp
            @foreach($bottomItems as $item)
                <a href="{{ $item['href'] }}" aria-current="{{ $item['active'] ? 'page' : false }}"
                    class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ $item['active'] ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">
                    <span class="material-symbols-outlined text-2xl" style="{{ $item['active'] ? "font-variation-settings: 'FILL' 1;" : '' }}">{{ $item['icon'] }}</span>
                    <span class="relative text-[11px] leading-none">
                        {{ $item['label'] }}
                        @if($item['icon'] === 'shopping_cart')
                            <span data-cart-count class="absolute -top-2.5 -right-4 min-w-[16px] h-4 px-1 rounded-full bg-error text-white text-[9px] font-bold grid place-items-center {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    </nav>

    @include('ecommerce::components.cart-button')

    {{-- Floating chat button --}}
    <a href="{{ url('/chat') }}" target="_blank" rel="noopener"
       style="position:fixed;bottom:10px;right:16px;z-index:9999;width:56px;height:56px;border-radius:50%;background:var(--color-primary,#388e3c);color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,.3);text-decoration:none;"
       title="Chat dengan kami">
        <span class="material-symbols-outlined" style="font-size:28px;">chat</span>
    </a>
</body>
</html>
