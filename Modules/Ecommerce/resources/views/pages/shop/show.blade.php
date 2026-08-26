<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product->product_nama }} — {{ config('app.name', 'Mayur') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
        @include('ecommerce::components.brand')
</head>
<body class="bg-surface text-on-surface antialiased">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-outline-variant">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="shrink-0" title="Juwara Sayur">
                <img src="{{ asset('images/logo.png') }}" alt="Juwara Sayur" class="h-10 w-auto">
            </a>
            <a href="{{ route('shop.index') }}" class="hidden md:inline-flex items-center gap-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span> Kembali ke katalog
            </a>
            <nav class="ml-auto flex items-center gap-1 sm:gap-2">
                <a href="{{ route('shop.index') }}" class="text-sm font-medium px-2 py-1 rounded-lg {{ request()->is('product') ? 'text-primary' : 'text-on-surface-variant hover:text-primary' }} transition-colors">Katalog</a>
                <a href="{{ route('blog') }}" class="hidden sm:block text-sm font-medium px-2 py-1 rounded-lg text-on-surface-variant hover:text-primary transition-colors">Blog</a>
                {{-- Keranjang bisa dipakai guest (session) maupun login (DB) via CartService --}}
                <a href="{{ route('cart.index') }}" class="relative p-2 rounded-full hover:bg-surface-container" title="Keranjang">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    @php $cartCount = app(\Modules\Ecommerce\Services\CartService::class)->count(); @endphp
                    <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-on-error text-[10px] font-bold grid place-items-center {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                </a>
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isDeveloper()))
                    <a href="/dashboard" class="hidden sm:inline-flex btn btn-soft btn-sm ml-1">Dashboard</a>
                @endif
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <nav class="text-xs text-on-surface-variant mb-4 flex flex-wrap gap-1.5 items-center">
            <a href="{{ route('shop.index') }}" class="hover:underline">Katalog</a>
            <span>›</span>
            @if($product->has_category)
                <a href="{{ route('shop.index', ['category' => $product->has_category->category_slug]) }}" class="hover:underline">{{ $product->has_category->category_nama }}</a>
                <span>›</span>
            @endif
            <span class="text-on-surface font-medium line-clamp-1">{{ $product->product_nama }}</span>
        </nav>

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="card overflow-hidden">
                <div class="aspect-[4/3] bg-surface-container overflow-hidden">
                    @if($product->product_gambar)
                        <img src="{{ $product->product_gambar_url }}" alt="{{ $product->product_nama }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full grid place-items-center text-outline">
                            <span class="material-symbols-outlined text-6xl">image</span>
                        </div>
                    @endif
                </div>
                @if(!empty($product->product_galeri) && is_array($product->product_galeri))
                    <div class="p-3 grid grid-cols-4 gap-2">
                        @foreach(array_slice($product->product_galeri, 0, 8) as $g)
                            <img src="{{ fileUrl($g) }}" alt="" class="w-full aspect-square object-cover rounded border border-outline-variant">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div>
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        @if($product->has_category)
                            <a href="{{ route('shop.index', ['category' => $product->has_category->category_slug]) }}" class="badge badge-neutral text-xs">{{ $product->has_category->category_nama }}</a>
                        @endif
                        @if($product->has_brand)
                            <a href="{{ route('shop.index', ['brand' => $product->has_brand->brand_slug]) }}" class="badge bg-white border border-outline-variant text-xs">{{ $product->has_brand->brand_nama }}</a>
                        @endif
                        @foreach($product->has_tags as $t)
                            <a href="{{ route('shop.index', ['tag' => $t->tag_slug]) }}" class="badge text-xs text-white border-transparent" style="background: {{ $t->tag_warna ?? '#64748b' }}">{{ $t->tag_nama }}</a>
                        @endforeach
                    </div>
                    <h1 class="text-headline-lg-mobile lg:text-headline-lg font-bold leading-tight">{{ $product->product_nama }}</h1>
                    <p class="text-sm text-on-surface-variant mt-1">
                        SKU: <span class="font-mono">{{ $product->product_sku ?? $product->product_kode ?? '-' }}</span>
                        @if($product->product_barcode) · Barcode: {{ $product->product_barcode }} @endif
                    </p>
                </div>

                    <div class="card">
                    <div class="card-body">
                        @php
                            $harga = (int) $product->product_harga;
                            $resellerPct = $isReseller ? (float) ($product->reseller_fee_percent ?? 0) : 0;
                            $hargaReseller = $resellerPct > 0 ? (int) ($harga * (1 - $resellerPct / 100)) : 0;
                        @endphp
                        @if($isReseller && $hargaReseller > 0)
                            <p class="text-sm text-on-surface-variant line-through">{{ formatAngka($harga, 'Rp ') }}</p>
                            <p class="text-2xl font-bold text-primary">{{ formatAngka($hargaReseller, 'Rp ') }}</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">Harga reseller (diskon {{ $resellerPct }}%)</p>
                        @else
                            <p class="text-2xl font-bold text-primary">{{ formatAngka($harga, 'Rp ') }}</p>
                        @endif
                        @if($product->product_harga_grosir)
                            <p class="text-xs text-on-surface-variant">Grosir: {{ formatAngka((int) $product->product_harga_grosir, 'Rp ') }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            <span class="badge {{ $product->product_stok > $product->product_stok_minimum ? 'badge-success' : 'badge-warning' }}">Stok {{ $product->product_stok }}</span>
                            @if($product->has_satuan)<span class="badge badge-neutral">{{ $product->has_satuan->satuan_nama }}</span>@endif
                            @if($product->is_featured)<span class="badge badge-warning">Featured</span>@endif
                        </div>
                        {{-- Guest & login sama-sama bisa tambah ke keranjang (cart guest berbasis session) --}}
                        <button type="button" onclick="addToCart({{ $product->id }}, this)" @disabled($product->product_stok <= 0)
                            class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary text-on-primary text-sm font-semibold py-3 hover:opacity-90 transition-opacity disabled:opacity-50 disabled:pointer-events-none">
                            <span class="material-symbols-outlined text-base">add_shopping_cart</span> Tambah ke Keranjang
                        </button>
                        @if($product->product_berat || $product->product_panjang)
                            <p class="text-xs text-on-surface-variant mt-2">
                                @if($product->product_berat) Berat {{ $product->product_berat }} kg @endif
                                @if($product->product_panjang) · {{ $product->product_panjang }}×{{ $product->product_lebar }}×{{ $product->product_tinggi }} cm @endif
                            </p>
                        @endif
                    </div>
                </div>

                @if($product->product_deskripsi)
                    <div class="card">
                        <div class="card-body">
                            <h2 class="font-semibold mb-2">Deskripsi</h2>
                            <p class="text-sm leading-relaxed text-on-surface-variant">{{ $product->product_deskripsi }}</p>
                        </div>
                    </div>
                @endif

                @if($product->product_deskripsi_lengkap)
                    <div class="card">
                        <div class="card-body prose prose-sm max-w-none">
                            {!! $product->product_deskripsi_lengkap !!}
                        </div>
                    </div>
                @endif

                <div class="flex gap-2">
                    <a href="{{ route('shop.index', ['category' => $product->has_category?->category_slug]) }}" class="btn btn-soft btn-sm">Lihat kategori</a>
                    <a href="{{ route('shop.index', ['brand' => $product->has_brand?->brand_slug]) }}" class="btn btn-soft btn-sm">Lihat brand</a>
                </div>
            </div>
        </div>

        @if($related->count() > 0)
            <section class="mt-8">
                <h2 class="font-semibold mb-3">Produk terkait</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    @foreach($related as $p)
                        @php
                            $relHarga = (int) $p->product_harga;
                            $relPct = $isReseller ? (float) ($p->reseller_fee_percent ?? 0) : 0;
                            $relHargaReseller = $relPct > 0 ? (int) ($relHarga * (1 - $relPct / 100)) : 0;
                            $relShowDual = $isReseller && $relHargaReseller > 0;
                        @endphp
                        <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant/60 bg-white shadow-[0_1px_3px_rgba(15,61,17,0.08)] hover:-translate-y-1 hover:border-primary-fixed hover:shadow-[0_14px_30px_-10px_rgba(46,125,50,0.4)] transition-all duration-300">
                            <a href="{{ route('shop.show', $p->product_slug) }}" class="aspect-[4/3] bg-surface-container overflow-hidden block">
                                @if($p->product_gambar)
                                    <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" loading="lazy">
                                @else
                                    <div class="w-full h-full grid place-items-center text-outline"><span class="material-symbols-outlined">image</span></div>
                                @endif
                            </a>
                            <div class="px-3 pb-1 pt-2.5 flex-1">
                                <p class="text-xs text-on-surface-variant line-clamp-1">{{ $p->has_category?->category_nama ?? '' }}</p>
                                <a href="{{ route('shop.show', $p->product_slug) }}" class="font-medium text-sm line-clamp-2 leading-tight hover:text-primary transition-colors">{{ $p->product_nama }}</a>
                            </div>
                            <div class="px-3 pb-2 mt-auto">
                                @if($relShowDual)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[11px] text-on-surface-variant line-through">{{ formatAngka($relHarga, 'Rp ') }}</span>
                                        <span class="text-[9px] font-bold text-on-error bg-error/10 rounded px-1 py-0.5 leading-none">-{{ $relPct }}%</span>
                                    </div>
                                    <p class="text-sm font-extrabold text-primary leading-tight">{{ formatAngka($relHargaReseller, 'Rp ') }}</p>
                                @else
                                    <p class="text-sm font-extrabold text-primary leading-tight">{{ formatAngka($relHarga, 'Rp ') }}</p>
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
    </main>
    @include('ecommerce::components.cart-button')

</body>
</html>
