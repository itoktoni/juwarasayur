@props(['items' => []])

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    @foreach($items as $item)
    @php $tag = isset($item['url']) && $item['url'] ? 'a' : 'div'; @endphp
    <{{ $tag }} @if(isset($item['url']) && $item['url']) href="{{ $item['url'] }}" @endif class="flex items-center gap-4 lg:flex-col lg:items-start bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 lg:p-5 shadow-sm mb-3 {{ isset($item['url']) && $item['url'] ? 'hover:border-primary/40 hover:shadow-md hover:bg-primary/5 transition cursor-pointer' : '' }}">
        <div class="w-11 h-11 lg:w-12 lg:h-12 rounded-xl shrink-0 {{ $item['bg_color'] ?? 'bg-primary/10' }} flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl {{ $item['icon_color'] ?? 'text-primary' }}">{{ $item['icon_name'] ?? 'analytics' }}</span>
        </div>
        <div class="min-w-0">
            @if(isset($item['badge']))
            <span class="font-label-caps text-label-caps {{ $item['badge_class'] ?? 'bg-primary-fixed text-primary' }} px-2 py-1 rounded-full">{{ $item['badge'] }}</span>
            @endif
            <p class="font-semibold text-lg lg:font-headline-lg lg:text-headline-lg text-on-surface truncate" title="{{ $item['value'] ?? '' }}">{{ $item['value'] ?? '' }}</p>
            <p class="text-xs lg:font-label-caps lg:text-label-caps text-on-surface-variant mt-0.5 lg:mt-1 truncate">{{ $item['label'] ?? '' }}</p>
            @if(isset($item['url']) && $item['url'])
            <p class="text-[10px] text-primary mt-1 hidden lg:block">Klik untuk lihat →</p>
            @endif
        </div>
    </{{ $tag }}>
    @endforeach
</div>
