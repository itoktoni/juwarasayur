{{-- Layout akun (profile, pesanan, customer reseller): navbar publik + dropdown akun --}}
@props(['title' => 'Akun'])
@php
    $siteName = config('app.name', 'Mayur');
    $cartCount = app(\Modules\Ecommerce\Services\CartService::class)->count();
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — {{ config('app.name', 'Mayur') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />
    @include('ecommerce::components.brand')
</head>
<body class="bg-surface text-on-surface antialiased pb-16 md:pb-0">

    {{-- Navbar --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-outline-variant">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 py-3">
                <a href="{{ route('home') }}" class="shrink-0" title="Juwara Sayur">
                    <img src="{{ asset('images/logo.png') }}" alt="Juwara Sayur" class="h-10 w-auto">
                </a>
                <nav class="ml-auto flex items-center gap-1 sm:gap-2">
                    <a href="{{ route('shop.index') }}" class="text-sm font-medium px-2 py-1 rounded-lg text-on-surface-variant hover:text-primary transition-colors">Katalog</a>
                    <a href="{{ route('cart.index') }}" class="relative p-2 rounded-full hover:bg-surface-container" title="Keranjang">
                        <span class="material-symbols-outlined">shopping_cart</span>
                        <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-error text-white text-[10px] font-bold grid place-items-center {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                    </a>

                    {{-- Dropdown akun (icon khusus menu) --}}
                    <div class="relative" id="user-menu">
                        <button type="button" id="user-menu-btn"
                            class="flex items-center justify-center w-10 h-10 rounded-lg hover:bg-surface-container transition-colors cursor-pointer text-on-surface"
                            title="Menu" aria-haspopup="true" aria-expanded="false">
                            <span class="material-symbols-outlined text-2xl">menu</span>
                        </button>
                        <div id="user-menu-panel" class="absolute right-0 top-full mt-2 w-52 bg-white border border-outline-variant rounded-xl shadow-lg z-50 overflow-hidden opacity-0 translate-y-2 pointer-events-none transition-all duration-150">
                            <div class="px-4 py-3 border-b border-outline-variant/60">
                                <p class="text-sm font-bold text-on-surface truncate">{{ $user?->name }}</p>
                                <p class="text-xs text-on-surface-variant truncate">{{ $user?->email }}</p>
                            </div>
                            <a href="{{ route('account.profile') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface hover:bg-surface-container-low transition-colors">
                                <span class="material-symbols-outlined text-xl text-on-surface-variant">person</span> Profile
                            </a>
                            <a href="{{ route('ecommerce.orders.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface hover:bg-surface-container-low transition-colors">
                                <span class="material-symbols-outlined text-xl text-on-surface-variant">receipt_long</span> Pesanan
                            </a>
                            @if($user?->isReseller())
                                <a href="{{ route('account.customers') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-on-surface hover:bg-surface-container-low transition-colors">
                                    <span class="material-symbols-outlined text-xl text-on-surface-variant">group</span> Customer
                                </a>
                            @endif
                            <div class="border-t border-outline-variant">
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm text-error font-medium hover:bg-error-container/30 transition-colors">
                                        <span class="material-symbols-outlined text-xl">logout</span> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    {{-- Konten --}}
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 mt-4 lg:mt-0">
        {{ $slot }}
    </main>

    @include('cms::frontend.layouts.footer')

    {{-- Bottom bar navigasi mobile --}}
    <nav class="fixed bottom-0 inset-x-0 z-40 md:hidden bg-white border-t border-outline-variant pb-[env(safe-area-inset-bottom)]" aria-label="Navigasi utama">
        <div class="grid grid-cols-4 h-16 max-w-lg mx-auto">
            @php
                $bottomItems = [
                    ['href' => route('home'), 'label' => 'Home', 'icon' => 'home', 'active' => request()->routeIs('home')],
                    ['href' => route('shop.index'), 'label' => 'Belanja', 'icon' => 'storefront', 'active' => request()->routeIs('shop.*')],
                    ['href' => route('cart.index'), 'label' => 'Keranjang', 'icon' => 'shopping_cart', 'active' => false],
                    ['href' => route('account.profile'), 'label' => 'Akun', 'icon' => 'person', 'active' => request()->routeIs('account.*') || request()->routeIs('ecommerce.orders.*')],
                ];
            @endphp
            @foreach($bottomItems as $item)
                <a href="{{ $item['href'] }}" aria-current="{{ $item['active'] ? 'page' : false }}"
                    class="flex flex-col items-center justify-center gap-0.5 transition-colors {{ $item['active'] ? 'text-primary font-semibold' : 'text-on-surface-variant hover:text-on-surface' }}">
                    <span class="material-symbols-outlined text-2xl" style="{{ $item['active'] ? "font-variation-settings: 'FILL' 1;" : '' }}">{{ $item['icon'] }}</span>
                    <span class="relative text-[11px] leading-none">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <script>
        // Dropdown akun (mirror halaman home)
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

</body>
</html>
