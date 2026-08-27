<x-layouts::app>
    <x-breadcrumb :items="[
        ['url' => route('prepare.index'), 'label' => 'Prepare dari SO'],
        ['url' => '', 'label' => 'Siapkan ' . $product->product_nama],
    ]" />

    <x-card label="Info Produk">
        <div class="col-span-12 md:col-span-4">
            <label class="text-xs font-bold text-on-surface-variant block mb-1">Produk</label>
            <div class="w-full h-12 px-4 bg-surface-container border border-outline-variant rounded-lg flex items-center text-sm font-medium">{{ $product->product_nama }}</div>
        </div>
        <div class="col-span-6 md:col-span-2">
            <label class="text-xs font-bold text-on-surface-variant block mb-1">Total Diminta</label>
            <div class="w-full h-12 px-4 bg-surface-container border border-outline-variant rounded-lg flex items-center font-mono text-sm">{{ $totalDiminta }}</div>
        </div>
        <div class="col-span-6 md:col-span-2">
            <label class="text-xs font-bold text-on-surface-variant block mb-1">Sudah Disiapkan</label>
            <div class="w-full h-12 px-4 bg-surface-container border border-outline-variant rounded-lg flex items-center font-mono text-sm">{{ $totalDisiapkan }}</div>
        </div>
        <div class="col-span-6 md:col-span-2">
            <label class="text-xs font-bold text-on-surface-variant block mb-1">Sisa</label>
            <div class="w-full h-12 px-4 bg-primary-fixed text-primary border border-outline-variant rounded-lg flex items-center font-mono font-bold text-sm">{{ $sisa }}</div>
        </div>
        <div class="col-span-6 md:col-span-2">
            <label class="text-xs font-bold text-on-surface-variant block mb-1">Jumlah SO</label>
            <div class="w-full h-12 px-4 bg-surface-container border border-outline-variant rounded-lg flex items-center font-mono text-sm">{{ $details->count() }}</div>
        </div>
    </x-card>

    {{-- Daftar SO sumber --}}
    <x-card label="SO yang Meminta" class="mt-5" icon="receipt_long" :noGrid="true">
        <x-table :border="false">
            <x-slot:head>
                <th>Kode SO</th>
                <th>Customer</th>
                <th class="text-center">Qty Diminta</th>
                <th class="text-center">Sudah</th>
            </x-slot:head>
            <x-slot:body>
                @foreach($details as $d)
                    @php
                        $sudah = (int) $d->has_prepare_allocations->sum('qty');
                    @endphp
                    <tr>
                        <td class="font-data-mono text-data-mono text-primary">{{ $d->has_so?->so_code ?? '-' }}</td>
                        <td>{{ $d->has_so?->so_customer_name ?? $d->has_so?->has_customer?->name ?? '-' }}</td>
                        <td class="text-center font-medium">{{ (int) $d->so_detail_qty }}</td>
                        <td class="text-center">
                            <x-badge :type="$sudah > 0 ? 'info' : 'default'">{{ $sudah }}</x-badge>
                        </td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-table>
    </x-card>

    <form method="POST" action="{{ route('prepare.storePrepare', ['product' => $product->id]) }}">
        @csrf
        @foreach($soDetailIds as $id)
            <input type="hidden" name="so_detail_ids[]" value="{{ $id }}">
        @endforeach
        <x-card label="Form Siapkan" class="mt-5">
            <x-input col="12" type="hidden" name="product_id" :value="$product->id" />
            <div class="col-span-12 md:col-span-5">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Lokasi Gudang <span class="text-error">*</span></label>
                <select name="lokasi_id" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" required>
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($lokasiOptions as $l)
                        <option value="{{ $l->id }}">{{ $l->lokasi_nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 md:col-span-3">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Qty <span class="text-error">*</span></label>
                <input type="number" name="qty" value="{{ $sisa }}" min="1" max="{{ $sisa }}" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" required>
            </div>
            <div class="col-span-6 md:col-span-4">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Expired Date <span class="text-on-surface-variant font-normal">(opsional, untuk filter stok)</span></label>
                <input type="date" name="expired_date" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
        </x-card>

        <div class="mt-5 flex justify-end gap-2">
            <a href="{{ route('prepare.group', ['so_ids' => $soIds ?? []]) }}"
               class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg text-sm font-semibold bg-surface-container-highest text-on-surface hover:bg-surface-container-low transition-all">
                <span class="material-symbols-outlined text-xl">close</span> Batal
            </a>
            <x-button type="submit" variant="primary" icon="save">Simpan & Kurangi Stok</x-button>
        </div>
    </form>
</x-layouts::app>
