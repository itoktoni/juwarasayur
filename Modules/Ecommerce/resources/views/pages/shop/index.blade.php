@php
    $buildUrl = function(array $overrides = []) use ($q, $sort, $perPage) {
        $params = array_filter([
            'q' => $q !== '' ? $q : null,
            'category' => request('category'),
            'tag' => request('tag'),
            'brand' => request('brand'),
            'sort' => $sort !== 'latest' ? $sort : null,
            'per_page' => $perPage !== 12 ? $perPage : null,
        ]);
        $params = array_merge($params, $overrides);
        foreach ($overrides as $k => $v) {
            if ($v === null) unset($params[$k]);
        }
        return route('shop.index', $params);
    };
    $isFiltered = $q !== '' || $categorySlug !== '' || $tagSlug !== '' || $brandSlug !== '';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Katalog Produk — {{ config('app.name', 'Mayur') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
        @include('ecommerce::components.brand')
</head>
<body class="bg-surface text-on-surface antialiased pb-16 md:pb-0">
    @php
        $cartCount = app(\Modules\Ecommerce\Services\CartService::class)->count();
        $txUrl = auth()->check() ? route('ecommerce.orders.index') : route('login');
        $profileUrl = auth()->check() ? route('account.profile') : route('login');
    @endphp
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-outline-variant">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 py-3">
                <a href="{{ route('home') }}" class="shrink-0" title="Juwara Sayur">
                    <img src="{{ asset('images/logo.png') }}" alt="Juwara Sayur" class="h-10 w-auto">
                </a>
                <form method="GET" action="{{ route('shop.index') }}" class="hidden md:flex flex-1 max-w-xl gap-2">
                    @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                    @if(request('tag'))<input type="hidden" name="tag" value="{{ request('tag') }}">@endif
                    @if(request('brand'))<input type="hidden" name="brand" value="{{ request('brand') }}">@endif
                    @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                    <div class="join flex-1">
                        <input type="search" name="q" value="{{ $q }}" placeholder="Cari produk, kode, atau SKU..." class="input input-sm join-item flex-1" />
                        <button type="submit" class="btn btn-primary btn-sm join-item px-4">Cari</button>
                    </div>
                    @if($q !== '')
                        <a href="{{ $buildUrl(['q' => null]) }}" class="btn btn-soft btn-sm">Reset</a>
                    @endif
                </form>
                <nav class="ml-auto flex items-center gap-1 sm:gap-2">
                    <a href="{{ route('shop.index') }}" class="text-sm font-medium px-2 py-1 rounded-lg {{ request()->is('product') ? 'text-primary' : 'text-on-surface-variant hover:text-primary' }} transition-colors">Katalog</a>
                    <a href="{{ route('blog') }}" class="hidden sm:block text-sm font-medium px-2 py-1 rounded-lg text-on-surface-variant hover:text-primary transition-colors">Blog</a>
                    <a href="{{ route('cart.index') }}" class="relative p-2 rounded-full hover:bg-surface-container" title="Keranjang">
                        <span class="material-symbols-outlined">shopping_cart</span>
                        <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-white text-[10px] font-bold grid place-items-center {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                    </a>
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isDeveloper()))
                        <a href="/dashboard" class="hidden sm:inline-flex btn btn-soft btn-sm ml-1">Dashboard</a>
                    @endif
                </nav>
            </div>
        </div>
    </header>

    {{-- Strip filter mobile: label statis kiri, badge scrollable ke kanan --}}
    <div class="sticky top-[65px] z-20 lg:hidden bg-white/95 backdrop-blur border-b border-outline-variant">
        <div class="max-w-7xl mx-auto px-4 flex items-center gap-3 py-2">
            <button type="button" id="btn-filter" aria-haspopup="dialog" aria-controls="filter-sheet"
                class="shrink-0 inline-flex items-center gap-1 text-sm font-semibold text-on-surface pr-3 border-r border-outline-variant">
                <span class="material-symbols-outlined text-lg">tune</span> Filter
            </button>
            <div class="flex-1 overflow-x-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden" role="region" aria-label="Filter produk">
                <div class="flex items-center gap-1.5 w-max py-0.5">
                    <a href="{{ route('shop.index') }}"
                        class="shrink-0 badge {{ $categorySlug === '' && $brandSlug === '' && $tagSlug === '' ? 'badge-primary' : 'badge-neutral' }} text-xs">Semua</a>
                    @foreach($categories as $cat)
                        @php $active = $categorySlug === $cat->category_slug; @endphp
                        <a href="{{ $active ? $buildUrl(['category' => null]) : $buildUrl(['category' => $cat->category_slug]) }}" aria-pressed="{{ $active ? 'true' : 'false' }}"
                            class="shrink-0 badge {{ $active ? 'badge-primary' : 'bg-white border border-outline-variant text-on-surface' }} text-xs whitespace-nowrap">{{ $cat->category_nama }}</a>
                    @endforeach
                    @foreach($brands as $b)
                        @php $active = $brandSlug === $b->brand_slug; @endphp
                        <a href="{{ $active ? $buildUrl(['brand' => null]) : $buildUrl(['brand' => $b->brand_slug]) }}" aria-pressed="{{ $active ? 'true' : 'false' }}"
                            class="shrink-0 badge {{ $active ? 'badge-primary' : 'bg-white border border-outline-variant text-on-surface' }} text-xs whitespace-nowrap">{{ $b->brand_nama }}</a>
                    @endforeach
                    @foreach($tags as $t)
                        @php $active = $tagSlug === $t->tag_slug; @endphp
                        <a href="{{ $active ? $buildUrl(['tag' => null]) : $buildUrl(['tag' => $t->tag_slug]) }}" aria-pressed="{{ $active ? 'true' : 'false' }}"
                            class="shrink-0 badge text-xs border whitespace-nowrap {{ $active ? 'text-white border-transparent' : 'bg-white text-on-surface border-outline-variant' }}" @if($active && $t->tag_warna) style="background: {{ $t->tag_warna }}" @endif>{{ $t->tag_nama }}</a>
                    @endforeach
                    <span class="shrink-0 w-px h-4 bg-outline-variant mx-1" aria-hidden="true"></span>
                    @foreach(['latest' => 'Terbaru', 'price_asc' => 'Harga ↑', 'price_desc' => 'Harga ↓', 'name_asc' => 'Nama A–Z'] as $k => $label)
                        <a href="{{ $buildUrl(['sort' => $k === 'latest' ? null : $k]) }}" aria-pressed="{{ $sort === $k ? 'true' : 'false' }}"
                            class="shrink-0 badge {{ $sort === $k ? 'badge-secondary' : 'badge-neutral' }} text-xs whitespace-nowrap">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <aside class="hidden lg:block lg:w-64 shrink-0 space-y-4">
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h2 class="font-semibold text-body-sm">Filter</h2>
                            @if($isFiltered)
                                <a href="{{ route('shop.index') }}" class="text-xs text-primary hover:underline">Hapus semua</a>
                            @endif
                        </div>

                        <div class="space-y-5">
                            <div>
                                <h3 class="text-xs font-semibold tracking-wide uppercase text-on-surface-variant mb-2">Kategori</h3>
                                <div class="flex flex-wrap gap-1.5">
                                    <a href="{{ $buildUrl(['category' => null]) }}" class="badge {{ $categorySlug === '' ? 'badge-primary' : 'badge-neutral' }} text-xs">Semua</a>
                                    @foreach($categories as $cat)
                                        @php $active = $categorySlug === $cat->category_slug; @endphp
                                        <a href="{{ $active ? $buildUrl(['category' => null]) : $buildUrl(['category' => $cat->category_slug]) }}" class="badge {{ $active ? 'badge-primary' : 'bg-white border border-outline-variant text-on-surface' }} text-xs hover:opacity-80" title="{{ $cat->category_nama }}">
                                            {{ $cat->category_nama }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-semibold tracking-wide uppercase text-on-surface-variant mb-2">Brand</h3>
                                <div class="flex flex-wrap gap-1.5">
                                    <a href="{{ $buildUrl(['brand' => null]) }}" class="badge {{ $brandSlug === '' ? 'badge-primary' : 'badge-neutral' }} text-xs">Semua</a>
                                    @foreach($brands as $b)
                                        @php $active = $brandSlug === $b->brand_slug; @endphp
                                        <a href="{{ $active ? $buildUrl(['brand' => null]) : $buildUrl(['brand' => $b->brand_slug]) }}" class="badge {{ $active ? 'badge-primary' : 'bg-white border border-outline-variant text-on-surface' }} text-xs">
                                            {{ $b->brand_nama }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-semibold tracking-wide uppercase text-on-surface-variant mb-2">Tag</h3>
                                <div class="flex flex-wrap gap-1.5">
                                    <a href="{{ $buildUrl(['tag' => null]) }}" class="badge {{ $tagSlug === '' ? 'badge-primary' : 'badge-neutral' }} text-xs">Semua</a>
                                    @foreach($tags as $t)
                                        @php $active = $tagSlug === $t->tag_slug; @endphp
                                        <a href="{{ $active ? $buildUrl(['tag' => null]) : $buildUrl(['tag' => $t->tag_slug]) }}" class="badge text-xs border {{ $active ? 'text-white border-transparent' : 'bg-white text-on-surface border-outline-variant' }}" @if($active && $t->tag_warna) style="background: {{ $t->tag_warna }}" @endif>
                                            {{ $t->tag_nama }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-semibold tracking-wide uppercase text-on-surface-variant mb-2">Urutkan</h3>
                                <div class="grid grid-cols-2 gap-1.5">
                                    @foreach(['latest' => 'Terbaru','price_asc' => 'Harga ↑','price_desc' => 'Harga ↓','name_asc' => 'Nama A–Z'] as $k => $label)
                                        <a href="{{ $buildUrl(['sort' => $k === 'latest' ? null : $k]) }}" class="btn btn-sm {{ $sort === $k ? 'btn-primary' : 'btn-soft' }} text-xs">{{ $label }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($isFiltered)
                    <div class="card bg-primary-container/40 border-primary/20">
                        <div class="card-body py-3">
                            <p class="text-xs font-medium mb-1.5">Filter aktif</p>
                            <div class="flex flex-wrap gap-1.5">
                                @if($q !== '')<span class="badge badge-info text-xs">q: {{ Str::limit($q, 18) }}</span>@endif
                                @if($activeCategory)<span class="badge badge-success text-xs">{{ $activeCategory->category_nama }}</span>@endif
                                @if($activeBrand)<span class="badge badge-success text-xs">{{ $activeBrand->brand_nama }}</span>@endif
                                @if($activeTag)<span class="badge text-xs text-white" style="background: {{ $activeTag->tag_warna ?? '#64748b' }}">{{ $activeTag->tag_nama }}</span>@endif
                            </div>
                        </div>
                    </div>
                @endif
            </aside>

            <section class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <p class="text-sm text-on-surface-variant">
                        Menampilkan <span class="font-semibold text-on-surface">{{ $products->total() }}</span> produk
                        @if($isFiltered) <span class="opacity-70">· hasil filter</span> @endif
                    </p>
                    <form method="GET" class="flex items-center gap-2 text-xs">
                        @foreach(request()->except('per_page') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                        <label class="text-on-surface-variant">Per/Halaman</label>
                        <select name="per_page" onchange="this.form.submit()" class="select select-sm w-auto">
                            @foreach([12,24,36,48] as $n)
                                <option value="{{ $n }}" @selected($perPage===$n)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if($products->count() === 0)
                    <div class="card">
                        <div class="card-body py-12 text-center">
                            <span class="material-symbols-outlined text-5xl text-outline mb-3">search_off</span>
                            <p class="font-medium">Tidak ada produk ditemukan</p>
                            <p class="text-sm text-on-surface-variant mt-1">Coba ubah kata kunci atau filter.</p>
                            <a href="{{ route('shop.index') }}" class="btn btn-primary btn-sm mt-4">Lihat semua produk</a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                        @foreach($products as $p)
                            @php
                                $harga = (int) $p->product_harga;
                                $resellerPct = $isReseller ? (float) ($p->reseller_fee_percent ?? 0) : 0;
                                $hargaReseller = $resellerPct > 0 ? (int) ($harga * (1 - $resellerPct / 100)) : 0;
                                $showDualPrice = $isReseller && $hargaReseller > 0;
                            @endphp
                            <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/60 bg-white shadow-[0_1px_3px_rgba(15,61,17,0.08)] hover:-translate-y-1 hover:border-primary-fixed hover:shadow-[0_14px_30px_-10px_rgba(46,125,50,0.4)] transition-all duration-300">
                                <a href="{{ route('shop.show', $p->product_slug) }}" class="aspect-square bg-surface-container overflow-hidden relative block">
                                    @if($p->product_gambar)
                                        <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" loading="lazy">
                                    @else
                                        <div class="w-full h-full grid place-items-center text-outline">
                                            <span class="material-symbols-outlined text-4xl">image</span>
                                        </div>
                                    @endif
                                    @if($p->is_featured)
                                        <span class="absolute left-2 top-2 z-10 inline-flex items-center gap-0.5 rounded-full bg-secondary px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-widest text-on-secondary shadow-lg shadow-secondary/40"><span class="material-symbols-outlined text-[11px]" style="font-variation-settings: 'FILL' 1;">star</span>Featured</span>
                                    @endif
                                    @if($p->product_stok <= 0)
                                        <span class="absolute right-2 top-2 z-10 rounded-full bg-error px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-widest text-on-error shadow-lg shadow-error/40">Habis</span>
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

                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                @endif
            </section>
        </div>
    </main>

    @include('cms::frontend.layouts.footer')

    {{-- Bottom sheet filter (mobile) --}}
    <div id="filter-sheet" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Filter produk" data-open="false">
        <div class="absolute inset-0 bg-black/40 transition-opacity duration-300 opacity-0" id="filter-backdrop"></div>
        <form method="GET" action="{{ route('shop.index') }}" id="filter-form"
            class="absolute bottom-0 inset-x-0 bg-surface rounded-t-2xl shadow-[0_-8px_30px_rgba(0,0,0,0.15)] flex flex-col max-h-[85dvh] translate-y-full transition-transform duration-300 ease-out">
            <input type="hidden" name="q" value="{{ $q }}">

            <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-outline-variant">
                <h2 class="text-base font-bold text-on-surface">Filter</h2>
                <button type="button" id="filter-close" class="p-1.5 rounded-full hover:bg-surface-container text-on-surface-variant" aria-label="Tutup">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">
                <fieldset>
                    <legend class="text-xs font-semibold tracking-wide uppercase text-on-surface-variant mb-2">Kategori</legend>
                    <div class="flex flex-wrap gap-1.5">
                        <label class="badge cursor-pointer has-[:checked]:badge-primary border border-outline-variant text-xs">
                            <input type="radio" name="category" value="" class="sr-only" @checked($categorySlug === '')> Semua
                        </label>
                        @foreach($categories as $cat)
                            <label class="badge cursor-pointer has-[:checked]:badge-primary border border-outline-variant text-xs">
                                <input type="radio" name="category" value="{{ $cat->category_slug }}" class="sr-only" @checked($categorySlug === $cat->category_slug)> {{ $cat->category_nama }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-xs font-semibold tracking-wide uppercase text-on-surface-variant mb-2">Brand</legend>
                    <div class="flex flex-wrap gap-1.5">
                        <label class="badge cursor-pointer has-[:checked]:badge-primary border border-outline-variant text-xs">
                            <input type="radio" name="brand" value="" class="sr-only" @checked($brandSlug === '')> Semua
                        </label>
                        @foreach($brands as $b)
                            <label class="badge cursor-pointer has-[:checked]:badge-primary border border-outline-variant text-xs">
                                <input type="radio" name="brand" value="{{ $b->brand_slug }}" class="sr-only" @checked($brandSlug === $b->brand_slug)> {{ $b->brand_nama }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-xs font-semibold tracking-wide uppercase text-on-surface-variant mb-2">Tag</legend>
                    <div class="flex flex-wrap gap-1.5">
                        <label class="badge cursor-pointer has-[:checked]:badge-primary border border-outline-variant text-xs">
                            <input type="radio" name="tag" value="" class="sr-only" @checked($tagSlug === '')> Semua
                        </label>
                        @foreach($tags as $t)
                            <label class="badge cursor-pointer border text-xs {{ $tagSlug === $t->tag_slug ? 'text-white border-transparent' : 'border-outline-variant text-on-surface has-[:checked]:text-white has-[:checked]:border-transparent' }}"
                                @if($tagSlug === $t->tag_slug && $t->tag_warna) style="background: {{ $t->tag_warna }}" @endif>
                                <input type="radio" name="tag" value="{{ $t->tag_slug }}" class="sr-only"
                                    data-color="{{ $t->tag_warna }}" @checked($tagSlug === $t->tag_slug)> {{ $t->tag_nama }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-xs font-semibold tracking-wide uppercase text-on-surface-variant mb-2">Urutkan</legend>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach(['latest' => 'Terbaru', 'price_asc' => 'Harga ↑', 'price_desc' => 'Harga ↓', 'name_asc' => 'Nama A–Z'] as $k => $label)
                            <label class="btn btn-sm cursor-pointer justify-center {{ $sort === $k ? 'btn-primary' : 'btn-soft' }} text-xs has-[:checked]:btn-primary">
                                <input type="radio" name="sort" value="{{ $k === 'latest' ? '' : $k }}" class="sr-only" @checked($sort === $k)> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>

            <div class="sticky bottom-0 flex items-center gap-2 px-5 py-3 border-t border-outline-variant bg-surface pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                <a href="{{ route('shop.index') }}" class="btn btn-soft btn-sm h-11 px-4">Reset</a>
                <button type="submit" class="btn btn-primary flex-1 h-11 justify-center font-semibold">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const sheet = document.getElementById('filter-sheet');
            if (!sheet) return;
            const backdrop = document.getElementById('filter-backdrop');
            const panel = sheet.querySelector('form');
            const btnOpen = document.getElementById('btn-filter');
            const btnClose = document.getElementById('filter-close');

            function open() {
                sheet.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                requestAnimationFrame(() => {
                    backdrop.classList.remove('opacity-0');
                    panel.classList.remove('translate-y-full');
                });
                sheet.dataset.open = 'true';
            }

            function close() {
                backdrop.classList.add('opacity-0');
                panel.classList.add('translate-y-full');
                document.body.style.overflow = '';
                sheet.dataset.open = 'false';
                setTimeout(() => sheet.classList.add('hidden'), 300);
            }

            btnOpen?.addEventListener('click', open);
            btnClose?.addEventListener('click', close);
            backdrop.addEventListener('click', close);
            document.addEventListener('keydown', e => { if (e.key === 'Escape' && sheet.dataset.open === 'true') close(); });

            // Jangan kirim param kosong (category=, brand=, dst.)
            panel?.addEventListener('submit', () => {
                panel.querySelectorAll('input[type="radio"][value=""]').forEach(i => { i.disabled = true; });
            });

            // Warna tag saat dipilih di sheet
            panel?.querySelectorAll('input[name="tag"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    panel.querySelectorAll('input[name="tag"]').forEach(r => {
                        const label = r.closest('label');
                        if (!r.dataset.color) return;
                        label.style.background = (r.checked && r.dataset.color) ? r.dataset.color : '';
                    });
                });
            });
        })();
    </script>


    {{-- Bottom bar navigasi mobile --}}
    <nav class="fixed bottom-0 inset-x-0 z-40 md:hidden bg-white border-t border-outline-variant pb-[env(safe-area-inset-bottom)]" aria-label="Navigasi utama">
        <div class="grid grid-cols-4 h-16 max-w-lg mx-auto">
            @php
                $items = [
                    ['href' => route('home'), 'label' => 'Home', 'icon' => 'home', 'active' => request()->routeIs('home')],
                    ['href' => route('cart.index'), 'label' => 'Keranjang', 'icon' => 'shopping_cart', 'active' => request()->routeIs('cart.*') || request()->routeIs('checkout.*') || request()->routeIs('payment.*')],
                    ['href' => $txUrl, 'label' => 'Transaksi', 'icon' => 'receipt_long', 'active' => request()->routeIs('ecommerce.orders.*')],
                    ['href' => $profileUrl, 'label' => 'Profile', 'icon' => 'person', 'active' => request()->routeIs('profile.*')],
                ];
            @endphp
            @foreach($items as $item)
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
</body>
</html>
