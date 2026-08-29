@extends('cms::frontend.layouts.public')

@section('title', isset($category) ? 'Berita - ' . $category->name : (isset($tag) ? 'Berita - ' . $tag->name : 'Blog & Profil Bisnis'))

@php
    $site = \App\Models\WebsiteSetting::merged();
    $logoUrl = \App\Models\WebsiteSetting::fileUrl($site['logo'] ?? null);
@endphp

@section('content')
{{-- ===== PROFIL BISNIS ===== --}}
<section class="relative overflow-hidden bg-gradient-to-br from-primary via-primary to-green-700 text-on-primary pt-20">
    {{-- Dekorasi lingkaran --}}
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/10"></div>
    <div class="absolute -bottom-32 -left-16 w-80 h-80 rounded-full bg-black/10"></div>

    <div class="relative max-w-7xl mx-auto px-8 py-16">
        <div class="flex flex-col lg:flex-row lg:items-center gap-10">
            {{-- Logo + Nama --}}
            <div class="flex items-center gap-5 shrink-0">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $site['name'] ?? '' }}" class="h-20 w-20 rounded-2xl object-contain bg-white p-2 shadow-lg" />
                @else
                    <div class="h-20 w-20 rounded-2xl bg-white/20 backdrop-blur grid place-items-center shadow-lg">
                        <span class="material-symbols-outlined text-4xl">storefront</span>
                    </div>
                @endif
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">{{ $site['name'] ?? config('app.name') }}</h1>
                    <p class="text-white/80 font-medium mt-1">{{ $site['tagline'] }}</p>
                </div>
            </div>

            {{-- Deskripsi --}}
            <div class="lg:border-l lg:border-white/25 lg:pl-10 flex-1">
                <div class="wysiwyg-content text-white/90 leading-relaxed max-w-2xl
                    [&_a]:underline [&_a]:text-white [&_a]:font-medium
                    [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-1
                    [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:mb-2 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mb-2 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:mb-1
                    [&_p]:mb-2 [&_p:last-child]:mb-0 [&_img]:rounded-lg [&_img]:my-2 [&_img]:max-w-full [&_img]:h-auto">{!! $site['description'] !!}</div>
                <div class="flex flex-wrap gap-2 mt-5">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur text-sm">
                        <span class="material-symbols-outlined text-base">location_on</span>
                        {{ $site['alamat'] }}
                    </span>
                    <a href="tel:{{ $site['telepon'] }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur text-sm hover:bg-white/25 transition-colors">
                        <span class="material-symbols-outlined text-base">call</span>
                        {{ $site['telepon'] }}
                    </a>
                    <a href="mailto:{{ $site['email'] }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur text-sm hover:bg-white/25 transition-colors">
                        <span class="material-symbols-outlined text-base">mail</span>
                        {{ $site['email'] }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== ARTIKEL / BERITA ===== --}}
<section class="py-16 bg-surface-container-highest min-h-[50vh]">
    <div class="max-w-7xl mx-auto px-8">
        {{-- Header artikel --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
            <div>
                <p class="text-primary font-bold text-sm uppercase tracking-widest mb-2">Blog & Berita</p>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">
                    @if(isset($category))
                        Kategori: {{ $category->name }}
                    @elseif(isset($tag))
                        Tag: {{ $tag->name }}
                    @else
                        Artikel Terbaru
                    @endif
                </h2>
                <p class="text-on-surface-variant mt-2">Informasi terkini seputar bisnis dan tips sayuran segar</p>
            </div>

            {{-- Search Bar --}}
            <form action="{{ route('search') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari artikel..."
                    class="flex-1 md:w-64 px-5 py-3 bg-white border border-outline-variant rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all font-body-md" />
                <button type="submit" class="bg-primary text-on-primary px-5 py-3 rounded-xl hover:opacity-90 active:scale-95 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg">search</span>
                    <span class="hidden sm:inline">Cari</span>
                </button>
            </form>
        </div>

        {{-- Posts Grid --}}
        @if($posts->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($posts as $post)
                    <a href="{{ route('blog.post', $post->slug) }}" class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group border border-outline-variant/30">
                        @if(!empty($post->featured_image))
                            <div class="h-52 overflow-hidden">
                                <img src="{{ fileUrl($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                            </div>
                        @else
                            <div class="h-52 bg-gradient-to-br from-primary/15 to-green-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-5xl text-primary/50">article</span>
                            </div>
                        @endif
                        <div class="p-6">
                            <div class="flex items-center gap-3 text-xs text-outline mb-3">
                                @if($post->published_at)
                                    <span class="inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">calendar_today</span>
                                        {{ $post->published_at->format('d M Y') }}
                                    </span>
                                @endif
                                @if($post->has_type)
                                    <span class="w-1 h-1 bg-outline rounded-full"></span>
                                    <span class="text-primary font-semibold uppercase tracking-wide">{{ $post->has_type->name }}</span>
                                @endif
                            </div>
                            <h3 class="font-headline-md text-headline-md text-on-surface mb-3 group-hover:text-primary transition-colors line-clamp-2">{{ $post->title }}</h3>
                            @if($post->excerpt)
                                <p class="text-on-surface-variant text-sm line-clamp-3">{{ $post->excerpt }}</p>
                            @endif
                            <div class="mt-4 flex items-center gap-2 text-primary font-label-md text-sm font-medium">
                                Baca Selengkapnya <span class="material-symbols-outlined text-lg group-hover:translate-x-1 transition-transform">arrow_right_alt</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-12 flex justify-center">
                {{ $posts->links() }}
            </div>
        @else
            <div class="text-center py-20">
                <span class="material-symbols-outlined text-6xl text-outline mb-4">newspaper</span>
                <h3 class="font-headline-md text-headline-md text-on-surface mb-2">Belum Ada Artikel</h3>
                <p class="text-on-surface-variant">Belum ada artikel yang tersedia untuk saat ini.</p>
            </div>
        @endif
    </div>
</section>
@endsection
