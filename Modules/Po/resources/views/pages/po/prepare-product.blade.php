<?php /** @var Modules\Po\Models\PoDetail $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('po-po.getTable'), 'label' => 'Purchase Order'], ['url' => route('po-po.getPrepare', ['id' => $model->po_detail_id_po]), 'label' => 'Prepare'], ['url' => '', 'label' => 'Product']]" />

    <x-card label="Info Produk">
        <div class="col-span-12 md:col-span-4">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Produk</label>
            <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center text-sm">{{ $model->has_product->product_nama ?? '-' }}</div>
        </div>
        <div class="col-span-12 md:col-span-2">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Kode PO</label>
            <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center font-mono text-sm">{{ $model->has_po->po_code ?? '-' }}</div>
        </div>
        <div class="col-span-6 md:col-span-2">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Qty PO</label>
            <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center text-sm">{{ $model->po_detail_qty }}</div>
        </div>
        <div class="col-span-6 md:col-span-2">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Diminta SO</label>
            <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center text-sm font-mono">{{ ($totalDiminta ?? 0) > 0 ? (int) $totalDiminta : '—' }}</div>
        </div>
        <div class="col-span-6 md:col-span-2">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Prepared</label>
            <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center text-sm">{{ $model->po_detail_prepared }}</div>
        </div>
    </x-card>

    {{-- Card ringkas sumber SO (hanya tampil jika PO berasal dari SO) --}}
    @if(($soSources ?? collect())->isNotEmpty())
    <x-card label="Diminta oleh Sales Order" class="mt-5" icon="receipt_long" :noGrid="true">
        <x-table :border="false">
            <x-slot:head>
                <th>Kode SO</th>
                <th>Customer</th>
                <th class="text-center">Qty Diminta</th>
            </x-slot:head>
            <x-slot:body>
                @foreach($soSources as $src)
                <tr>
                    <td class="font-data-mono text-data-mono text-primary">{{ $src->has_so?->so_code ?? '-' }}</td>
                    <td>{{ $src->has_so?->has_customer?->name ?? $src->has_so?->so_customer_name ?? '-' }}</td>
                    <td class="text-center font-mono font-semibold">× {{ (int) $src->pivot->qty }}</td>
                </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </x-card>
    @endif

    <x-form :action="route('po-po.postPrepareProduct', ['id' => $model->id])">
        <x-card label="Lokasi & Qty" class="mt-5">
            <div class="col-span-12">
                @php
                    // Sisa qty = permintaan SO (kalau ada) atau po_detail_qty
                    $sisaQty = (int) max(0, ($totalDiminta ?? 0) > 0 ? $totalDiminta : $model->po_detail_qty);
                    // Sisa sudah dikurangi prepared
                    $sisaSiap = max(0, $sisaQty - (int) $model->po_detail_prepared);
                @endphp
                <div class="p-3 rounded-lg border border-outline-variant bg-surface-container-low/50">
                    <div class="grid grid-cols-12 gap-2 items-end">
                        <div class="col-span-12 md:col-span-6">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Lokasi Gudang <span class="text-error">*</span></label>
                            <select name="locations[0][lokasi_id]" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" required>
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach($lokasiOptions as $lid => $lnama)
                                    <option value="{{ $lid }}">{{ $lnama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-6 md:col-span-3">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Qty <span class="text-error">*</span></label>
                            <input type="number" name="locations[0][qty]" value="{{ $sisaSiap }}" min="1" max="{{ $sisaSiap }}" class="prepare-qty w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" required>
                        </div>
                        <div class="col-span-6 md:col-span-3">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Expired Date</label>
                            <input type="date" name="locations[0][expired_date]" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-end gap-2 text-sm">
                    <span class="text-on-surface-variant">Sisa qty diminta:</span>
                    <span class="font-bold {{ $sisaSiap > 0 ? 'text-warning' : 'text-success' }}">{{ $sisaSiap }}</span>
                </div>
                @error('locations')<p class="text-error text-xs mt-2">{{ $message }}</p>@enderror
            </div>
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
