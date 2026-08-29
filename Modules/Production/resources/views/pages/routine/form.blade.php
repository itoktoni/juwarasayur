<?php /** @var Modules\Production\Models\Production $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Produksi Rutin'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Work Order Produksi Rutin">
            @bind($model ?? null)
                <x-input col="4" name="production_code" label="Kode WO" placeholder="Otomatis jika kosong" />
                <x-select col="4" name="production_status" label="Status" :options="$statusOptions" />
                <div class="md:col-span-4 col-span-12">
                    <label class="font-label-md text-label-md text-on-surface-variant block mb-2">Qty Hasil <span class="text-error">*</span></label>
                    <input type="number" min="1" name="production_qty_output" value="{{ old('production_qty_output', $model?->production_qty_output ?? 1) }}"
                        class="w-full h-12 px-3 border border-outline-variant rounded-lg bg-white text-sm" required>
                </div>

                <x-select col="8" name="production_id_product" label="Produk Hasil (Paket)" :options="$productOptions" class="search" required />
            @endbind

            {{-- Bahan baku yang digabung menjadi 1 paket --}}
            <div class="col-span-12 mt-2 pt-4 border-t border-outline-variant">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-on-surface">Bahan Baku yang Digabung <span class="text-error">*</span></p>
                    <button type="button" onclick="window.addWoItem && window.addWoItem()" class="btn btn-soft btn-sm">
                        <span class="material-symbols-outlined text-base">add</span> Tambah Bahan
                    </button>
                </div>
                <div id="wo-items" class="space-y-2"></div>
            </div>

            {{-- Biaya tambahan: parkir, konsumsi, dll --}}
            <div class="col-span-12 mt-2 pt-4 border-t border-outline-variant">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-on-surface">Biaya Tambahan</p>
                    <button type="button" onclick="window.addWoCost && window.addWoCost()" class="btn btn-soft btn-sm">
                        <span class="material-symbols-outlined text-base">add</span> Tambah Biaya
                    </button>
                </div>
                <div id="wo-costs" class="space-y-2"></div>
            </div>

            {{-- Estimasi harga modal per unit paket --}}
            <div class="col-span-12 mt-3 p-3 rounded-lg bg-secondary-container/40 border border-outline-variant/60 flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-on-surface-variant font-label-caps text-label-caps uppercase">Estimasi Harga Modal / Paket</p>
                    <p id="modal-estimate" class="text-lg font-bold text-on-surface font-mono">Rp 0</p>
                    <p id="modal-breakdown" class="text-xs text-on-surface-variant"></p>
                </div>
                <span class="material-symbols-outlined text-primary text-3xl">payments</span>
            </div>

            <div class="col-span-12 mt-4">
                <label class="font-label-md text-label-md text-on-surface-variant block mb-2">Catatan</label>
                <textarea name="production_note" rows="1" class="w-full px-3 py-2.5 border border-outline-variant rounded-lg bg-white text-sm">{{ old('production_note', $model?->production_note) }}</textarea>
            </div>

            <div class="col-span-12 mt-2 p-3 rounded-lg bg-surface-container-low/60 border border-outline-variant/60">
                <p class="text-xs text-on-surface-variant">Stok bahan berkurang & stok paket bertambah otomatis saat status diubah menjadi <strong>Selesai</strong>.</p>
            </div>
        </x-card>

        <x-action :model="$model" :action="['save']"/>

        @push('scripts')
        <script>
            // Baris bahan & biaya dinamis + estimasi harga modal live.
            (() => {
                const products = @json(collect($productOptions));
                const prices = @json(collect($productPrices));
                let rowIndex = 0;
                let costIndex = 0;

                function itemRowHtml(idx, data = {}) {
                    let options = '';
                    for (const [pid, pname] of Object.entries(products)) {
                        const selected = (data.product_id ?? '') == pid ? 'selected' : '';
                        options += `<option value="${pid}" ${selected}>${pname}</option>`;
                    }

                    return `
                    <div class="wo-item flex gap-2 items-center" data-row="${idx}">
                        <select name="item_id_product[]" class="flex-1 h-11 px-3 border border-outline-variant rounded-lg bg-white text-sm">${options}</select>
                        <input type="number" min="1" name="item_qty[]" value="${data.qty ?? 1}" placeholder="Qty"
                            class="w-28 h-11 px-3 border border-outline-variant rounded-lg bg-white text-sm font-mono" required>
                        <button type="button" onclick="this.closest('.wo-item').remove(); window.updateModalEstimate && window.updateModalEstimate()" class="btn btn-soft h-11 shrink-0 text-error" title="Hapus">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </div>`;
                }

                function costRowHtml(idx, data = {}) {
                    return `
                    <div class="wo-cost flex gap-2 items-center" data-row="${idx}">
                        <input type="text" name="cost_nama[]" value="${data.nama ?? ''}" placeholder="Nama biaya (parkir, konsumsi, dll)"
                            class="flex-1 h-11 px-3 border border-outline-variant rounded-lg bg-white text-sm">
                        <input type="number" min="0" step="1" name="cost_nominal[]" value="${data.nominal ?? ''}" placeholder="Nominal"
                            class="w-40 h-11 px-3 border border-outline-variant rounded-lg bg-white text-sm font-mono"
                            oninput="window.updateModalEstimate && window.updateModalEstimate()">
                        <button type="button" onclick="this.closest('.wo-cost').remove(); window.updateModalEstimate && window.updateModalEstimate()" class="btn btn-soft h-11 shrink-0 text-error" title="Hapus">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </div>`;
                }

                window.addWoItem = function (data = {}) {
                    document.getElementById('wo-items').insertAdjacentHTML('beforeend', itemRowHtml(rowIndex++, data));
                    window.updateModalEstimate && window.updateModalEstimate();
                };

                window.addWoCost = function (data = {}) {
                    document.getElementById('wo-costs').insertAdjacentHTML('beforeend', costRowHtml(costIndex++, data));
                    window.updateModalEstimate && window.updateModalEstimate();
                };

                // Estimasi harga modal per unit paket (live, sebelum completed)
                window.updateModalEstimate = function () {
                    let bahanTotal = 0;

                    document.querySelectorAll('#wo-items .wo-item').forEach(row => {
                        const pid = row.querySelector('select[name="item_id_product[]"]')?.value;
                        const qty = parseInt(row.querySelector('input[name="item_qty[]"]')?.value || 0, 10);
                        bahanTotal += qty * parseFloat(prices[pid] ?? 0);
                    });

                    let extraTotal = 0;
                    document.querySelectorAll('#wo-costs .wo-cost input[name="cost_nominal[]"]').forEach(input => {
                        extraTotal += parseFloat(input.value || 0);
                    });

                    const qtyOutput = Math.max(1, parseInt(document.querySelector('input[name="production_qty_output"]')?.value || 1, 10));
                    const modal = (bahanTotal + extraTotal) / qtyOutput;
                    const rp = n => 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 2 });

                    const el = document.getElementById('modal-estimate');
                    if (el) el.textContent = rp(modal);

                    const breakdown = document.getElementById('modal-breakdown');
                    if (breakdown) {
                        breakdown.textContent = `Bahan: ${rp(bahanTotal)} + Biaya tambahan: ${rp(extraTotal)} ÷ Qty ${qtyOutput}`;
                    }
                };

                // Minimal 1 baris bahan untuk create
                if (!document.querySelectorAll('#wo-items .wo-item').length) {
                    window.addWoItem();
                }

                // Repopulasi bahan saat update
                @foreach (($model?->has_items ?? collect()) as $item)
                    window.addWoItem({ product_id: {{ $item->production_item_id_product }}, qty: {{ $item->production_item_qty }} });
                @endforeach

                // Repopulasi biaya tambahan saat update
                @foreach (($model?->has_costs ?? collect()) as $cost)
                    window.addWoCost({ nama: {{ json_encode($cost->production_cost_nama) }}, nominal: {{ json_encode((float) $cost->production_cost_nominal) }} });
                @endforeach

                // Hitung ulang estimasi saat bahan/qty berubah
                document.addEventListener('change', e => {
                    if (e.target.closest('#wo-items') || e.target.name === 'production_qty_output') {
                        window.updateModalEstimate && window.updateModalEstimate();
                    }
                });
                document.addEventListener('input', e => {
                    if (e.target.name === 'item_qty[]' || e.target.name === 'production_qty_output') {
                        window.updateModalEstimate && window.updateModalEstimate();
                    }
                });

                window.updateModalEstimate();

                // Jalankan saat load normal maupun setelah navigasi wire:navigate (Livewire)
                document.addEventListener('livewire:navigated', () => {
                    if (!document.querySelectorAll('#wo-items .wo-item').length) {
                        window.addWoItem && window.addWoItem();
                    }
                    window.updateModalEstimate && window.updateModalEstimate();
                });
            })();
        </script>
        @endpush
    </x-form>
</x-layouts::app>
