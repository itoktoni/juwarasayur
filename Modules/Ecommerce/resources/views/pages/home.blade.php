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
    @php $faviconUrl = \App\Models\WebsiteSetting::fileUrl(\App\Models\WebsiteSetting::merged()['favicon'] ?? null) ?? asset('favicon.ico'); @endphp
    <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
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
            <div class="ml-auto flex items-center gap-2 relative">
                <a href="{{ route('shop.index') }}" class="text-sm text-on-surface hover:text-primary px-2 hidden sm:block">Belanja</a>
                <a href="{{ route('blog') }}" class="text-sm text-on-surface hover:text-primary px-2 hidden sm:block">About</a>
                <a href="{{ route('cart.index') }}" class="relative p-2 rounded-full hover:bg-surface-container" title="Keranjang">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-white text-[10px] font-bold grid place-items-center {{ $cartCount <= 0 ? 'hidden' : '' }}">{{ max($cartCount, 0) }}</span>
                </a>
                @auth
                    <div class="relative" id="user-menu">
                        <button type="button" id="user-menu-btn" class="p-2 rounded-full hover:bg-surface-container flex items-center cursor-pointer" title="Profile" aria-haspopup="true" aria-expanded="false">
                            <span class="material-symbols-outlined">person</span>
                        </button>
                        <div id="user-menu-panel" class="absolute right-0 top-full mt-2 w-52 bg-white border border-outline-variant rounded-xl shadow-lg z-50 overflow-hidden opacity-0 translate-y-2 pointer-events-none transition-all duration-150">
                            <a href="{{ route('account.profile') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface hover:bg-surface-container-low transition-colors">
                                <span class="material-symbols-outlined text-xl text-on-surface-variant">person</span>
                                Profile
                            </a>
                            @if(auth()->user()->isAffiliator())
                                <a href="{{ route('account.dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface hover:bg-surface-container-low transition-colors">
                                    <span class="material-symbols-outlined text-xl text-on-surface-variant">space_dashboard</span> Dashboard
                                </a>
                                <a href="{{ route('account.customers') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface hover:bg-surface-container-low transition-colors">
                                    <span class="material-symbols-outlined text-xl text-on-surface-variant">group</span>
                                    Customer
                                </a>
                            @endif
                            <a href="{{ route('ecommerce.orders.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface hover:bg-surface-container-low transition-colors">
                                <span class="material-symbols-outlined text-xl text-on-surface-variant">receipt_long</span>
                                Pesanan
                            </a>
                            <div class="border-t border-outline-variant">
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-error font-medium hover:bg-error-container/30 transition-colors">
                                        <span class="material-symbols-outlined text-xl">logout</span>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-primary text-on-primary">
        {{-- Dekorasi lingkaran --}}
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-32 -left-16 w-80 h-80 rounded-full bg-black/10"></div>
        <div class="absolute top-1/2 left-1/3 w-40 h-40 rounded-full bg-white/5 hidden md:block"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 text-center">
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight">{{ $settings['hero_title'] }}</h1>
            <p class="mt-4 text-sm md:text-base opacity-90 max-w-2xl mx-auto">{{ $settings['hero_subtitle'] }}</p>

            <div class="mt-7 flex flex-wrap justify-center gap-3">
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
                    @php
                        $harga = (int) $p->product_harga;
                        $resellerPct = $isReseller ? (float) ($p->reseller_fee_percent ?? 0) : 0;
                        $hargaReseller = $resellerPct > 0 ? (int) ($harga * (1 - $resellerPct / 100)) : 0;
                        $showDualPrice = $isReseller && $hargaReseller > 0;
                    @endphp
                    <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/60 bg-white shadow-[0_1px_3px_rgba(15,61,17,0.08)] hover:-translate-y-1 hover:border-primary-fixed hover:shadow-[0_14px_30px_-10px_rgba(46,125,50,0.4)] transition-all duration-300">
                        <span class="absolute left-2 top-2 z-10 inline-flex items-center gap-0.5 rounded-full bg-error px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-widest text-on-error shadow-lg shadow-error/40"><span class="material-symbols-outlined text-[11px]" style="font-variation-settings: 'FILL' 1;">bolt</span>Flash</span>
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="relative block aspect-square overflow-hidden bg-surface-container">
                            @if($p->product_gambar)
                                <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" loading="lazy">
                            @else
                                <div class="w-full h-full grid place-items-center text-outline-variant"><span class="material-symbols-outlined text-4xl">image</span></div>
                            @endif
                        </a>
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="block px-3 pb-1 pt-2.5 text-[13px] font-semibold leading-snug text-on-surface line-clamp-2 min-h-[2.6rem] transition-colors hover:text-primary">{{ $p->product_nama }}</a>
                        <div class="px-3 pb-2 mt-auto">
                            @if($showDualPrice)
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-[11px] text-on-surface-variant line-through">{{ formatAngka($harga, 'Rp ') }}</span>
                                    <span class="text-[9px] font-bold text-on-error bg-error/10 rounded px-1 py-0.5 leading-none">-{{ $resellerPct }}%</span>
                                </div>
                                <p class="text-sm font-extrabold text-primary leading-tight">{{ formatAngka($hargaReseller, 'Rp ') }}</p>
                            @else
                                <p class="text-sm font-extrabold text-primary leading-tight">{{ formatAngka($harga, 'Rp ') }}</p>
                            @endif
                        </div>
                        <button type="button" onclick="addToCart({{ $p->id }}, this)" @disabled($p->product_stok <= 0)
                            class="flex w-full items-center justify-center gap-1.5 bg-primary text-on-primary text-xs font-semibold py-2 transition-colors duration-200 hover:bg-primary-container disabled:pointer-events-none disabled:opacity-50">
                            <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">add_shopping_cart</span> Keranjang
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
                    @php
                        $harga = (int) $p->product_harga;
                        $resellerPct = $isReseller ? (float) ($p->reseller_fee_percent ?? 0) : 0;
                        $hargaReseller = $resellerPct > 0 ? (int) ($harga * (1 - $resellerPct / 100)) : 0;
                        $showDualPrice = $isReseller && $hargaReseller > 0;
                    @endphp
                    <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/60 bg-white shadow-[0_1px_3px_rgba(15,61,17,0.08)] hover:-translate-y-1 hover:border-primary-fixed hover:shadow-[0_14px_30px_-10px_rgba(46,125,50,0.4)] transition-all duration-300">
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="relative block aspect-square overflow-hidden bg-surface-container">
                            @if($p->product_gambar)
                                <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" loading="lazy">
                            @else
                                <div class="w-full h-full grid place-items-center text-outline-variant"><span class="material-symbols-outlined text-4xl">image</span></div>
                            @endif
                        </a>
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="block px-3 pb-1 pt-2.5 text-[13px] font-semibold leading-snug text-on-surface line-clamp-2 min-h-[2.6rem] transition-colors hover:text-primary">{{ $p->product_nama }}</a>
                        <div class="px-3 pb-2 mt-auto">
                            @if($showDualPrice)
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-[11px] text-on-surface-variant line-through">{{ formatAngka($harga, 'Rp ') }}</span>
                                    <span class="text-[9px] font-bold text-on-error bg-error/10 rounded px-1 py-0.5 leading-none">-{{ $resellerPct }}%</span>
                                </div>
                                <p class="text-sm font-extrabold text-primary leading-tight">{{ formatAngka($hargaReseller, 'Rp ') }}</p>
                            @else
                                <p class="text-sm font-extrabold text-primary leading-tight">{{ formatAngka($harga, 'Rp ') }}</p>
                            @endif
                        </div>
                        <button type="button" onclick="addToCart({{ $p->id }}, this)" @disabled($p->product_stok <= 0)
                            class="flex w-full items-center justify-center gap-1.5 bg-primary text-on-primary text-xs font-semibold py-2 transition-colors duration-200 hover:bg-primary-container disabled:pointer-events-none disabled:opacity-50">
                            <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">add_shopping_cart</span> Keranjang
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
                    @php
                        $harga = (int) $p->product_harga;
                        $resellerPct = $isReseller ? (float) ($p->reseller_fee_percent ?? 0) : 0;
                        $hargaReseller = $resellerPct > 0 ? (int) ($harga * (1 - $resellerPct / 100)) : 0;
                        $showDualPrice = $isReseller && $hargaReseller > 0;
                    @endphp
                    <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/60 bg-white shadow-[0_1px_3px_rgba(15,61,17,0.08)] hover:-translate-y-1 hover:border-primary-fixed hover:shadow-[0_14px_30px_-10px_rgba(46,125,50,0.4)] transition-all duration-300">
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="relative block aspect-square overflow-hidden bg-surface-container">
                            @if($p->product_gambar)
                                <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" loading="lazy">
                            @else
                                <div class="w-full h-full grid place-items-center text-outline-variant"><span class="material-symbols-outlined text-4xl">image</span></div>
                            @endif
                        </a>
                        <a href="{{ route('shop.show', $p->product_slug) }}" class="block px-3 pb-1 pt-2.5 text-[13px] font-semibold leading-snug text-on-surface line-clamp-2 min-h-[2.6rem] transition-colors hover:text-primary">{{ $p->product_nama }}</a>
                        <div class="px-3 pb-2 mt-auto">
                            @if($showDualPrice)
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-[11px] text-on-surface-variant line-through">{{ formatAngka($harga, 'Rp ') }}</span>
                                    <span class="text-[9px] font-bold text-on-error bg-error/10 rounded px-1 py-0.5 leading-none">-{{ $resellerPct }}%</span>
                                </div>
                                <p class="text-sm font-extrabold text-primary leading-tight">{{ formatAngka($hargaReseller, 'Rp ') }}</p>
                            @else
                                <p class="text-sm font-extrabold text-primary leading-tight">{{ formatAngka($harga, 'Rp ') }}</p>
                            @endif
                        </div>
                        <button type="button" onclick="addToCart({{ $p->id }}, this)" @disabled($p->product_stok <= 0)
                            class="flex w-full items-center justify-center gap-1.5 bg-primary text-on-primary text-xs font-semibold py-2 transition-colors duration-200 hover:bg-primary-container disabled:pointer-events-none disabled:opacity-50">
                            <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1;">add_shopping_cart</span> Keranjang
                        </button>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Footer --}}
    @include('cms::frontend.layouts.footer')

    <script>
        // Profile dropdown (mirror admin notification dropdown behavior)
        (function () {
            const btn = document.getElementById('user-menu-btn');
            const menu = document.getElementById('user-menu-panel');
            if (!btn || !menu) return;

            const open = () => {
                menu.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
                btn.setAttribute('aria-expanded', 'true');
            };
            const close = () => {
                menu.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
                btn.setAttribute('aria-expanded', 'false');
            };

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.contains('opacity-0') ? open() : close();
            });
            document.addEventListener('click', (e) => {
                if (!document.getElementById('user-menu').contains(e.target)) close();
            });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
        })();
    </script>

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
