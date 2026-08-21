<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product->product_nama }} — {{ config('app.name', 'Mayur') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface antialiased">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-outline-variant">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-sm font-medium hover:underline">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span> Kembali ke katalog
            </a>
            <a href="/dashboard" class="hidden sm:inline-flex btn btn-soft btn-sm">Dashboard</a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <nav class="text-xs text-on-surface-variant mb-4 flex flex-wrap gap-1.5 items-center">
            <a href="{{ route('catalog.index') }}" class="hover:underline">Katalog</a>
            <span>›</span>
            @if($product->has_category)
                <a href="{{ route('catalog.index', ['category' => $product->has_category->category_slug]) }}" class="hover:underline">{{ $product->has_category->category_nama }}</a>
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
                            <a href="{{ route('catalog.index', ['category' => $product->has_category->category_slug]) }}" class="badge badge-neutral text-xs">{{ $product->has_category->category_nama }}</a>
                        @endif
                        @if($product->has_brand)
                            <a href="{{ route('catalog.index', ['brand' => $product->has_brand->brand_slug]) }}" class="badge bg-white border border-outline-variant text-xs">{{ $product->has_brand->brand_nama }}</a>
                        @endif
                        @foreach($product->has_tags as $t)
                            <a href="{{ route('catalog.index', ['tag' => $t->tag_slug]) }}" class="badge text-xs text-white border-transparent" style="background: {{ $t->tag_warna ?? '#64748b' }}">{{ $t->tag_nama }}</a>
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
                        <p class="text-2xl font-bold text-primary">{{ formatAngka((int) $product->product_harga, 'Rp ') }}</p>
                        @if($product->product_harga_grosir)
                            <p class="text-xs text-on-surface-variant">Grosir: {{ formatAngka((int) $product->product_harga_grosir, 'Rp ') }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            <span class="badge {{ $product->product_stok > $product->product_stok_minimum ? 'badge-success' : 'badge-warning' }}">Stok {{ $product->product_stok }}</span>
                            @if($product->has_satuan)<span class="badge badge-neutral">{{ $product->has_satuan->satuan_nama }}</span>@endif
                            @if($product->is_featured)<span class="badge badge-warning">Featured</span>@endif
                        </div>
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
                    <a href="{{ route('catalog.index', ['category' => $product->has_category?->category_slug]) }}" class="btn btn-soft btn-sm">Lihat kategori</a>
                    <a href="{{ route('catalog.index', ['brand' => $product->has_brand?->brand_slug]) }}" class="btn btn-soft btn-sm">Lihat brand</a>
                </div>
            </div>
        </div>

        @if($related->count() > 0)
            <section class="mt-8">
                <h2 class="font-semibold mb-3">Produk terkait</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    @foreach($related as $p)
                        <a href="{{ route('catalog.show', $p->product_slug) }}" class="card overflow-hidden hover:shadow-md transition-shadow">
                            <div class="aspect-[4/3] bg-surface-container overflow-hidden">
                                @if($p->product_gambar)
                                    <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    <div class="w-full h-full grid place-items-center text-outline"><span class="material-symbols-outlined">image</span></div>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="text-xs text-on-surface-variant line-clamp-1">{{ $p->has_category?->category_nama ?? '' }}</p>
                                <p class="font-medium text-sm line-clamp-2 leading-tight">{{ $p->product_nama }}</p>
                                <p class="font-bold text-sm text-primary mt-1">{{ formatAngka((int) $p->product_harga, 'Rp ') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</body>
</html>
