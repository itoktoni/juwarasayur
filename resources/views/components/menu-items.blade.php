@props(['items', 'mobile' => false])

@php
    $menu = config('menu.sidebar');
    $currentRoute = request()->route()?->getName();
    $allMenuRoutes = collect($menu)->flatMap(fn ($section) => collect($section['items'])->pluck('route'))->all();
@endphp

@foreach($menu as $section)
    @if($section['label'])
        <div class="px-4 pt-4 pb-1 font-label-caps text-label-caps text-on-surface-variant uppercase tracking-widest">{{ $section['label'] }}</div>
    @endif
    @foreach($section['items'] as $item)
        @php
            $routeName = $item['route'];
            $url = route($routeName);
            $matchRoutes = $item['match'] ?? [];
            $isActive = request()->routeIs($routeName)
                || request()->routeIs($routeName . '.*')
                || collect($matchRoutes)->contains(fn ($m) => request()->routeIs($m));
            // Halaman aktif adalah item menu lain secara eksak?
            // Matikan highlight wildcard agar tidak dua menu aktif sekaligus.
            if ($isActive && $routeName !== $currentRoute && in_array($currentRoute, $allMenuRoutes, true)) {
                $isActive = false;
            }
        @endphp
        <a
            href="{{ $url }}"
            wire:navigate
            @if($mobile) @click="drawerOpen = false" @endif
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ $mobile ? '' : 'group' }} {{ $isActive ? 'bg-primary text-on-primary font-semibold' : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
        >
            <span class="material-symbols-outlined {{ $isActive ? 'text-on-primary' : 'text-on-surface-variant' . ($mobile ? '' : ' group-hover:text-on-surface') }}">{{ $item['icon'] }}</span>
            <span class="font-body-sm">{{ $item['label'] }}</span>
        </a>
    @endforeach
@endforeach
