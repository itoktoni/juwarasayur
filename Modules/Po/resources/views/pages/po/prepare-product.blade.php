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
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Prepared</label>
            <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center text-sm">{{ $model->po_detail_prepared }}</div>
        </div>
        <div class="col-span-6 md:col-span-2">
            <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Sisa</label>
            <div class="w-full h-12 px-4 bg-primary-fixed text-primary border border-outline-variant rounded-lg flex items-center font-bold text-sm">{{ $model->po_detail_sisa }}</div>
        </div>
    </x-card>

    <x-form :action="route('po-po.postPrepareProduct', ['id' => $model->id])">
        <x-card label="Lokasi & Qty" class="mt-5">
            <div class="col-span-12">
                <div id="prepare-locations" class="space-y-3">
                    <div class="prepare-location-row grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50" data-index="0">
                        <div class="col-span-12 md:col-span-5">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Lokasi Gudang <span class="text-error">*</span></label>
                            <select name="locations[0][lokasi_id]" class="prepare-lokasi w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach($lokasiOptions as $lid => $lnama)
                                    <option value="{{ $lid }}">{{ $lnama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-6 md:col-span-3">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Qty <span class="text-error">*</span></label>
                            <input type="number" name="locations[0][qty]" value="1" min="1" class="prepare-qty w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        </div>
                        <div class="col-span-6 md:col-span-3">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Expired Date</label>
                            <input type="date" name="locations[0][expired_date]" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        </div>
                        <div class="col-span-12 md:col-span-1 flex justify-end">
                            <button type="button" onclick="removeLocationRow(this)" class="btn btn-soft w-full h-12 text-error" title="Hapus baris">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2 items-center justify-between">
                    <button type="button" onclick="addLocationRow()" class="btn btn-soft btn-sm">
                        <span class="material-symbols-outlined text-base">add</span> Tambah Lokasi
                    </button>
                    <span id="prepare-total" class="font-bold text-sm">Total: 0 / Sisa {{ $model->po_detail_sisa }}</span>
                </div>
                @error('locations')<p class="text-error text-xs mt-2">{{ $message }}</p>@enderror
            </div>
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>

    <template id="location-row-template">
        <div class="prepare-location-row grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50" data-index="__IDX__">
            <div class="col-span-12 md:col-span-5">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Lokasi Gudang <span class="text-error">*</span></label>
                <select name="locations[__IDX__][lokasi_id]" class="prepare-lokasi w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach($lokasiOptions as $lid => $lnama)
                        <option value="{{ $lid }}">{{ $lnama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 md:col-span-3">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Qty <span class="text-error">*</span></label>
                <input type="number" name="locations[__IDX__][qty]" value="1" min="1" class="prepare-qty w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div class="col-span-6 md:col-span-3">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Expired Date</label>
                <input type="date" name="locations[__IDX__][expired_date]" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div class="col-span-12 md:col-span-1 flex justify-end">
                <button type="button" onclick="removeLocationRow(this)" class="btn btn-soft w-full h-12 text-error" title="Hapus baris">
                    <span class="material-symbols-outlined text-lg">delete</span>
                </button>
            </div>
        </div>
    </template>

    <script>
        const SISA = {{ $model->po_detail_sisa }};
        function totalQty() {
            let total = 0;
            document.querySelectorAll('.prepare-qty').forEach(function(el) {
                total += parseInt(el.value || 0, 10) || 0;
            });
            return total;
        }
        function updateTotal() {
            const total = totalQty();
            const el = document.getElementById('prepare-total');
            if (el) el.textContent = 'Total: ' + total + ' / Sisa ' + SISA;
            if (total > SISA) {
                el.classList.add('text-error');
            } else {
                el.classList.remove('text-error');
            }
        }
        function addLocationRow() {
            const wrap = document.getElementById('prepare-locations');
            const tpl = document.getElementById('location-row-template').innerHTML;
            const idx = wrap.querySelectorAll('.prepare-location-row').length;
            const html = tpl.replaceAll('__IDX__', idx);
            wrap.insertAdjacentHTML('beforeend', html);
            updateTotal();
        }
        function removeLocationRow(btn) {
            const wrap = document.getElementById('prepare-locations');
            if (wrap.querySelectorAll('.prepare-location-row').length <= 1) { alert('Minimal 1 lokasi'); return; }
            btn.closest('.prepare-location-row')?.remove();
            wrap.querySelectorAll('.prepare-location-row').forEach(function(row, i) {
                row.dataset.index = i;
                row.querySelectorAll('[name]').forEach(function(el) {
                    el.name = el.name.replace(/locations\[\d+\]/, 'locations[' + i + ']');
                });
            });
            updateTotal();
        }
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('prepare-qty')) updateTotal();
        });
        updateTotal();
    </script>
</x-layouts::app>
