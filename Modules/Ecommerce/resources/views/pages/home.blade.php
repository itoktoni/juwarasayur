@php
    $siteName = config('app.name', 'Mayur');
    $cartCount = auth()->check() ? \Modules\Ecommerce\Models\CartItem::where('user_id', auth()->id())->sum('qty') : 0;
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
                @auth
                    <a href="{{ route('cart.index') }}" class="relative p-2 rounded-full hover:bg-surface-container" title="Keranjang">
                        <span class="material-symbols-outlined">shopping_cart</span>
                        <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-on-error text-[10px] font-bold grid place-items-center {{ $cartCount <= 0 ? 'hidden' : '' }}">{{ max($cartCount, 0) }}</span>
                    </a>
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
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                @foreach($flashSaleProducts as $p)
                    <div class="card overflow-hidden border border-outline-variant bg-white rounded-xl flex flex-col hover:shadow-lg transition-shadow relative">
                        <span class="absolute top-2 left-2 z-10 badge badge-danger text-[10px] px-2 py-0.5 rounded-full bg-error text-on-error">FLASH</span>
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="aspect-square bg-surface-container overflow-hidden block">
                            @if($p->product_gambar)
                                <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="w-full h-full object-cover hover:scale-[1.03] transition-transform duration-300" loading="lazy">
                            @else
                                <div class="w-full h-full grid place-items-center text-outline-variant"><span class="material-symbols-outlined text-4xl">image</span></div>
                            @endif
                        </a>
                        <div class="p-2.5 flex flex-col gap-1 flex-1">
                            <a href="{{ route('shop.show', $p->product_slug) }}" class="text-xs font-medium leading-tight line-clamp-2 min-h-[2rem] hover:text-primary">{{ $p->product_nama }}</a>
                            <div class="mt-auto pt-1">
                                <p class="font-bold text-sm text-primary">{{ formatAngka((int) $p->product_harga, 'Rp ') }}</p>
                                @auth
                                    <button type="button" onclick="addToCart({{ $p->id }}, this)" @disabled($p->product_stok <= 0)
                                        class="mt-1.5 w-full inline-flex items-center justify-center gap-1 rounded-lg bg-primary text-on-primary text-xs font-semibold py-1.5 hover:opacity-90 transition-opacity disabled:opacity-50 disabled:pointer-events-none">
                                        <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                                    </button>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Produk Terbaru --}}
    @if($latestProducts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl md:text-2xl font-bold">{{ $settings['latest_title'] }}</h2>
                <a href="{{ route('shop.index') }}" class="text-sm text-primary hover:underline inline-flex items-center gap-1">
                    Lihat Semua <span class="material-symbols-outlined text-base">arrow_forward</span>
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3">
                @foreach($latestProducts as $p)
                    <div class="card overflow-hidden border border-outline-variant bg-white rounded-xl flex flex-col hover:shadow-md transition-shadow">
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="aspect-square bg-surface-container overflow-hidden block">
                            @if($p->product_gambar)
                                <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="w-full h-full object-cover hover:scale-[1.03] transition-transform duration-300" loading="lazy">
                            @else
                                <div class="w-full h-full grid place-items-center text-outline-variant"><span class="material-symbols-outlined text-4xl">image</span></div>
                            @endif
                        </a>
                        <div class="p-2.5 flex flex-col gap-1 flex-1">
                            <a href="{{ route('shop.show', $p->product_slug) }}" class="text-xs font-medium leading-tight line-clamp-2 min-h-[2rem] hover:text-primary">{{ $p->product_nama }}</a>
                            <div class="mt-auto pt-1 flex items-center justify-between gap-1">
                                <p class="font-bold text-xs text-primary">{{ formatAngka((int) $p->product_harga, 'Rp ') }}</p>
                                @auth
                                    <button type="button" onclick="addToCart({{ $p->id }}, this)" @disabled($p->product_stok <= 0)
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-on-primary transition-colors disabled:opacity-50 disabled:pointer-events-none" title="Tambah ke keranjang">
                                        <span class="material-symbols-outlined text-sm">add_shopping_cart</span>
                                    </button>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Footer --}}
    <footer class="border-t border-outline-variant py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Juwara Sayur" class="h-9 w-auto">
                <span class="text-xs text-on-surface-variant">Sayur & Sembako Segar</span>
            </div>
            <div class="flex items-center gap-5 text-sm text-on-surface-variant">
                <a href="{{ route('shop.index') }}" class="hover:text-primary transition-colors">Belanja</a>
                <a href="{{ route('blog') }}" class="hover:text-primary transition-colors">Blog</a>
                <a href="{{ route('cart.index') }}" class="hover:text-primary transition-colors">Keranjang</a>
                <a href="{{ route('contact') }}" class="hover:text-primary transition-colors">Kontak</a>
            </div>
        </div>
        <p class="text-center text-[11px] text-on-surface-variant/70 mt-6">© {{ now()->year }} Juwara Sayur. All rights reserved.</p>
    </footer>

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

    @auth
        @include('ecommerce::components.cart-button')
    @endauth
</body>
</html>
