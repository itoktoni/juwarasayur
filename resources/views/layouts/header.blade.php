{{-- Header --}}
<header class="fixed top-0 w-full z-50 bg-surface-container-lowest shadow-sm border-b border-outline-variant flex items-center justify-between px-3 md:px-8 h-16">
    <div class="flex min-w-0 items-center gap-2 md:gap-4">
        <button type="button" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full hover:bg-surface-container transition-colors md:hidden" aria-label="Open navigation menu" @click="drawerOpen = !drawerOpen">
            <span class="material-symbols-outlined text-[22px] text-on-surface-variant">menu</span>
        </button>
        <button type="button" class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-full hover:bg-surface-container transition-colors md:inline-flex" aria-label="Toggle sidebar" @click="sidebarOpen = !sidebarOpen">
            <span class="material-symbols-outlined text-[22px] text-on-surface-variant">menu</span>
        </button>
        <a href="{{ url('/') }}" class="min-w-0 truncate font-headline-md text-headline-md font-bold text-primary" wire:navigate>
            {{ config('website.name', config('app.name', 'Laravel')) }}
        </a>
    </div>

    <div class="flex items-center gap-2">

        {{-- Notification Dropdown --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button class="relative p-2 hover:bg-surface-container rounded-full transition-colors text-on-surface-variant" @click="open = !open">
                <span class="material-symbols-outlined text-[22px]">notifications</span>
                <span class="absolute top-1 right-1 w-4 h-4 bg-error text-on-error text-[10px] font-bold rounded-full flex items-center justify-center" x-show="unreadCount > 0" x-text="unreadCount" x-cloak></span>
            </button>

            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" class="absolute right-0 top-full mt-2 w-80 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg overflow-hidden z-50">
                <div class="flex items-center justify-between px-4 py-3 border-b border-outline-variant">
                    <span class="font-headline-md text-headline-md text-on-surface">Notifications</span>
                    <button class="font-label-caps text-label-caps text-primary hover:underline" x-show="unreadCount > 0" @click="markAllRead()">Mark all read</button>
                </div>
                <div class="max-h-80 overflow-y-auto">
                    <template x-if="notifications.length === 0">
                        <div class="px-4 py-8 text-center">
                            <span class="material-symbols-outlined text-3xl text-on-surface-variant/40">notifications_off</span>
                            <p class="font-label-caps text-label-caps text-on-surface-variant mt-2">No notifications</p>
                        </div>
                    </template>
                    <template x-for="notif in notifications" :key="notif.id">
                        <div class="flex items-start gap-3 px-4 py-3 hover:bg-surface-container-low transition-colors border-b border-outline-variant/30 cursor-pointer" :class="{ 'bg-primary-fixed/10': !notif.read }" @click="markRead(notif)">
                            <span class="mt-0.5 shrink-0" :style="'color:' + (notif.iconColor || '#176c33')" x-text="notif.icon || 'ℹ️'"></span>
                            <div class="flex-1 min-w-0">
                                <p class="font-body-sm text-body-sm text-on-surface" :class="{ 'font-semibold': !notif.read }" x-text="notif.title"></p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5 line-clamp-2" x-text="notif.body" x-show="notif.body"></p>
                                <p class="font-label-caps text-label-caps text-on-surface-variant mt-0.5" x-text="notif.time"></p>
                            </div>
                            <div x-show="!notif.read" class="w-2 h-2 bg-primary rounded-full shrink-0 mt-2"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Profile Dropdown --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center overflow-hidden border border-outline-variant hover:ring-2 hover:ring-primary/20 transition-all" @click="open = !open">
                <span class="material-symbols-outlined text-[18px] text-on-secondary-container">person</span>
            </button>

            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" class="absolute right-0 top-full mt-2 w-64 bg-surface-container-lowest border border-outline-variant rounded-xl shadow-lg overflow-hidden z-50">
                <div class="px-4 py-3 border-b border-outline-variant">
                    <p class="font-body-sm font-semibold text-on-surface">{{ auth()->user()->name ?? 'Warehouse Admin' }}</p>
                    <p class="font-label-caps text-label-caps text-on-surface-variant">{{ auth()->user()->email ?? 'admin@wms.com' }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('profile.edit') }}" wire:navigate class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-surface-container-low transition-colors text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-xl">person</span>
                        <span class="font-body-sm text-body-sm">My Profile</span>
                    </a>
                    <a href="#" class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-surface-container-low transition-colors text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined text-xl">help</span>
                        <span class="font-body-sm text-body-sm">Help & Support</span>
                    </a>
                </div>
                <div class="border-t border-outline-variant py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-error-container/30 transition-colors text-error">
                            <span class="material-symbols-outlined text-xl">logout</span>
                            <span class="font-body-sm text-body-sm font-semibold">Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
