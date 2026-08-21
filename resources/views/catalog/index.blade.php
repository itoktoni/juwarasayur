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
        return route('catalog.index', $params);
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
</head>
<body class="bg-surface text-on-surface antialiased">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-outline-variant">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 py-3">
                <a href="{{ route('catalog.index') }}" class="flex items-center gap-2 font-bold text-headline-md tracking-tight">
                    <span class="material-symbols-outlined text-primary">storefront</span>
                    Katalog
                </a>
                <form method="GET" action="{{ route('catalog.index') }}" class="flex-1 max-w-xl flex gap-2">
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
                <a href="/dashboard" class="hidden sm:inline-flex btn btn-soft btn-sm">Dashboard</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:flex-row gap-6">
            <aside class="lg:w-64 shrink-0 space-y-4">
                <div class="card">
                    <div class="card-body">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h2 class="font-semibold text-body-sm">Filter</h2>
                            @if($isFiltered)
                                <a href="{{ route('catalog.index') }}" class="text-xs text-primary hover:underline">Hapus semua</a>
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
                        <label class="text-on-surface-variant">Per halaman</label>
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
                            <a href="{{ route('catalog.index') }}" class="btn btn-primary btn-sm mt-4">Lihat semua produk</a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                        @foreach($products as $p)
                            <a href="{{ route('catalog.show', $p->product_slug) }}" class="group card overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
                                <div class="aspect-[4/3] bg-surface-container overflow-hidden relative">
                                    @if($p->product_gambar)
                                        <img src="{{ $p->product_gambar_url }}" alt="{{ $p->product_nama }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300" loading="lazy">
                                    @else
                                        <div class="w-full h-full grid place-items-center text-outline">
                                            <span class="material-symbols-outlined text-4xl">image</span>
                                        </div>
                                    @endif
                                    @if($p->is_featured)
                                        <span class="absolute top-2 left-2 badge badge-warning text-[10px]">Featured</span>
                                    @endif
                                    @if($p->product_stok <= 0)
                                        <span class="absolute top-2 right-2 badge badge-danger text-[10px]">Habis</span>
                                    @endif
                                </div>
                                <div class="p-3 flex flex-col gap-1.5 flex-1">
                                    <div class="flex flex-wrap gap-1">
                                        @if($p->has_category)<span class="badge badge-neutral text-[10px] leading-none px-2 py-1">{{ $p->has_category->category_nama }}</span>@endif
                                        @if($p->has_brand)<span class="badge bg-white border border-outline-variant text-[10px] leading-none px-2 py-1">{{ $p->has_brand->brand_nama }}</span>@endif
                                    </div>
                                    <h3 class="font-medium text-sm leading-tight line-clamp-2 min-h-[2.5rem]">{{ $p->product_nama }}</h3>
                                    <p class="text-xs text-on-surface-variant line-clamp-1">{{ $p->has_tags->pluck('tag_nama')->implode(' · ') }}</p>
                                    <div class="mt-auto pt-1 flex items-baseline justify-between gap-2">
                                        <span class="font-bold text-sm text-primary">{{ formatAngka((int) $p->product_harga, 'Rp ') }}</span>
                                        <span class="text-[11px] text-on-surface-variant">Stok {{ $p->product_stok }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                @endif
            </section>
        </div>
    </main>
</body>
</html>
