@php
    $site = \App\Models\WebsiteSetting::merged();
    $footerCfg = config('frontend.footer', []);
    $siteTagline = trim((string) ($footerCfg['tagline'] ?? ''));
    $siteAlamat = trim((string) ($footerCfg['alamat'] ?: ($site['alamat'] ?? '')));
    $siteTelepon = trim((string) ($footerCfg['telepon'] ?: ($site['telepon'] ?? '')));
    $siteEmail = trim((string) ($footerCfg['email'] ?: ($site['email'] ?? '')));
@endphp
<footer class="bg-[#0f1f18] text-white pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        {{-- Mobile: kolom menu dibagi 2 sebaris, kontak full row; desktop: grid 12 --}}
        <div class="grid grid-cols-2 md:grid-cols-12 gap-x-6 gap-y-10">
            @if(($footerMenu ?? null) && $footerMenu->items)
                @php
                    $footerItems = collect($footerMenu->items)->sortBy('sort_order')->values()->take(3);
                    $menuCount = $footerItems->count();
                    // Grid 12 kolom: logo + N kolom menu (2 each) + kontak (3)
                    $logoSpan = max(3, 12 - 3 - ($menuCount * 2));
                @endphp
                <div class="col-span-2 md:col-span-{{ $logoSpan }}">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Juwara Sayur') }}" class="h-12 w-auto">
                    </div>
                    <p class="text-white/60 text-sm leading-relaxed max-w-md">{{ $siteTagline }}</p>
                    <div class="flex gap-3 mt-6">
                        @if($siteEmail !== '')
                            <a href="mailto:{{ $siteEmail }}" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" aria-label="Email"><span class="material-symbols-outlined text-lg">mail</span></a>
                        @endif
                        @if($siteTelepon !== '')
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteTelepon) }}" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" aria-label="Phone"><span class="material-symbols-outlined text-lg">call</span></a>
                        @endif
                    </div>
                </div>
                @foreach($footerItems as $item)
                    <div class="md:col-span-2">
                        <h5 class="font-semibold text-white mb-4">{{ $item['label'] }}</h5>
                        <ul class="space-y-2.5">
                            @foreach(($item['children'] ?? []) as $child)
                                <li><a href="{{ $child['url'] ?? '#' }}" target="{{ $child['target'] ?? '_self' }}" class="text-white/60 text-sm hover:text-white transition-colors">{{ $child['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
                <div class="col-span-2 md:col-span-3">
                    <h5 class="font-semibold text-white mb-4">Kontak</h5>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        @if($siteAlamat !== '')
                            <li class="flex gap-2"><span class="material-symbols-outlined text-base mt-0.5 shrink-0">location_on</span><span>{{ $siteAlamat }}</span></li>
                        @endif
                        @if($siteEmail !== '')
                            <li><a href="mailto:{{ $siteEmail }}" class="hover:text-white transition-colors">{{ $siteEmail }}</a></li>
                        @endif
                        @if($siteTelepon !== '')
                            <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteTelepon) }}" class="hover:text-white transition-colors">{{ $siteTelepon }}</a></li>
                        @endif
                    </ul>
                </div>
            @else
                <div class="col-span-2 md:col-span-3">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Juwara Sayur') }}" class="h-12 w-auto">
                    </div>
                    <p class="text-white/60 text-sm leading-relaxed max-w-md">{{ $siteTagline }}</p>
                    <div class="flex gap-3 mt-6">
                        @if($siteEmail !== '')
                            <a href="mailto:{{ $siteEmail }}" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" aria-label="Email"><span class="material-symbols-outlined text-lg">mail</span></a>
                        @endif
                        @if($siteTelepon !== '')
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteTelepon) }}" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" aria-label="Phone"><span class="material-symbols-outlined text-lg">call</span></a>
                        @endif
                    </div>
                </div>
                <div class="md:col-span-2">
                    <h5 class="font-semibold text-white mb-4">Jelajahi</h5>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        <li><a href="{{ url('/') }}" class="hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="{{ route('shop.index') }}" class="hover:text-white transition-colors">Belanja</a></li>
                        <li><a href="{{ route('blog') }}" class="hover:text-white transition-colors">Blog</a></li>
                    </ul>
                </div>
                <div class="md:col-span-2">
                    <h5 class="font-semibold text-white mb-4">Akun Saya</h5>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        <li><a href="{{ route('cart.index') }}" class="hover:text-white transition-colors">Keranjang</a></li>
                        <li><a href="{{ route('checkout.show') }}" class="hover:text-white transition-colors">Checkout</a></li>
                        <li><a href="{{ route('ecommerce.orders.index') }}" class="hover:text-white transition-colors">Pesanan Saya</a></li>
                    </ul>
                </div>
                <div class="md:col-span-2">
                    <h5 class="font-semibold text-white mb-4">Bantuan</h5>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition-colors">Hubungi Kami</a></li>
                        <li><a href="{{ route('search') }}" class="hover:text-white transition-colors">Pencarian</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Masuk / Daftar</a></li>
                    </ul>
                </div>
                <div class="col-span-2 md:col-span-3">
                    <h5 class="font-semibold text-white mb-4">Kontak</h5>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        @if($siteAlamat !== '')
                            <li class="flex gap-2"><span class="material-symbols-outlined text-base mt-0.5 shrink-0">location_on</span><span>{{ $siteAlamat }}</span></li>
                        @endif
                        @if($siteEmail !== '')
                            <li><a href="mailto:{{ $siteEmail }}" class="hover:text-white transition-colors">{{ $siteEmail }}</a></li>
                        @endif
                        @if($siteTelepon !== '')
                            <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteTelepon) }}" class="hover:text-white transition-colors">{{ $siteTelepon }}</a></li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>
        <div class="mt-12 pt-6 border-t border-white/10 flex flex-col sm:flex-row justify-between gap-3 text-xs text-white/40">
            <span>&copy; {{ date('Y') }} Juwara Sayur. All rights reserved.</span>
            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Juwara Sayur</span>
        </div>
    </div>
</footer>
