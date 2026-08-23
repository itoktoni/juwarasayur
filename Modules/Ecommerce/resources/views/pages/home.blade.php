@php
    $siteName = config('app.name', 'Mayur');
    $cartCount = app(\Modules\Ecommerce\Services\CartService::class)->count();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName }} — Toko Sayur & Sembako Segar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
        @include('ecommerce::components.brand')
</head>
<body class="bg-surface text-on-surface antialiased">

    {{-- Navbar --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-outline-variant">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Juwara Sayur" class="h-10 w-auto">
            </a>
            <form method="GET" action="{{ route('shop.index') }}" class="hidden md:flex flex-1 max-w-xl">
                <input type="search" name="q" placeholder="Cari produk..." class="w-full h-10 px-4 bg-surface-container border border-outline-variant rounded-l-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                <button type="submit" class="h-10 px-4 bg-primary text-on-primary rounded-r-lg"><span class="material-symbols-outlined text-base">search</span></button>
            </form>
            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('shop.index') }}" class="text-sm text-on-surface hover:text-primary px-2 hidden sm:block">Belanja</a>
                <a href="{{ route('blog') }}" class="text-sm text-on-surface hover:text-primary px-2 hidden sm:block">Blog</a>
                <a href="{{ route('cart.index') }}" class="relative p-2 rounded-full hover:bg-surface-container" title="Keranjang">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-white text-[10px] font-bold grid place-items-center {{ $cartCount <= 0 ? 'hidden' : '' }}">{{ max($cartCount, 0) }}</span>
                </a>
                @auth
                    <a href="{{ route('ecommerce.orders.index') }}" class="p-2 rounded-full hover:bg-surface-container" title="Pesanan Saya">
                        <span class="material-symbols-outlined">receipt_long</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="bg-gradient-to-r from-primary to-primary/70 text-on-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight">{{ $settings['hero_title'] }}</h1>
            <p class="mt-4 text-sm md:text-base opacity-90 max-w-2xl mx-auto">{{ $settings['hero_subtitle'] }}</p>
            <div class="mt-6 flex justify-center gap-3">
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-white text-primary font-semibold px-6 py-3 rounded-lg hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-base">storefront</span> {{ $settings['hero_cta_text'] }}
                </a>
                <a href="#flash-sale" class="inline-flex items-center gap-2 border border-white/60 text-white font-semibold px-6 py-3 rounded-lg hover:bg-white/10 transition-colors">
                    <span class="material-symbols-outlined text-base">bolt</span> Flash Sale
                </a>
            </div>
        </div>
    </section>

    {{-- Flash Sale --}}
    <section id="flash-sale" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-error text-2xl">bolt</span>
                <h2 class="text-xl md:text-2xl font-bold">{{ $settings['flash_sale_title'] }}</h2>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="text-on-surface-variant">Berakhir dalam</span>
                <div id="flash-timer" data-ends-at="{{ $flashSaleEndsAt->toIso8601String() }}" class="flex items-center gap-1 font-mono font-bold">
                    <span id="ft-h" class="bg-error text-on-error rounded px-2 py-1 min-w-[34px] text-center">00</span>:
                    <span id="ft-m" class="bg-error text-on-error rounded px-2 py-1 min-w-[34px] text-center">00</span>:
                    <span id="ft-s" class="bg-error text-on-error rounded px-2 py-1 min-w-[34px] text-center">00</span>
                </div>
            </div>
        </div>

        @if($flashSaleProducts->isEmpty())
            <div class="p-8 rounded-xl border border-dashed border-outline-variant text-center text-on-surface-variant text-sm">
                Flash sale belum tersedia. Cek katalog lengkap kami.
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
                @foreach($flashSaleProducts as $p)
                    <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/60 bg-white shadow-[0_1px_3px_rgba(15,61,17,0.08)] hover:-translate-y-1 hover:border-primary-fixed hover:shadow-[0_14px_30px_-10px_rgba(46,125,50,0.4)] transition-all duration-300">
                        <span class="absolute left-2 top-2 z-10 inline-flex items-center gap-0.5 rounded-full bg-error px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-widest text-on-error shadow-lg shadow-error/40"><span class="material-symbols-outlined text-[11px]" style="font-variation-settings: 'FILL' 1;">bolt</span>Flash</span>
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="relative block aspect-square overflow-hidden bg-surface-container">
                            @if($p->product_gambar)
                                <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" loading="lazy">
                            @else
                                <div class="w-full h-full grid place-items-center text-outline-variant"><span class="material-symbols-outlined text-4xl">image</span></div>
                            @endif
                        </a>
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="block px-3 pb-2 pt-2.5 text-[13px] font-semibold leading-snug text-on-surface line-clamp-2 min-h-[2.6rem] transition-colors hover:text-primary">{{ $p->product_nama }}</a>
<button type="button" onclick="addToCart({{ $p->id }}, this)" @disabled($p->product_stok <= 0)
    class="mt-auto flex w-full items-center justify-between gap-2 bg-primary px-3 py-2.5 text-on-primary transition-colors duration-200 hover:bg-primary-container disabled:pointer-events-none disabled:opacity-50" title="Tambah ke keranjang">
    <span class="text-sm font-extrabold tracking-tight">{{ formatAngka((int) $p->product_harga, 'Rp ') }}</span>
    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-white/20 shadow-inner">
        <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">add_shopping_cart</span>
    </span>
</button>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Paling Laris --}}
    @if($bestSellingProducts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-warning text-2xl">local_fire_department</span>
                    <h2 class="text-xl md:text-2xl font-bold">{{ $settings['best_selling_title'] }}</h2>
                </div>
                <a href="{{ route('shop.index') }}" class="text-sm text-primary hover:underline inline-flex items-center gap-1">
                    Lihat Semua <span class="material-symbols-outlined text-base">arrow_forward</span>
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3 sm:gap-4">
                @foreach($bestSellingProducts as $p)
                    <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/60 bg-white shadow-[0_1px_3px_rgba(15,61,17,0.08)] hover:-translate-y-1 hover:border-primary-fixed hover:shadow-[0_14px_30px_-10px_rgba(46,125,50,0.4)] transition-all duration-300">
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="relative block aspect-square overflow-hidden bg-surface-container">
                            @if($p->product_gambar)
                                <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" loading="lazy">
                            @else
                                <div class="w-full h-full grid place-items-center text-outline-variant"><span class="material-symbols-outlined text-4xl">image</span></div>
                            @endif
                        </a>
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="block px-3 pb-2 pt-2.5 text-[13px] font-semibold leading-snug text-on-surface line-clamp-2 min-h-[2.6rem] transition-colors hover:text-primary">{{ $p->product_nama }}</a>
<button type="button" onclick="addToCart({{ $p->id }}, this)" @disabled($p->product_stok <= 0)
    class="mt-auto flex w-full items-center justify-between gap-2 bg-primary px-3 py-2.5 text-on-primary transition-colors duration-200 hover:bg-primary-container disabled:pointer-events-none disabled:opacity-50" title="Tambah ke keranjang">
    <span class="text-sm font-extrabold tracking-tight">{{ formatAngka((int) $p->product_harga, 'Rp ') }}</span>
    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-white/20 shadow-inner">
        <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">add_shopping_cart</span>
    </span>
</button>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Produk Terbaru --}}
    @if($latestProducts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl md:text-2xl font-bold">{{ $settings['latest_title'] }}</h2>
                <a href="{{ route('shop.index') }}" class="text-sm text-primary hover:underline inline-flex items-center gap-1">
                    Lihat Semua <span class="material-symbols-outlined text-base">arrow_forward</span>
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3 sm:gap-4">
                @foreach($latestProducts as $p)
                    <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/60 bg-white shadow-[0_1px_3px_rgba(15,61,17,0.08)] hover:-translate-y-1 hover:border-primary-fixed hover:shadow-[0_14px_30px_-10px_rgba(46,125,50,0.4)] transition-all duration-300">
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="relative block aspect-square overflow-hidden bg-surface-container">
                            @if($p->product_gambar)
                                <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" loading="lazy">
                            @else
                                <div class="w-full h-full grid place-items-center text-outline-variant"><span class="material-symbols-outlined text-4xl">image</span></div>
                            @endif
                        </a>
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="block px-3 pb-2 pt-2.5 text-[13px] font-semibold leading-snug text-on-surface line-clamp-2 min-h-[2.6rem] transition-colors hover:text-primary">{{ $p->product_nama }}</a>
<button type="button" onclick="addToCart({{ $p->id }}, this)" @disabled($p->product_stok <= 0)
    class="mt-auto flex w-full items-center justify-between gap-2 bg-primary px-3 py-2.5 text-on-primary transition-colors duration-200 hover:bg-primary-container disabled:pointer-events-none disabled:opacity-50" title="Tambah ke keranjang">
    <span class="text-sm font-extrabold tracking-tight">{{ formatAngka((int) $p->product_harga, 'Rp ') }}</span>
    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-white/20 shadow-inner">
        <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">add_shopping_cart</span>
    </span>
</button>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Footer --}}
    @include('cms::frontend.layouts.footer')

    <script>
        // Countdown flash sale (server-time based)
        (function () {
            const el = document.getElementById('flash-timer');
            if (!el) return;
            const endsAt = new Date(el.dataset.endsAt).getTime();

            function pad(n) { return String(n).padStart(2, '0'); }

            function tick() {
                const diff = Math.max(0, Math.floor((endsAt - Date.now()) / 1000));
                const h = Math.floor(diff / 3600), m = Math.floor((diff % 3600) / 60), s = diff % 60;
                document.getElementById('ft-h').textContent = pad(h);
                document.getElementById('ft-m').textContent = pad(m);
                document.getElementById('ft-s').textContent = pad(s);
            }
            tick();
            setInterval(tick, 1000);
        })();
    </script>

    @include('ecommerce::components.cart-button')
</body>
</html>
