<?php /** @var Modules\Po\Models\Po $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('po-po.getTable'), 'label' => 'Purchase Order'], ['url' => '', 'label' => 'Prepare']]" />

    <x-card label="Informasi PO">
        <div class="col-span-12 md:col-span-3">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Kode PO</label>
            <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center font-mono text-sm">{{ $model->po_code }}</div>
        </div>
        <div class="col-span-12 md:col-span-3">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Supplier</label>
            <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center text-sm truncate">{{ $model->has_supplier->supplier_nama ?? '-' }}</div>
        </div>
        <div class="col-span-12 md:col-span-3">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Tanggal</label>
            <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center text-sm">{{ formatDate($model->po_tanggal) }}</div>
        </div>
        <div class="col-span-12 md:col-span-3">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Status</label>
            <div class="w-full h-12 px-4 bg-surface-container border border-outline-variant rounded-lg flex items-center">
                <x-badge :type="match($model->po_status){'pending'=>'warning','ordered'=>'info','partial'=>'warning','closed'=>'success','cancelled'=>'error', default=>''}">{{ ucfirst($model->po_status ?? '-') }}</x-badge>
            </div>
        </div>
    </x-card>

    <x-card label="Detail Produk" class="mt-5" :noGrid="true">
        <x-table :border="false">
            <x-slot:head>
                <th>Produk</th>
                <th class="text-center">Qty PO</th>
                <th class="text-center">Prepared</th>
                <th class="text-center">Sisa</th>
                <th class="text-left">Progress</th>
                <th class="text-right">Aksi</th>
            </x-slot:head>
            <x-slot:body>
                @foreach($model->has_details as $detail)
                @php
                    $qty = (int) $detail->po_detail_qty;
                    $prepared = (int) $detail->po_detail_prepared;
                    $sisa = $detail->po_detail_sisa;
                    $percent = $qty > 0 ? min(100, round($prepared / $qty * 100)) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="font-medium text-on-surface">{{ $detail->has_product->product_nama ?? '-' }}</div>
                        <div class="font-data-mono text-data-mono text-on-surface-variant mt-0.5">{{ $detail->po_detail_code ?? '#' . $detail->id }}</div>
                    </td>
                    <td class="text-center font-medium">{{ $qty }}</td>
                    <td class="text-center">
                        <x-badge :type="$prepared > 0 ? 'info' : 'default'">{{ $prepared }}</x-badge>
                    </td>
                    <td class="text-center">
                        <span class="@if($sisa > 0) font-bold text-warning @else text-success @endif">{{ $sisa }}</span>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-1.5 rounded-full bg-surface-container overflow-hidden">
                                <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="text-label-caps text-on-surface-variant w-9 text-right">{{ $percent }}%</span>
                        </div>
                    </td>
                    <td class="text-right">
                        @if($sisa > 0)
                        <a href="{{ route('po-po.getPrepareProduct', ['id' => $detail->id]) }}" class="inline-flex items-center justify-center gap-1.5 h-8 px-3 text-xs font-semibold rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                            <span class="material-symbols-outlined text-base">inventory_2</span> Prepare
                        </a>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-success">
                            <span class="material-symbols-outlined text-base">check_circle</span> Selesai
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </x-slot:body>
            <x-slot:mobile>
                <div class="space-y-3">
                    @foreach($model->has_details as $detail)
                    @php
                        $qty = (int) $detail->po_detail_qty;
                        $prepared = (int) $detail->po_detail_prepared;
                        $sisa = $detail->po_detail_sisa;
                        $percent = $qty > 0 ? min(100, round($prepared / $qty * 100)) : 0;
                    @endphp
                    <div class="mx-1 rounded-xl p-4 bg-surface-container-low">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-data-mono text-data-mono text-on-surface-variant">{{ $detail->po_detail_code ?? $model->po_code }}</p>
                                <p class="font-headline-md text-headline-md text-on-surface truncate mt-0.5">{{ $detail->has_product->product_nama ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 mt-4">
                            <div class="rounded-lg bg-surface-container px-3 py-2">
                                <p class="text-label-caps text-on-surface-variant">Qty PO</p>
                                <p class="font-medium text-sm mt-0.5">{{ $qty }}</p>
                            </div>
                            <div class="rounded-lg bg-surface-container px-3 py-2">
                                <p class="text-label-caps text-on-surface-variant">Prepared</p>
                                <p class="font-medium text-sm mt-0.5">{{ $prepared }}</p>
                            </div>
                            <div class="rounded-lg bg-surface-container px-3 py-2">
                                <p class="text-label-caps text-on-surface-variant">Sisa</p>
                                <p class="font-bold text-sm mt-0.5 {{ $sisa > 0 ? 'text-warning' : 'text-success' }}">{{ $sisa }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-4">
                            <div class="flex-1 h-1.5 rounded-full bg-surface-container overflow-hidden">
                                <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="text-label-caps text-on-surface-variant">{{ $percent }}%</span>
                        </div>

                        <div class="mt-4 pt-3 border-t border-outline-variant/50">
                            @if($sisa > 0)
                            <a href="{{ route('po-po.getPrepareProduct', ['id' => $detail->id]) }}" class="inline-flex items-center justify-center gap-1.5 w-full h-10 text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 transition-colors">
                                <span class="material-symbols-outlined text-base">inventory_2</span> Prepare Produk
                            </a>
                            @else
                            <div class="w-full h-10 rounded-lg bg-success/10 text-success inline-flex items-center justify-center gap-1.5 text-sm font-medium">
                                <span class="material-symbols-outlined text-base">check_circle</span> Sudah Diprepare
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-slot:mobile>
        </x-table>
    </x-card>

    <div class="mt-5">
        <a href="{{ route('po-po.getTable') }}" class="inline-flex items-center justify-center gap-1 h-10 px-4 text-sm font-semibold rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-xl">arrow_back</span> Kembali
        </a>
    </div>
</x-layouts::app>
