@props(['items' => []])

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    @foreach($items as $item)
    <div class="flex items-center gap-4 lg:flex-col lg:items-start bg-surface-container-lowest border border-outline-variant rounded-2xl p-4 lg:p-5 shadow-sm mb-3">
        <div class="w-11 h-11 lg:w-12 lg:h-12 rounded-xl shrink-0 {{ $item['bg_color'] ?? 'bg-primary/10' }} flex items-center justify-center">
            <span class="material-symbols-outlined text-2xl {{ $item['icon_color'] ?? 'text-primary' }}">{{ $item['icon_name'] ?? 'analytics' }}</span>
        </div>
        <div class="min-w-0">
            @if(isset($item['badge']))
            <span class="font-label-caps text-label-caps {{ $item['badge_class'] ?? 'bg-primary-fixed text-primary' }} px-2 py-1 rounded-full">{{ $item['badge'] }}</span>
            @endif
            <p class="font-semibold text-lg lg:font-headline-lg lg:text-headline-lg text-on-surface truncate" title="{{ $item['value'] ?? '' }}">{{ $item['value'] ?? '' }}</p>
            <p class="text-xs lg:font-label-caps lg:text-label-caps text-on-surface-variant mt-0.5 lg:mt-1 truncate">{{ $item['label'] ?? '' }}</p>
        </div>
    </div>
    @endforeach
</div>
