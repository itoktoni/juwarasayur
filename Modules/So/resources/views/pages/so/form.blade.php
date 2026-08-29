<?php
use Modules\So\Models\So;

/** @var So $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Sales Order'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    @php
        $isEdit = isset($model) && $model->exists;
        $details = $isEdit && $model->relationLoaded('has_details') ? $model->has_details : collect(old('details', []));
        if ($details->isEmpty() && !$isEdit) {
            $details = collect([['so_detail_id_product' => '', 'so_detail_qty' => 1, 'so_detail_harga' => '', 'so_detail_keterangan' => '']]);
        }
        $trimVal = fn($v) => $v === '' || $v === null ? '' : rtrim(rtrim(number_format((float)$v, 2, ".", ""), "0"), ".");
        $shippingFeeVal = old('so_shipping_fee', $model?->so_shipping_fee ?? 0);
        $warehouseJson = json_encode($warehouse ?? []);
        $codLocationsJson = json_encode(collect($codLocations ?? [])->values()->all());
    @endphp

    <x-form :model="$model">
        <x-card label="Informasi Sales Order">
            <div class="col-span-12 md:col-span-4">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Kode SO</label>
                <div class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center font-mono text-sm">{{ $model->so_code ?? 'Auto generate' }}</div>
            </div>
            @bind($model ?? null)
                <x-input col="4" name="so_tanggal" label="Tanggal" type="date" />
                <x-select col="4" name="so_id_customer" label="Customer" :options="$customerOptions" class="search" placeholder="-- Pilih Customer --" />
                @if(!empty($resellerOptions))
                    <x-select col="6" name="so_id_reseller" label="Reseller" :options="$resellerOptions" class="search" placeholder="-- User Login (Saya) --" helper="Kosongkan untuk memakai user login sebagai reseller" />
                @endif
                <x-select col="6" name="so_status" label="Status" :options="$statusOptions" />
                <x-textarea col="12" name="so_keterangan" label="Keterangan" />
            @endbind
        </x-card>

        <x-card label="Detail Produk" class="mt-5">
            <div class="col-span-12">
                <div id="so-details" class="space-y-3">
                    @foreach($details as $idx => $row)
                        @php
                            $rowId = is_array($row) ? ($row['so_detail_id'] ?? $row['id'] ?? '') : ($row->id ?? '');
                            $rowProduct = is_array($row) ? ($row['so_detail_id_product'] ?? '') : ($row->so_detail_id_product ?? '');
                            $rowQty = is_array($row) ? ($row['so_detail_qty'] ?? 1) : ($row->so_detail_qty ?? 1);
                            $rowHargaRaw = is_array($row) ? ($row['so_detail_harga'] ?? '') : ($row->so_detail_harga ?? '');
                            $rowHarga = $rowHargaRaw === '' || $rowHargaRaw === null ? '' : rtrim(rtrim(number_format((float) $rowHargaRaw, 2, ".", ""), "0"), ".");
                            $rowKet = is_array($row) ? ($row['so_detail_keterangan'] ?? '') : ($row->so_detail_keterangan ?? '');
                        @endphp
                        <div class="so-detail-row grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50" data-index="{{ $idx }}">
                            <input type="hidden" name="details[{{ $idx }}][so_detail_id]" value="{{ $rowId }}">
                            <div class="col-span-12 md:col-span-5">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Produk <span class="text-error">*</span></label>
                                <select name="details[{{ $idx }}][so_detail_id_product]" class="so-product-select w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none search">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($productOptions as $pid => $pname)
                                        <option value="{{ $pid }}" @selected((string)$rowProduct === (string)$pid)>{{ $pname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-6 md:col-span-2">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Qty <span class="text-error">*</span></label>
                                <input type="number" name="details[{{ $idx }}][so_detail_qty]" value="{{ $rowQty }}" min="1" class="so-qty w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            </div>
                            <div class="col-span-6 md:col-span-3">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Harga</label>
                                <input type="number" name="details[{{ $idx }}][so_detail_harga]" value="{{ $rowHarga }}" min="0" step="1" placeholder="Otomatis" class="so-harga w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            </div>
                            <div class="col-span-10 md:col-span-2">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Ket.</label>
                                <input type="text" name="details[{{ $idx }}][so_detail_keterangan]" value="{{ $rowKet }}" placeholder="Opsional" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            </div>
                            <div class="col-span-2 flex justify-end">
                                <button type="button" onclick="removeSoRow(this)" class="btn btn-soft w-full h-12 text-error" title="Hapus baris">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                            <div class="col-span-12 text-right">
                                <span class="so-row-subtotal text-xs font-mono text-on-surface-variant">Subtotal: Rp 0</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 flex flex-wrap gap-2 items-center justify-between">
                    <button type="button" onclick="addSoRow()" class="btn btn-soft btn-sm">
                        <span class="material-symbols-outlined text-base">add</span> Tambah Produk
                    </button>
                    <span id="so-subtotal" class="font-bold text-sm">Subtotal: Rp 0</span>
                </div>
                @error('details')<p class="text-error text-xs mt-2">{{ $message }}</p>@enderror
            </div>
        </x-card>

        <x-card label="Diskon & Pajak (Opsional)" class="mt-5">
            <div class="col-span-12 md:col-span-3">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Tipe Diskon</label>
                <select name="so_discount_type" id="so-discount-type" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container">
                    @foreach($discountTypeOptions as $dk => $dv)
                        <option value="{{ $dk }}" @selected(old('so_discount_type', $model?->so_discount_type ?? 'nominal') === $dk)>{{ $dv }}</option>
                    @endforeach
                </select>
                @error('so_discount_type')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Diskon</label>
                <input type="number" name="so_discount" id="so-discount" value="{{ old('so_discount', $model?->so_discount !== null ? $trimVal($model?->so_discount) : '') }}" min="0" step="1" placeholder="0"
                    class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" />
                @error('so_discount')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">PPN Rate (%)</label>
                <input type="number" name="so_ppn_rate" id="so-ppn-rate" value="{{ old('so_ppn_rate', $model?->so_ppn_rate !== null ? rtrim(rtrim((string) (float) $model?->so_ppn_rate, '0'), '.') : '0') }}" min="0" max="100" step="1" placeholder="0"
                    class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" />
                @error('so_ppn_rate')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
            <div class="col-span-12 md:col-span-3">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">PPH Rate (%)</label>
                <input type="number" name="so_pph_rate" id="so-pph-rate" value="{{ old('so_pph_rate', $model?->so_pph_rate !== null ? rtrim(rtrim((string) (float) $model?->so_pph_rate, '0'), '.') : '0') }}" min="0" max="100" step="1" placeholder="0"
                    class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" />
                @error('so_pph_rate')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
            <div class="col-span-12">
                <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Keterangan Diskon</label>
                <textarea name="so_discount_note" rows="2" placeholder="cth: promo member, diskon volume"
                    class="w-full px-4 py-3 bg-white border border-outline-variant rounded-lg font-body-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container resize-none">{{ old('so_discount_note', $model?->so_discount_note ?? '') }}</textarea>
                @error('so_discount_note')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
            </div>
        </x-card>

        <x-card label="Pengiriman" class="mt-5">
            @bind($model ?? null)
                <div class="col-span-12">
                    <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Metode Pengiriman</label>
                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                        @foreach($shippingMethodOptions as $methodVal => $methodLabel)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="so_shipping_method" value="{{ $methodVal }}" @checked(old('so_shipping_method', $model?->so_shipping_method ?? 'pickup') === (string) $methodVal) class="w-4 h-4 border-outline-variant text-primary focus:ring-primary-container">
                                <span class="font-body-sm text-body-sm text-on-surface">{{ $methodLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('so_shipping_method')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
                </div>

                {{-- Pickup --}}
                <div class="col-span-12 shipping-pane" data-method="pickup">
                    <div class="p-3 rounded-lg border border-outline-variant bg-surface-container-low/50 text-sm text-on-surface-variant">
                        Diambil di gudang: <strong>{{ $warehouse['name'] ?? '-' }}</strong>{{ !empty($warehouse['address']) ? ' — '.$warehouse['address'] : '' }}. Tanpa biaya pengiriman.
                    </div>
                </div>

                {{-- COD --}}
                <div class="col-span-12 md:col-span-6 shipping-pane hidden" data-method="cod">
                    <label class="text-xs font-bold text-on-surface-variant block mb-1">Lokasi COD <span class="text-error">*</span></label>
                    <select name="so_cod_location" id="so-cod-location" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        <option value="">-- Pilih Lokasi COD --</option>
                        @foreach($codLocationOptions as $loc)
                            <option value="{{ $loc }}" @selected(old('so_cod_location', $model?->so_cod_location) === $loc)>{{ $loc }}</option>
                        @endforeach
                    </select>
                    @error('so_cod_location')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
                </div>
                <div class="col-span-12 md:col-span-6 shipping-pane hidden" data-method="cod">
                    <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Ongkir COD</label>
                    <div id="so-cod-fee" class="w-full h-12 px-4 bg-surface-container text-on-surface-variant border border-outline-variant rounded-lg flex items-center font-mono text-sm">{{ formatAngka((int) ($model?->so_shipping_method === 'cod' ? $shippingFeeVal : 0), 'Rp') }}</div>
                </div>

                {{-- Delivery --}}
                <div class="col-span-12 shipping-pane hidden" data-method="delivery">
                    <x-textarea name="so_address" label="Alamat Pengiriman" rows="2" />
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <div>
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Latitude <span class="text-error">*</span></label>
                            <input type="number" step="any" name="so_lat" id="so-lat" value="{{ old('so_lat', $model?->so_lat) }}" placeholder="-7.644872" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Longitude <span class="text-error">*</span></label>
                            <input type="number" step="any" name="so_lng" id="so-lng" value="{{ old('so_lng', $model?->so_lng) }}" placeholder="112.904528" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="text-xs font-bold text-on-surface-variant block mb-1">Cari Lokasi di Peta</label>
                        <div class="flex gap-2">
                            <input type="text" id="so-map-search" placeholder="cth: nama jalan, kota, atau tempat"
                                class="flex-1 h-11 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchSoLocation(); }">
                            <button type="button" onclick="searchSoLocation()" id="so-map-search-btn" class="btn btn-soft h-11 shrink-0">
                                <span class="material-symbols-outlined text-base">search</span> Cari
                            </button>
                            <button type="button" onclick="useMyLocation()" class="btn btn-soft h-11 shrink-0" title="Gunakan lokasi GPS saya">
                                <span class="material-symbols-outlined text-base">my_location</span>
                            </button>
                        </div>
                        <div id="so-search-results" class="hidden mt-2 max-h-44 overflow-y-auto rounded-lg border border-outline-variant divide-y divide-outline-variant bg-white"></div>
                        @error('so_lat')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
                        @error('so_address')<span class="font-label-caps text-label-caps text-error mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    <div id="so-map" class="w-full h-80 rounded-lg border border-outline-variant mt-3 z-0"></div>
                    <p class="text-xs text-on-surface-variant mt-1">Klik peta atau geser pin untuk menentukan titik pengiriman.</p>
                    <div class="mt-2 text-xs font-mono text-on-surface-variant" id="so-delivery-info">Pilih titik lokasi untuk hitung ongkir.</div>
                </div>
            @endbind

            <input type="hidden" name="so_shipping_fee" id="so-shipping-fee" value="{{ $trimVal($shippingFeeVal) ?: 0 }}">
        </x-card>

        <x-card label="Ringkasan" class="mt-5">
            <div class="col-span-12 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-on-surface-variant">Subtotal</span><span id="so-summary-subtotal" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">Diskon</span><span id="so-summary-discount" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">DPP (Subtotal - Diskon)</span><span id="so-summary-dpp" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">PPN</span><span id="so-summary-ppn" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">PPH</span><span id="so-summary-pph" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-on-surface-variant">Ongkir</span><span id="so-summary-shipping" class="font-mono font-medium">Rp 0</span></div>
                <div class="flex justify-between border-t border-outline-variant pt-2 mt-2"><span class="font-bold">Grand Total</span><span id="so-summary-grand" class="font-mono font-bold text-primary">Rp 0</span></div>
            </div>
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>

    <template id="so-row-template">
        <div class="so-detail-row grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50" data-index="__IDX__">
            <input type="hidden" name="details[__IDX__][so_detail_id]" value="">
            <div class="col-span-12 md:col-span-5">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Produk <span class="text-error">*</span></label>
                <select name="details[__IDX__][so_detail_id_product]" class="so-product-select w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($productOptions as $pid => $pname)
                        <option value="{{ $pid }}">{{ $pname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Qty <span class="text-error">*</span></label>
                <input type="number" name="details[__IDX__][so_detail_qty]" value="1" min="1" class="so-qty w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div class="col-span-6 md:col-span-3">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Harga</label>
                <input type="number" name="details[__IDX__][so_detail_harga]" value="" min="0" step="1" placeholder="Otomatis" class="so-harga w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div class="col-span-10 md:col-span-2">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Ket.</label>
                <input type="text" name="details[__IDX__][so_detail_keterangan]" value="" placeholder="Opsional" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
            </div>
            <div class="col-span-2 flex justify-end">
                <button type="button" onclick="removeSoRow(this)" class="btn btn-soft w-full h-12 text-error" title="Hapus baris">
                    <span class="material-symbols-outlined text-lg">delete</span>
                </button>
            </div>
            <div class="col-span-12 text-right">
                <span class="so-row-subtotal text-xs font-mono text-on-surface-variant">Subtotal: Rp 0</span>
            </div>
        </div>
    </template>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        // IIFE + AbortController: cegah "already been declared" saat Livewire
        // navigate menukar HTML halaman, dan hapus listener document lama.
        if (window.__soFormAbort) { window.__soFormAbort.abort(); }
        const __abort = new AbortController();
        window.__soFormAbort = __abort;
        const __signal = __abort.signal;

        (() => {
        const SO_PRICES = {!! json_encode($productPrices ?? []) !!};
        const SO_RESELLER_FEES = {!! json_encode($productResellerFees ?? []) !!};
        const SO_USER_TYPES = {!! json_encode($resellerTypes ?? []) !!};
        const AUTH_TYPE = '{{ auth()->user()?->type ?? '' }}';
        const SO_WAREHOUSE = {!! $warehouseJson !!};
        const SO_COD_LOCATIONS = {!! $codLocationsJson !!};
        const SO_SHIPPING_COST_URL = '{{ route("so-so.getShippingCost") }}';
        const SO_COD_FEE_URL = '{{ route("so-so.getCodFee") }}';

        function fmtRp(n){ return 'Rp ' + (Math.round(n)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }

        // --- Harga otomatis per-role pemilik order (so_id_reseller) ---
        // customer/user biasa: harga dasar; reseller: diskon reseller_fee_percent;
        // affiliator: harga tetap (komisi di-snapshot server-side saat simpan).
        function currentRole(){
            const sel = document.querySelector('select[name="so_id_reseller"]');
            if(sel && sel.value && SO_USER_TYPES[sel.value]) return SO_USER_TYPES[sel.value];
            return (AUTH_TYPE === 'reseller' || AUTH_TYPE === 'affiliator') ? AUTH_TYPE : 'customer';
        }

        function autoPriceFor(pid){
            if(!pid || SO_PRICES[pid] == null) return null;
            const base = parseFloat(SO_PRICES[pid]);
            if(currentRole() === 'reseller'){
                const pct = parseFloat(SO_RESELLER_FEES[pid]);
                if(!isNaN(pct) && pct > 0) return base * (1 - pct / 100);
            }
            return base;
        }

        function trimPrice(n){ return String(parseFloat(n.toFixed(2))); }

        function hargaAutoEligible(el){
            // Harga isi-an manual user tidak pernah ditimpa
            return el.dataset.manual !== '1' && (el.value === '' || el.value === '0' || el.dataset.auto === '1');
        }

        function fillAutoHarga(el){
            if(!hargaAutoEligible(el)) return;
            const pid = el.closest('.so-detail-row')?.querySelector('.so-product-select')?.value;
            const p = autoPriceFor(pid);
            if(p == null) return;
            el.value = trimPrice(p);
            el.dataset.auto = '1';
        }

        function refreshAutoHarga(){
            document.querySelectorAll('.so-detail-row .so-harga').forEach(fillAutoHarga);
        }

        function calcRow(row){
            const qty = parseInt(row.querySelector('.so-qty')?.value || 0, 10) || 0;
            let harga = parseFloat(row.querySelector('.so-harga')?.value || '');
            if (isNaN(harga)) {
                const pid = row.querySelector('.so-product-select')?.value;
                harga = autoPriceFor(pid) ?? 0;
            }
            const sub = qty * (isNaN(harga)?0:harga);
            const el = row.querySelector('.so-row-subtotal');
            if(el) el.textContent = 'Subtotal: ' + fmtRp(sub);
            return sub;
        }

        function calcSubtotal(){
            let total = 0;
            document.querySelectorAll('.so-detail-row').forEach(r=> total += calcRow(r));
            return total;
        }

        function currentShippingFee(){
            return parseFloat(document.getElementById('so-shipping-fee')?.value || '0') || 0;
        }

        function updateSummary(){
            const subtotal = calcSubtotal();
            const fee = currentShippingFee();
            const discountType = document.getElementById('so-discount-type')?.value || 'nominal';
            const discountRaw = parseFloat(document.getElementById('so-discount')?.value || '0') || 0;
            const ppnRate = parseFloat(document.getElementById('so-ppn-rate')?.value || '0') || 0;
            const pphRate = parseFloat(document.getElementById('so-pph-rate')?.value || '0') || 0;
            const discountAmount = discountType === 'percent' ? Math.min(subtotal * discountRaw / 100, subtotal) : Math.min(discountRaw, subtotal);
            const dpp = Math.max(0, subtotal - discountAmount);
            const ppn = dpp * ppnRate / 100;
            const pph = dpp * pphRate / 100;
            const grand = dpp + ppn + pph + fee;
            const set = (id, v) => { const el = document.getElementById(id); if(el) el.textContent = fmtRp(v); };
            set('so-subtotal', subtotal);
            set('so-summary-subtotal', subtotal);
            const discEl = document.getElementById('so-summary-discount');
            if(discEl) discEl.textContent = '- ' + fmtRp(discountAmount) + (discountType === 'percent' ? ` (${discountRaw}%)` : '');
            set('so-summary-dpp', dpp);
            set('so-summary-ppn', ppn);
            set('so-summary-pph', pph);
            set('so-summary-shipping', fee);
            set('so-summary-grand', grand);
        }

        // --- Shipping panes ---
        function selectedMethod(){
            const checked = document.querySelector('input[name="so_shipping_method"]:checked');
            return checked ? checked.value : 'pickup';
        }

        function renderShippingPanes(){
            const method = selectedMethod();
            document.querySelectorAll('.shipping-pane').forEach(p => {
                p.classList.toggle('hidden', p.dataset.method !== method);
            });
            if(method === 'delivery') initSoMap();
            updateSummary();
        }

        async function fetchShippingCost(lat, lng){
            const info = document.getElementById('so-delivery-info');
            if(info) info.textContent = 'Menghitung jarak & ongkir...';
            try {
                const res = await fetch(`${SO_SHIPPING_COST_URL}?lat=${lat}&lng=${lng}`);
                const json = await res.json();
                if(!json.status){
                    if(info) info.textContent = json.message || 'Gagal menghitung ongkir.';
                    document.getElementById('so-shipping-fee').value = '0';
                } else {
                    document.getElementById('so-shipping-fee').value = Math.round(json.shipping_fee);
                    if(info) info.textContent = `Jarak: ${json.distance_km} km — Ongkir: ${fmtRp(json.shipping_fee)}`;
                }
            } catch(e){
                if(info) info.textContent = 'Gagal menghubungi server ongkir.';
                document.getElementById('so-shipping-fee').value = '0';
            }
            updateSummary();
        }

        async function fetchCodFee(location){
            const el = document.getElementById('so-cod-fee');
            try {
                const res = await fetch(`${SO_COD_FEE_URL}?location=${encodeURIComponent(location)}`);
                const json = await res.json();
                const fee = json.status ? json.shipping_fee : 0;
                document.getElementById('so-shipping-fee').value = Math.round(fee);
                if(el) el.textContent = fmtRp(fee);
            } catch(e){ if(el) el.textContent = 'Rp 0'; }
            updateSummary();
        }

        window.useMyLocation = function(){
            if(!navigator.geolocation){ alert('Geolocation tidak didukung browser ini.'); return; }
            navigator.geolocation.getCurrentPosition(pos => {
                setSoPoint(pos.coords.latitude, pos.coords.longitude);
            }, err => alert('Tidak bisa mengambil lokasi: ' + err.message));
        };

        // ================= Peta OpenStreetMap (Leaflet) =================
        let soMap = null;
        let soMarker = null;
        const NOMINATIM_SEARCH = 'https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=6&q=';
        const NOMINATIM_REVERSE = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&addressdetails=1&';

        function initSoMap(){
            if (soMap) { soMap.invalidateSize(); return; }
            if (!document.getElementById('so-map')) return;

            const startLat = parseFloat(document.getElementById('so-lat').value) || SO_WAREHOUSE.lat || -7.644872;
            const startLng = parseFloat(document.getElementById('so-lng').value) || SO_WAREHOUSE.lng || 112.904528;

            soMap = L.map('so-map').setView([startLat, startLng], 13);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(soMap);

            soMarker = L.marker([startLat, startLng], { draggable: true }).addTo(soMap);

            // Pin berdasarkan titik yang diklik di peta
            soMap.on('click', e => setSoPoint(e.latlng.lat, e.latlng.lng));

            // Pin digeser → update titik + ongkir
            soMarker.on('dragend', () => {
                const pos = soMarker.getLatLng();
                setSoPoint(pos.lat, pos.lng, true);
            });

            if (document.getElementById('so-lat').value && document.getElementById('so-lng').value) {
                fetchShippingCost(startLat, startLng);
            }
        }

        function moveSoMarker(lat, lng){
            initSoMap();
            if (!soMap || !soMarker) return;
            soMarker.setLatLng([lat, lng]);
            soMap.setView([lat, lng], Math.max(soMap.getZoom(), 15));
        }

        function setSoPoint(lat, lng, skipMove){
            document.getElementById('so-lat').value = parseFloat(lat).toFixed(7);
            document.getElementById('so-lng').value = parseFloat(lng).toFixed(7);
            if (!skipMove && soMap && soMarker) {
                soMarker.setLatLng([lat, lng]);
                soMap.setView([lat, lng], Math.max(soMap.getZoom(), 15));
            }
            fetchShippingCost(lat, lng);
            reverseGeocode(lat, lng);
        }

        // Isi alamat otomatis dari titik pin (Nominatim reverse)
        async function reverseGeocode(lat, lng){
            try {
                const res = await fetch(`${NOMINATIM_REVERSE}lat=${lat}&lon=${lng}`, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                if (json.display_name) {
                    document.querySelector('[name="so_address"]').value = json.display_name;
                }
            } catch(e) { /* abaikan */ }
        }

        // Cari lokasi via Nominatim (OpenStreetMap)
        window.searchSoLocation = async function(){
            const q = document.getElementById('so-map-search')?.value?.trim();
            const box = document.getElementById('so-search-results');
            const btn = document.getElementById('so-map-search-btn');
            if(!q){ alert('Masukkan nama lokasi / alamat terlebih dahulu.'); return; }
            if(!box) return;

            btn.disabled = true;
            box.innerHTML = '<div class="p-3 text-xs text-on-surface-variant">Mencari lokasi...</div>';
            box.classList.remove('hidden');

            try {
                const res = await fetch(NOMINATIM_SEARCH + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
                const results = await res.json();

                if(!results.length){
                    box.innerHTML = '<div class="p-3 text-xs text-on-surface-variant">Lokasi tidak ditemukan.</div>';
                    return;
                }

                box.innerHTML = '';
                results.forEach(r => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'block w-full text-left px-3 py-2 text-xs hover:bg-surface-container-low cursor-pointer';
                    item.textContent = r.display_name;
                    item.onclick = () => {
                        const lat = parseFloat(r.lat), lng = parseFloat(r.lon);
                        setSoPoint(lat, lng);
                        moveSoMarker(lat, lng);
                        box.classList.add('hidden');
                    };
                    box.appendChild(item);
                });
            } catch(e) {
                box.innerHTML = '<div class="p-3 text-xs text-error">Gagal mencari lokasi.</div>';
            } finally {
                btn.disabled = false;
            }
        };

        // Klik di luar hasil pencarian menutup daftar
        document.addEventListener('click', e => {
            const box = document.getElementById('so-search-results');
            if(box && !e.target.closest('#so-search-results') && !e.target.closest('#so-map-search')) {
                box.classList.add('hidden');
            }
        }, { signal: __signal });

        document.addEventListener('change', e => {
            if(e.target.name === 'so_shipping_method'){ renderShippingPanes(); }
            if(e.target.classList.contains('so-product-select')){
                const row = e.target.closest('.so-detail-row');
                const hargaInput = row?.querySelector('.so-harga');
                if(hargaInput) fillAutoHarga(hargaInput);
                updateSummary();
            }
            // Ganti reseller/affiliator → hitung ulang harga otomatis semua baris
            if(e.target.name === 'so_id_reseller'){
                refreshAutoHarga();
                updateSummary();
            }
            if(e.target.id === 'so-cod-location'){
                if(e.target.value) fetchCodFee(e.target.value);
            }
            if(['so-discount-type', 'so-ppn-rate', 'so-pph-rate', 'so-discount'].includes(e.target.id)) updateSummary();
        }, { signal: __signal });

        document.addEventListener('input', e => {
            // Harga diketik manual → nonaktifkan auto-fill untuk input ini
            if(e.target.classList && e.target.classList.contains('so-harga')){
                e.target.dataset.manual = '1';
                delete e.target.dataset.auto;
            }
            if(e.target.closest('.so-detail-row')) updateSummary();
            if(['so-discount', 'so-discount-type', 'so-ppn-rate', 'so-pph-rate'].includes(e.target.id)) updateSummary();
            if(['so-lat','so-lng'].includes(e.target.id)){
                clearTimeout(window.__soGeoTimer);
                window.__soGeoTimer = setTimeout(() => {
                    const lat = parseFloat(document.getElementById('so-lat')?.value || '');
                    const lng = parseFloat(document.getElementById('so-lng')?.value || '');
                    if(!isNaN(lat) && !isNaN(lng)){
                        moveSoMarker(lat, lng);
                        fetchShippingCost(lat, lng);
                    }
                }, 600);
            }
        }, { signal: __signal });

        // --- Detail rows ---
        window.addSoRow = function(){
            const wrap = document.getElementById('so-details');
            const tpl = document.getElementById('so-row-template').innerHTML;
            const idx = wrap.querySelectorAll('.so-detail-row').length;
            wrap.insertAdjacentHTML('beforeend', tpl.replaceAll('__IDX__', idx));
            updateSummary();
        }
        window.removeSoRow = function(btn){
            const wrap = document.getElementById('so-details');
            if(wrap.querySelectorAll('.so-detail-row').length <= 1){ alert('Minimal 1 produk'); return; }
            btn.closest('.so-detail-row')?.remove();
            wrap.querySelectorAll('.so-detail-row').forEach((row,i)=>{
                row.dataset.index = i;
                row.querySelectorAll('[name]').forEach(el=>{
                    el.name = el.name.replace(/details\[\d+\]/, 'details['+i+']');
                });
            });
            updateSummary();
        };

        renderShippingPanes();
        updateSummary();
        })();
    </script>
</x-layouts::app>
