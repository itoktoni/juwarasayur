{{-- Desktop Sidebar --}}
@php
    $totalMenuItems = collect(config('menu.sidebar'))->sum(fn($section) => count($section['items']));
@endphp
<aside class="hidden md:flex flex-col fixed top-16 left-0 h-[calc(100vh-4rem)] w-72 z-40 transition-transform duration-300 px-3 pt-4 border-r border-outline-variant/50 shadow-sm" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <nav style="flex:1 1 0; min-height:0; overflow-y:scroll; -webkit-overflow-scrolling:touch; touch-action:pan-y; padding-bottom:1rem" class="space-y-2 sidebar-scroll {{ $totalMenuItems > 15 ? 'pr-3' : '' }}">
        <x-menu-items />
    </nav>
</aside>

<script>
    // Auto-scroll sidebar ke menu yang aktif saat load maupun navigasi wire:navigate
    (function () {
        function scrollToActiveMenu() {
            var nav = document.querySelector('.sidebar-scroll');
            var active = nav ? nav.querySelector('a.bg-primary') : null;
            if (!nav || !active) return;

            var navRect = nav.getBoundingClientRect();
            var elRect = active.getBoundingClientRect();

            // Hanya scroll kalau item di luar area terlihat
            if (elRect.top >= navRect.top && elRect.bottom <= navRect.bottom) return;

            // Posisikan item aktif di tengah viewport sidebar (dibatasi atas/bawah)
            var target = nav.scrollTop + (elRect.top - navRect.top)
                - (nav.clientHeight / 2 - elRect.height / 2);
            nav.scrollTop = Math.max(0, target);
        }

        document.addEventListener('DOMContentLoaded', scrollToActiveMenu);
        document.addEventListener('livewire:navigated', function () {
            setTimeout(scrollToActiveMenu, 50);
        });
    })();
</script>