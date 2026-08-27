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
        @foreach ($groups as $i => $group)
            {{-- 1 card = 1 supplier = 1 calon PO dengan multiple product --}}
            <x-card :label="'#'.($i + 1).' — '.$group['supplier']->supplier_nama" icon="storefront" :noGrid="true">
                <div class="col-span-12 mb-4 rounded-lg bg-primary-container/30 px-4 py-3 flex flex-wrap justify-between items-center gap-2">
                    <span class="font-semibold text-on-surface">
                        {{ $group['supplier']->supplier_nama }}
                        <span class="text-on-surface-variant font-normal">· {{ $group['items']->count() }} produk</span>
                    </span>
                    <div class="flex items-center gap-3">
                        <span class="text-lg font-bold text-on-surface">{{ $fmtKg($group['total_berat']) }} kg</span>
                        {{-- Generate PO per supplier: 1 PO dengan semua produk di card ini --}}
                        {{-- (form terpisah, bukan membungkus card — hindari nested form) --}}
                        <form action="{{ route('po-generate.generate') }}" method="POST" class="shrink-0">
                            @csrf
                            <input type="hidden" name="tanggal" value="{{ $tanggal }}" />
                            <input type="hidden" name="suppliers[]" value="{{ $group['supplier']->id }}" />
                            <button type="submit" title="Generate 1 PO untuk {{ $group['supplier']->supplier_nama }} dengan {{ $group['items']->count() }} produk"
                                class="inline-flex items-center justify-center gap-1 h-8 px-3 text-xs font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 shrink-0">
                                <span class="material-symbols-outlined text-base">shopping_cart</span>
                                <span>Generate PO</span>
                            </button>
                        </form>
                    </div>
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
                            @php $soCount = count($item['so_codes']); @endphp
                            <tr>
                                <td class="font-data-mono text-data-mono text-on-surface-variant">
                                    {{ implode(', ', $item['so_codes']) }}
                                    @if ($soCount > 1)
                                        <span class="ml-1 inline-block text-[10px] font-semibold text-primary bg-primary/10 rounded px-1.5 py-0.5 align-middle">{{ $soCount }} SO</span>
                                    @endif
                                </td>
                                <td class="font-medium text-on-surface">{{ $item['product_nama'] }}</td>
                                <td class="text-center">{{ $fmtKg($item['berat']) }} kg</td>
                                <td class="text-center">× {{ $item['qty'] }}</td>
                                <td class="text-right font-medium">= {{ $fmtKg($item['total_berat']) }} kg</td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                    <x-slot:mobile>
                        {{-- Tampilan mobile: kartu per barang (tabel disembunyikan di bawah lg) --}}
                        <div class="p-3 space-y-3">
                            @foreach ($group['items'] as $item)
                            <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                                <p class="text-sm font-bold text-on-surface truncate mb-0.5">{{ $item['product_nama'] }}</p>
                                <p class="text-[11px] font-data-mono text-data-mono text-on-surface-variant break-all mb-3">
                                    {{ implode(', ', $item['so_codes']) }}
                                    @if (count($item['so_codes']) > 1)
                                        <span class="ml-1 inline-block text-[10px] font-semibold text-primary bg-primary/10 rounded px-1.5 py-0.5 align-middle">{{ count($item['so_codes']) }} SO</span>
                                    @endif
                                </p>
                                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-outline-variant/50">
                                    <div>
                                        <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Berat/unit</p>
                                        <p class="text-xs font-medium text-on-surface">{{ $fmtKg($item['berat']) }} kg</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Qty</p>
                                        <p class="text-xs font-medium text-on-surface">× {{ $item['qty'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Total</p>
                                        <p class="text-xs font-mono font-medium text-on-surface">{{ $fmtKg($item['total_berat']) }} kg</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </x-slot:mobile>
                </x-table>
            </x-card>
        @endforeach

        {{-- Summary: generate semua sekaligus — dikelompokkan per master product,
             supplier diambil dari rekomendasi master, hasilnya 1 PO per supplier multi produk --}}
        @php
            $supplierCount = $groups->unique(fn ($g) => $g['supplier']->id)->count();
            $totalItems = $groups->sum(fn ($g) => $g['items']->count());
            $totalBerat = $groups->sum('total_berat');
        @endphp
        <x-form :action="route('po-generate.generate')">
            <input type="hidden" name="tanggal" value="{{ $tanggal }}" />
            <div class="mt-6 rounded-xl border border-primary/30 bg-primary-container/30 p-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm text-on-surface">
                    <p class="font-semibold">Total: {{ $totalItems }} barang → {{ $supplierCount }} supplier ({{ $fmtKg($totalBerat) }} kg)</p>
                    <p class="text-on-surface-variant text-xs mt-0.5">
                        Dikelompokkan per product master, supplier dipilih dari rekomendasi master — 1 PO per supplier dengan multiple product.
                    </p>
                </div>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-1.5 h-10 px-4 text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 shrink-0">
                    <span class="material-symbols-outlined text-lg">shopping_cart_checkout</span>
                    <span>Generate PO Semua ({{ $supplierCount }} supplier)</span>
                </button>
            </div>
        </x-form>
    @elseif ($tanggal)
        <x-card label="Hasil" icon="info" class="mt-5" :noGrid="true">
            <p class="text-sm text-on-surface-variant">
                Tidak ada detail SO baru untuk tanggal {{ $tanggal }} (semua sudah digenerate / tidak ada SO).
            </p>
        </x-card>
    @endif
</x-layouts::app>
