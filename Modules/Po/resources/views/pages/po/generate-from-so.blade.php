<?php

/** @var string|null $tanggal */
/** @var Illuminate\Support\Collection $groups */
/** @var Illuminate\Support\Collection $warnings */

$fmtKg = fn ($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.');
?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('po-po.getTable'), 'label' => 'Purchase Orders'], ['url' => '', 'label' => 'Generate dari SO']]" />

    {{-- Filter tanggal --}}
    <x-card label="Pilih Tanggal SO">
        <form method="GET" action="{{ route('po-generate.preview') }}" class="contents">
            <x-input col="4" type="date" name="tanggal" label="Tanggal SO" :value="$tanggal" />
            <div class="col-span-12 md:col-span-2 flex items-end">
                <x-button type="submit" variant="primary" icon="search">Preview</x-button>
            </div>
        </form>
        @error('tanggal')
            <div class="col-span-12"><p class="text-sm text-error">{{ $message }}</p></div>
        @enderror
    </x-card>

    @if ($warnings->isNotEmpty())
        <x-card label="Dilewati — Tidak Digenereate" icon="warning" class="mt-5" :noGrid="true">
            <ul class="list-disc pl-5 space-y-1 text-sm text-on-surface-variant">
                @foreach ($warnings as $group)
                    <li>
                        <strong class="text-on-surface">{{ $group['nama'] }}</strong>
                        ({{ $group['reason'] }})
                        — total {{ $fmtKg($group['total_berat']) }} kg, {{ $group['items']->count() }} baris SO.
                        @if ($group['reason'] === 'Tanpa Product Master')
                            Set product master dulu di menu Product Masters.
                        @else
                            Atur supplier rekomendasi di menu Product Masters.
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endif

    @if ($groups->isNotEmpty())
        <x-form :action="route('po-generate.generate')">
            <input type="hidden" name="tanggal" value="{{ $tanggal }}" />

            @foreach ($groups as $i => $group)
                <x-card :label="'#'.($i + 1).' — '.$group['nama']" icon="category" class="mt-5" :noGrid="true">
                    <div class="col-span-12 mb-4 rounded-lg bg-primary-container/30 px-4 py-3 flex flex-wrap justify-between items-center gap-2">
                        <span class="font-semibold text-on-surface">
                            {{ $group['nama'] }}
                            <span class="text-on-surface-variant font-normal">→ {{ $group['supplier']->supplier_nama }}</span>
                        </span>
                        <span class="text-lg font-bold text-on-surface">{{ $fmtKg($group['total_berat']) }} kg</span>
                    </div>

                    <x-table :border="false">
                        <x-slot:head>
                            <th>SO</th>
                            <th>Produk</th>
                            <th class="text-center">Berat/unit</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Total Berat</th>
                        </x-slot:head>
                        <x-slot:body>
                            @foreach ($group['items'] as $item)
                                <tr>
                                    <td class="font-data-mono text-data-mono text-on-surface-variant">{{ $item['so_code'] }}</td>
                                    <td class="font-medium text-on-surface">{{ $item['product_nama'] }}</td>
                                    <td class="text-center">{{ $fmtKg($item['berat']) }} kg</td>
                                    <td class="text-center">× {{ $item['qty'] }}</td>
                                    <td class="text-right font-medium">= {{ $fmtKg($item['total_berat']) }} kg</td>
                                </tr>
                            @endforeach
                        </x-slot:body>
                    </x-table>
                </x-card>
            @endforeach

            {{-- Action bar standar — tombol submit custom via slot --}}
            <x-action cancel="{{ route('po-generate.preview') }}" :action="['generate']">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">shopping_cart</span>
                    <span class="hidden sm:inline">Generate PO ({{ $groups->count() }} supplier)</span>
                </button>
            </x-action>
        </x-form>
    @elseif ($tanggal)
        <x-card label="Hasil" icon="info" class="mt-5" :noGrid="true">
            <p class="text-sm text-on-surface-variant">
                Tidak ada detail SO baru untuk tanggal {{ $tanggal }} (semua sudah digenerate / tidak ada SO).
            </p>
        </x-card>
    @endif
</x-layouts::app>
