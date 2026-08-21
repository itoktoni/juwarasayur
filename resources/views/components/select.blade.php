@props(['name', 'label' => null, 'col' => '12', 'options' => [], 'default' => null, 'multiple' => false, 'placeholder' => '', 'model' => null, 'helper' => null])
@php
    global $activeBladeModel;
    $label = $label ?? formatLabel($name);
    $m = $model ?? $activeBladeModel ?? null;
    $selected = old($name, $default ?? ($m ? data_get($m, $name, '') : ''));
    if ($selected instanceof \BackedEnum) {
        $selected = $selected->value;
    }
    if (! is_array($selected)) {
        $selected = (string) $selected;
    }
    $hasError = $errors->has($name);
    $isTomSelect = $attributes->get('class') && str_contains($attributes->get('class'), 'search');
    $extraClass = $attributes->get('class') ? $attributes->get('class') : '';
    $selectClass = $isTomSelect
        ? 'w-full h-12 bg-transparent font-body-sm ' . $extraClass
        : 'w-full h-12 pl-4 pr-10 bg-white border ' . ($hasError ? 'border-error' : 'border-outline-variant') . ' rounded-lg font-body-sm appearance-none cursor-pointer focus:border-primary-container focus:ring-1 focus:ring-primary-container outline-none transition-all ' . $extraClass;
@endphp
<div class="col-span-12 md:col-span-{{ $col }}">
    <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">{{ $label }}</label>
    <div class="relative">
        <select name="{{ $name }}" {{ $multiple ? 'multiple' : '' }} id="select-{{ $name }}" class="{{ $selectClass }}" {{ $attributes->except('class') }}>
            @if(!$multiple && $placeholder !== false)
            <option value="">{{ $placeholder ?: '-- Silahkan Pilih --' }}</option>
            @endif
            @foreach($options as $key => $text)
            <option value="{{ $key }}" @if($multiple) {{ is_array($selected) && in_array($key, $selected) ? 'selected' : '' }} @else {{ (string) $selected === (string) $key ? 'selected' : '' }} @endif>{{ $text }}</option>
            @endforeach
        </select>
        @if(!$isTomSelect)
        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant text-xl">expand_more</span>
        @endif
    </div>
    @if($helper && !$hasError)<span class="font-label-caps text-label-caps text-on-surface-variant mt-1 block">{{ $helper }}</span>@endif
    @if($hasError)<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $errors->first($name) }}</span>@endif
</div>
@if($isTomSelect)
@once
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<style>
    .ts-wrapper {
        display: block !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }
    .ts-wrapper .ts-control {
        background: white !important;
        border: 1px solid #c4c5d5 !important;
        border-radius: 0.5rem !important;
        min-height: 3rem !important;
        height: auto !important;
        padding: 0.5rem 2.5rem 0.5rem 1rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25rem !important;
        color: #191c1e !important;
        box-shadow: none !important;
        opacity: 1 !important;
    }
    .ts-wrapper.multi .ts-control {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        gap: 4px !important;
        height: auto !important;
    }
    .ts-wrapper.multi .item {
        background: #00288e !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 0.25rem !important;
        padding: 2px 6px !important;
        line-height: 1.4 !important;
    }
    .ts-wrapper .ts-control::after {
        content: '' !important;
        display: block !important;
        position: absolute !important;
        right: 0.75rem !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 0 !important;
        height: 0 !important;
        border-left: 5px solid transparent !important;
        border-right: 5px solid transparent !important;
        border-top: 5px solid #444653 !important;
        border-bottom: none !important;
        margin: 0 !important;
        pointer-events: none !important;
    }
    .ts-wrapper.focus .ts-control,
    .ts-wrapper .ts-control:focus {
        border-color: #1e40af !important;
        box-shadow: 0 0 0 1px #1e40af !important;
    }
    .ts-wrapper .ts-control > * {
        color: #191c1e !important;
    }
    .ts-wrapper .ts-dropdown {
        background: white !important;
        border: 1px solid #c4c5d5 !important;
        border-radius: 0.5rem !important;
        margin-top: 4px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        z-index: 50 !important;
    }
    .ts-wrapper .ts-dropdown .ts-dropdown-content {
        max-height: 200px !important;
    }
    .ts-wrapper .ts-dropdown .option {
        padding: 0.625rem 1rem !important;
        font-size: 0.875rem !important;
        color: #191c1e !important;
    }
    .ts-wrapper .ts-dropdown .option.active {
        background: #dde1ff !important;
        color: #00288e !important;
    }
    .ts-wrapper .ts-dropdown .option:hover {
        background: #eceef0 !important;
    }
    .ts-wrapper .ts-control input {
        font-size: 0.875rem !important;
    }
    .ts-wrapper .ts-placeholder {
        color: #757684 !important;
        font-size: 0.875rem !important;
    }
    .ts-wrapper.has-error .ts-control {
        border-color: #ba1a1a !important;
    }
    .ts-wrapper.multi .remove {
        color: #ffffff !important;
        border: none !important;
        opacity: 0.7 !important;
    }
    .ts-wrapper.multi .remove:hover {
        opacity: 1 !important;
    }
</style>
@endonce
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('select-{{ $name }}');
    if (!el) return;
    var placeholderText = el.querySelector('option[value=""]');
    var ts = new TomSelect(el, {
        {!! $multiple ? 'create: true,' : 'create: false,' !!}
        plugins: {!! $multiple ? json_encode(['remove_button']) : json_encode([]) !!},
        {!! !$multiple && $placeholder !== false ? "placeholder: '" . addslashes($placeholder ?: '-- Silahkan Pilih --') . "'," : '' !!}
        allowEmptyOption: true,
    });
    @if($hasError)
    ts.wrapper.classList.add('has-error');
    @endif
});
</script>
@endif
