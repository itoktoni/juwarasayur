<?php /** @var Modules\Po\Models\Po $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Purchase Order'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    @php
        $isEdit = isset($model) && $model->exists;
        $details = $isEdit && $model->relationLoaded('has_details') ? $model->has_details : collect(old('details', []));
        if ($details->isEmpty() && !$isEdit) {
            $details = collect([['po_detail_id_product' => '', 'po_detail_qty' => 1, 'po_detail_harga' => '', 'po_detail_keterangan' => '']]);
        }
        $productPricesJson = json_encode($productPrices ?? []);
        $poDiscountVal = old('po_discount', $model?->po_discount ?? '');
        $poDiscountTypeVal = old('po_discount_type', $model?->po_discount_type ?? 'nominal');
        $poPpnRateVal = old('po_ppn_rate', $model?->po_ppn_rate ?? ($ppnRateDefault ?? 11));
        $poPphRateVal = old('po_pph_rate', $model?->po_pph_rate ?? ($pphRateDefault ?? 2));
        $trimVal = fn($v) => $v === '' || $v === null ? '' : rtrim(rtrim(number_format((float)$v, 2, ".", ""), "0"), ".");
        $poDiscountDisplay = $poDiscountVal === '' || $poDiscountVal === null ? '' : $trimVal($poDiscountVal);
        $poPpnRateDisplay = $poPpnRateVal === '' || $poPpnRateVal === null ? '' : $trimVal($poPpnRateVal);
        $poPphRateDisplay = $poPphRateVal === '' || $poPphRateVal === null ? '' : $trimVal($poPphRateVal);
    @endphp

    <x-form :model="$model">
        <x-card label="Informasi Purchase Order">
            @if($isEdit)
                <div class="col-span-12 md:col-span-4">
                    <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Kode PO</label>
                    <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center font-mono text-sm">{{ $model->po_code }}</div>
                </div>
                @bind($model ?? null)
                    <x-input col="4" name="po_tanggal" label="Tanggal" type="date" />
                    <x-select col="4" name="po_id_supplier" label="Supplier" :options="$supplierOptions" class="search" />
                    <x-select col="4" name="po_status" label="Status" :options="$statusOptions" />
                    <x-textarea col="8" name="po_keterangan" label="Keterangan" />
                @endbind
            @else
                @bind($model ?? null)
                    <x-input col="4" name="po_code" label="Kode PO" placeholder="Auto generate jika kosong" />
                    <x-input col="4" name="po_tanggal" label="Tanggal" type="date" />
                    <x-select col="4" name="po_id_supplier" label="Supplier" :options="$supplierOptions" class="search" />
                    <x-select col="4" name="po_status" label="Status" :options="$statusOptions" />
                    <x-textarea col="8" name="po_keterangan" label="Keterangan" />
                @endbind
            @endif
        </x-card>

        <x-card label="Diskon & Pajak" class="mt-5">
            <div class="col-span-12 md:col-span-3">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Tipe Diskon</label>
                <select name="po_discount_type" id="po-discount-type" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container">
                    @foreach($discountTypeOptions as $k => $v)
                        <option value="{{ $k }}" @selected((string)$poDiscountTypeVal === (string)$k)>{{ $v }}</option>
                    @endforeach
                </select>
                @error('po_discount_type')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Diskon</label>
                <input type="number" name="po_discount" id="po-discount" value="{{ $poDiscountDisplay }}" min="0" step="0.01" placeholder="0" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" />
                <span class="text-label-caps text-on-surface-variant mt-1 block" id="po-discount-hint">Rp jika nominal, % jika persen</span>
                @error('po_discount')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">PPN Rate (%)</label>
                <input type="number" name="po_ppn_rate" id="po-ppn-rate" value="{{ $poPpnRateDisplay }}" min="0" max="100" step="0.01" placeholder="{{ $ppnRateDefault ?? 11 }}" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" />
                <span class="text-label-caps text-on-surface-variant mt-1 block">Default .env: {{ $ppnRateDefault ?? 11 }}%</span>
                @error('po_ppn_rate')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">PPH Rate (%)</label>
                <input type="number" name="po_pph_rate" id="po-pph-rate" value="{{ $poPphRateDisplay }}" min="0" max="100" step="0.01" placeholder="{{ $pphRateDefault ?? 2 }}" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" />
                <span class="text-label-caps text-on-surface-variant mt-1 block">Default .env: {{ $pphRateDefault ?? 2 }}%</span>
                @error('po_pph_rate')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
            <div class="col-span-12">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Keterangan Diskon</label>
                <textarea name="po_discount_note" rows="2" placeholder="cth: cashback promo akhir bulan, diskon supplier langganan" class="w-full px-4 py-3 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container resize-none">{{ old('po_discount_note', $model?->po_discount_note ?? '') }}</textarea>
                @error('po_discount_note')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
        </x-card>

        <x-card label="Detail Produk" class="mt-5">
            <div class="col-span-12">
                <div id="po-details" class="space-y-3">
                    @foreach($details as $idx => $row)
                        @php
                            $rowId = is_array($row) ? ($row['po_detail_id'] ?? $row['id'] ?? '') : ($row->id ?? '');
                            $rowProduct = is_array($row) ? ($row['po_detail_id_product'] ?? '') : ($row->po_detail_id_product ?? '');
                            $rowQty = is_array($row) ? ($row['po_detail_qty'] ?? 1) : ($row->po_detail_qty ?? 1);
                            $rowHargaRaw = is_array($row) ? ($row['po_detail_harga'] ?? '') : ($row->po_detail_harga ?? '');
                            $rowHarga = $rowHargaRaw === '' || $rowHargaRaw === null ? '' : rtrim(rtrim(number_format((float) $rowHargaRaw, 2, ".", ""), "0"), ".");
                            $rowKet = is_array($row) ? ($row['po_detail_keterangan'] ?? '') : ($row->po_detail_keterangan ?? '');
                        @endphp
                        <div class="po-detail-row grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50" data-index="{{ $idx }}">
                            <input type="hidden" name="details[{{ $idx }}][po_detail_id]" value="{{ $rowId }}">
                            <div class="col-span-12 md:col-span-4">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Produk <span class="text-error">*</span></label>
                                <select name="details[{{ $idx }}][po_detail_id_product]" class="po-product-select w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none search">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($productOptions as $pid => $pname)
                                        <option value="{{ $pid }}" @selected((string)$rowProduct === (string)$pid)>{{ $pname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-6 md:col-span-2">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Qty <span class="text-error">*</span></label>
                                <input type="number" name="details[{{ $idx }}][po_detail_qty]" value="{{ $rowQty }}" min="1" class="po-qty w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            </div>
                            <div class="col-span-6 md:col-span-2">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Harga</label>
                                <input type="number" name="details[{{ $idx }}][po_detail_harga]" value="{{ $rowHarga }}" min="0" step="0.01" placeholder="Otomatis (modal)" class="po-harga w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            </div>
                            <div class="col-span-10 md:col-span-3">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Keterangan</label>
                                <input type="text" name="details[{{ $idx }}][po_detail_keterangan]" value="{{ $rowKet }}" placeholder="Opsional" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            </div>
                            <div class="col-span-2 md:col-span-1 flex justify-end">
                                <button type="button" onclick="removePoRow(this)" class="btn btn-soft w-full h-12 text-error" title="Hapus baris">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                            <div class="col-span-12 text-right">
                                <span class="po-row-subtotal text-xs font-mono text-on-surface-variant">Subtotal: Rp 0</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 flex flex-wrap gap-2 items-center justify-between">
                    <button type="button" onclick="addPoRow()" class="btn btn-soft btn-sm">
                        <span class="material-symbols-outlined text-base">add</span> Tambah Produk
                    </button>
                    <span id="po-grand-total" class="font-bold text-sm">Grand Total: Rp 0</span>
                </div>
                @error('details')<p class="text-error text-xs mt-2">{{ $message }}</p>@enderror
            </div>
        </x-card>

        <x-card label="Ringkasan" class="mt-5">
            <div class="col-span-12 space-y-2 text-sm" id="po-summary">
                <div class="flex justify-between"><span class="text-on-surface-variant">Subtotal</span><span id="po-summary-subtotal" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">Diskon</span><span id="po-summary-discount" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">DPP (Subtotal - Diskon)</span><span id="po-summary-dpp" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">PPN</span><span id="po-summary-ppn" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">PPH</span><span id="po-summary-pph" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between border-t border-outline-variant pt-2 mt-2"><span class="font-bold">Grand Total</span><span id="po-summary-grand" class="font-mono font-bold text-primary">Rp 0</span></div>
            </div>
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>

    <template id="po-row-template">
        <div class="po-detail-row grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50" data-index="__IDX__">
            <input type="hidden" name="details[__IDX__][po_detail_id]" value="">
            <div class="col-span-12 md:col-span-4">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Produk <span class="text-error">*</span></label>
                <select name="details[__IDX__][po_detail_id_product]" class="po-product-select w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($productOptions as $pid => $pname)
                        <option value="{{ $pid }}">{{ $pname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Qty <span class="text-error">*</span></label>
                <input type="number" name="details[__IDX__][po_detail_qty]" value="1" min="1" class="po-qty w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Harga</label>
                <input type="number" name="details[__IDX__][po_detail_harga]" value="" min="0" step="0.01" placeholder="Otomatis (modal)" class="po-harga w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div class="col-span-10 md:col-span-3">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Keterangan</label>
                <input type="text" name="details[__IDX__][po_detail_keterangan]" value="" placeholder="Opsional" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div class="col-span-2 md:col-span-1 flex justify-end">
                <button type="button" onclick="removePoRow(this)" class="btn btn-soft w-full h-12 text-error" title="Hapus baris">
                    <span class="material-symbols-outlined text-lg">delete</span>
                </button>
            </div>
            <div class="col-span-12 text-right">
                <span class="po-row-subtotal text-xs font-mono text-on-surface-variant">Subtotal: Rp 0</span>
            </div>
        </div>
    </template>

    <script>
        const PO_PRICES = {!! $productPricesJson !!};
        (function(){
            document.querySelectorAll('.po-harga').forEach(el=>{
                const v = el.value;
                if(v !== '' && v.indexOf(',') !== -1){
                    el.value = v.replace(',', '.');
                    el.value = parseFloat(el.value).toString();
                    if(el.value === 'NaN') el.value = '';
                } else if(v !== '' && !isNaN(parseFloat(v))){
                    const n = parseFloat(v);
                    el.value = Number.isInteger(n) ? String(Math.trunc(n)) : n.toString().replace(/0+$/, '').replace(/\.$/, '');
                }
            });
        })();
        function formatQty(v){
            const n = parseFloat(v);
            if(isNaN(n)) return '0';
            if(Math.abs(n - Math.trunc(n)) < 0.001) return Math.trunc(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            let s = n.toFixed(3).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            s = s.replace(/0+$/, '').replace(/,$/, '');
            return s;
        }
        function fmtRp(n){ return 'Rp ' + formatQty(n); }
        function calcRow(row){
            const qty = parseInt(row.querySelector('.po-qty')?.value || 0, 10) || 0;
            let harga = parseFloat(row.querySelector('.po-harga')?.value || '');
            if (isNaN(harga) || row.querySelector('.po-harga').value === '') {
                const pid = row.querySelector('.po-product-select')?.value;
                harga = pid && PO_PRICES[pid] != null ? parseFloat(PO_PRICES[pid]) : 0;
            }
            const sub = qty * (isNaN(harga)?0:harga);
            const el = row.querySelector('.po-row-subtotal');
            if(el) el.textContent = 'Subtotal: ' + fmtRp(sub);
            return sub;
        }
        function calcSubtotal(){
            let total = 0;
            document.querySelectorAll('.po-detail-row').forEach(r=> total += calcRow(r));
            return total;
        }
        function updateSummary(){
            const subtotal = calcSubtotal();
            const discountType = document.getElementById('po-discount-type')?.value || 'nominal';
            const discountRaw = parseFloat(document.getElementById('po-discount')?.value || '0') || 0;
            const ppnRate = parseFloat(document.getElementById('po-ppn-rate')?.value || '0') || 0;
            const pphRate = parseFloat(document.getElementById('po-pph-rate')?.value || '0') || 0;
            const discountAmount = discountType === 'percent' ? Math.min(subtotal * discountRaw / 100, subtotal) : Math.min(discountRaw, subtotal);
            const dpp = Math.max(0, subtotal - discountAmount);
            const ppn = dpp * ppnRate / 100;
            const pph = dpp * pphRate / 100;
            const grand = dpp + ppn + pph;
            const gt = document.getElementById('po-grand-total');
            if(gt) gt.textContent = 'Grand Total: ' + fmtRp(grand);
            const set = (id, v) => { const el = document.getElementById(id); if(el) el.textContent = fmtRp(v); };
            set('po-summary-subtotal', subtotal);
            const discLabel = discountType === 'percent' ? fmtRp(discountAmount) + ' (' + (isNaN(discountRaw)?'0':discountRaw) + '%)' : fmtRp(discountAmount);
            const discEl = document.getElementById('po-summary-discount');
            if(discEl) discEl.textContent = '- ' + discLabel;
            set('po-summary-dpp', dpp);
            set('po-summary-ppn', ppn);
            set('po-summary-pph', pph);
            const grandEl = document.getElementById('po-summary-grand');
            if(grandEl) grandEl.textContent = fmtRp(grand);
        }
        function calcGrand(){ updateSummary(); }
        function addPoRow(){
            const wrap = document.getElementById('po-details');
            const tpl = document.getElementById('po-row-template').innerHTML;
            const idx = wrap.querySelectorAll('.po-detail-row').length;
            const html = tpl.replaceAll('__IDX__', idx);
            wrap.insertAdjacentHTML('beforeend', html);
            updateSummary();
        }
        function removePoRow(btn){
            const wrap = document.getElementById('po-details');
            if(wrap.querySelectorAll('.po-detail-row').length <= 1){ alert('Minimal 1 produk'); return; }
            btn.closest('.po-detail-row')?.remove();
            wrap.querySelectorAll('.po-detail-row').forEach((row,i)=>{
                row.dataset.index = i;
                row.querySelectorAll('[name]').forEach(el=>{
                    el.name = el.name.replace(/details\[\d+\]/, 'details['+i+']');
                });
            });
            updateSummary();
        }
        document.addEventListener('input', e=>{
            if(e.target.closest('.po-detail-row') || ['po-discount','po-discount-type','po-ppn-rate','po-pph-rate'].includes(e.target.id)) updateSummary();
        });
        document.addEventListener('change', e=>{
            if(e.target.classList.contains('po-product-select')){
                const row = e.target.closest('.po-detail-row');
                const hargaInput = row?.querySelector('.po-harga');
                const wasEmpty = !hargaInput || hargaInput.value === '' || hargaInput.value === '0';
                const hadManualEdit = hargaInput?.dataset.userEdited === '1';
                if(hargaInput && wasEmpty && !hadManualEdit){
                    const pid = e.target.value;
                    if(pid && PO_PRICES[pid] != null) hargaInput.value = PO_PRICES[pid];
                }
                updateSummary();
            }
            if(['po-discount-type','po-ppn-rate','po-pph-rate','po-discount'].includes(e.target.id)) updateSummary();
        });
        document.addEventListener('input', e=>{
            if(e.target.classList.contains('po-harga')){
                e.target.dataset.userEdited = '1';
            }
        });
        updateSummary();
    </script>
</x-layouts::app>
