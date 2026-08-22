<footer class="bg-[#0f1f18] text-white pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-6 md:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
            <div class="md:col-span-5">
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Juwara Sayur') }}" class="h-12 w-auto">
                </div>
                <p class="text-white/60 text-sm leading-relaxed max-w-md">Sayur-mayur &amp; bahan dapur segar — sayur, telur, ikan, ayam, daging. Pasar ke dapur Anda, setiap hari.</p>
                <div class="flex gap-3 mt-6">
                    <a href="mailto:info@example.com" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" aria-label="Email"><span class="material-symbols-outlined text-lg">mail</span></a>
                    <a href="tel:+622100000000" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-primary transition-colors" aria-label="Phone"><span class="material-symbols-outlined text-lg">call</span></a>
                </div>
            </div>
            @if($footerMenu && $footerMenu->items)
                @php $footerItems = collect($footerMenu->items)->sortBy('sort_order')->values(); @endphp
                @foreach($footerItems->take(2) as $item)
                    <div class="md:col-span-2">
                        <h5 class="font-semibold text-white mb-4">{{ $item['label'] }}</h5>
                        <ul class="space-y-2.5">
                            @foreach(($item['children'] ?? []) as $child)
                                <li><a href="{{ $child['url'] ?? '#' }}" target="{{ $child['target'] ?? '_self' }}" class="text-white/60 text-sm hover:text-white transition-colors">{{ $child['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
                <div class="md:col-span-3">
                    <h5 class="font-semibold text-white mb-4">Kontak</h5>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        <li class="flex gap-2"><span class="material-symbols-outlined text-base mt-0.5 shrink-0">location_on</span><span>Alamat kantor Anda — ubah via seeder/CMS</span></li>
                        <li><a href="mailto:info@example.com" class="hover:text-white transition-colors">info@example.com</a></li>
                        <li><a href="tel:+622100000000" class="hover:text-white transition-colors">+62 21 0000 0000</a></li>
                    </ul>
                </div>
            @else
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
                <div class="md:col-span-3">
                    <h5 class="font-semibold text-white mb-4">Kontak</h5>
                    <ul class="space-y-2.5 text-sm text-white/60">
                        <li class="flex gap-2"><span class="material-symbols-outlined text-base mt-0.5 shrink-0">location_on</span><span>Pasar &amp; dapur — antar harian</span></li>
                        <li><a href="mailto:info@example.com" class="hover:text-white">info@example.com</a></li>
                        <li><a href="tel:+622100000000" class="hover:text-white">+62 21 0000 0000</a></li>
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
