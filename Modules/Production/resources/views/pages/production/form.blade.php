<?php /** @var Modules\Production\Models\Production $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Production'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Work Order Produksi">
            @bind($model ?? null)
                <x-input col="4" name="production_code" label="Kode WO" placeholder="Otomatis jika kosong" />
                <x-select col="4" name="production_type" label="Tipe Produksi" :options="$typeOptions"
                    helper="Dari Pesanan: pilih SO & kelompokkan kebutuhan. Rutin: gabung beberapa barang jadi 1 paket." />
                <x-select col="4" name="production_status" label="Status" :options="$statusOptions" />

                <div class="col-span-12 md:col-span-6">
                    <label class="font-label-md text-label-md text-on-surface-variant block mb-2">Sumber Pesanan (SO)</label>
                    <select name="production_orders[]" id="wo-orders" multiple size="6" class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-white text-sm">
                        @foreach($soOptions as $soId => $soCode)
                            <option value="{{ $soId }}"
                                {{ in_array((string) $soId, (array) old('production_orders', $model?->production_orders ?? [])) ? 'selected' : '' }}>
                                {{ $soCode }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-on-surface-variant mt-1">Tahan Ctrl/CMD untuk pilih beberapa. Lalu klik "Kelompokkan Kebutuhan".</p>
                    <button type="button" onclick="window.woGroupOrders && window.woGroupOrders()" class="btn btn-soft btn-sm mt-2">
                        <span class="material-symbols-outlined text-base">calculate</span> Kelompokkan Kebutuhan
                    </button>
                    <div id="wo-group-result" class="hidden mt-3 p-3 rounded-lg bg-surface-container-low border border-outline-variant text-sm"></div>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="font-label-md text-label-md text-on-surface-variant block mb-2">Produk Hasil (Paket) <span class="text-error">*</span></label>
                    <select name="production_id_product" class="search w-full h-12 px-3 border border-outline-variant rounded-lg bg-white text-sm">
                        @foreach($productOptions as $pid => $pname)
                            <option value="{{ $pid }}" @selected(old('production_id_product', $model?->production_id_product) == $pid)>{{ $pname }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3 col-span-12">
                    <label class="font-label-md text-label-md text-on-surface-variant block mb-2">Qty Hasil <span class="text-error">*</span></label>
                    <input type="number" min="1" name="production_qty_output" value="{{ old('production_qty_output', $model?->production_qty_output ?? 1) }}"
                        class="w-full h-12 px-3 border border-outline-variant rounded-lg bg-white text-sm" required>
                </div>
                <div class="md:col-span-9 col-span-12">
                    <label class="font-label-md text-label-md text-on-surface-variant block mb-2">Catatan</label>
                    <textarea name="production_note" rows="1" class="w-full px-3 py-2.5 border border-outline-variant rounded-lg bg-white text-sm">{{ old('production_note', $model?->production_note) }}</textarea>
                </div>
            @endbind

            {{-- Bahan baku --}}
            <div class="col-span-12 mt-4 pt-4 border-t border-outline-variant">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-on-surface">Bahan Baku / Barang yang Diproduksi <span class="text-error">*</span></p>
                    <button type="button" onclick="window.addWoItem && window.addWoItem()" class="btn btn-soft btn-sm">
                        <span class="material-symbols-outlined text-base">add</span> Tambah Bahan
                    </button>
                </div>
                <div id="wo-items" class="space-y-2"></div>
                <p class="text-xs text-on-surface-variant mt-2">Stok bahan berkurang & stok paket bertambah otomatis saat status diubah menjadi <strong>Selesai</strong>.</p>
            </div>
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>

@push('scripts')
<script>
    // Work order form: baris bahan dinamis + grouping SO.
    // Tidak tergantung library eksternal — selalu berfungsi.
    (() => {
        const products = @json(collect($productOptions));

        let rowIndex = document.querySelectorAll('#wo-items .wo-item').length;

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
                <button type="button" onclick="this.closest('.wo-item').remove()" class="btn btn-soft h-11 shrink-0 text-error" title="Hapus">
                    <span class="material-symbols-outlined text-lg">delete</span>
                </button>
            </div>`;
        }

        window.addWoItem = function (data = {}) {
            document.getElementById('wo-items').insertAdjacentHTML('beforeend', itemRowHtml(rowIndex++, data));
        };

        // Kelompokkan kebutuhan produk dari SO terpilih
        window.woGroupOrders = async function () {
            const ids = [...document.querySelectorAll('#wo-orders option:checked')].map(o => o.value);
            const box = document.getElementById('wo-group-result');

            if (!ids.length) {
                box.classList.remove('hidden');
                box.innerHTML = '<span class="text-error text-xs">Pilih minimal satu pesanan (SO) dulu.</span>';
                return;
            }

            box.classList.remove('hidden');
            box.innerHTML = '<span class="text-xs text-on-surface-variant">Menghitung…</span>';

            try {
                const res = await fetch('{{ route("production.getGroupOrders") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids })
                });
                const json = await res.json();

                if (!json.status || !json.data.length) {
                    box.innerHTML = '<span class="text-xs text-on-surface-variant">Tidak ada detail produk pada pesanan terpilih.</span>';
                    return;
                }

                let html = '<p class="font-semibold mb-2">Total Kebutuhan per Produk:</p><table class="w-full text-xs"><thead><tr class="text-left text-on-surface-variant"><th>Produk</th><th class="text-right">Total Qty</th></tr></thead><tbody>';
                json.data.forEach(r => {
                    html += `<tr class="border-t border-outline-variant/50"><td>${r.product_nama}</td><td class="text-right font-mono">${r.total_qty}</td></tr>`;

                    // Prefill baris bahan dari hasil grouping
                    window.addWoItem({ product_id: r.product_id, qty: r.total_qty });
                });
                html += '</tbody></table>';
                box.innerHTML = html;
            } catch (e) {
                box.innerHTML = '<span class="text-xs text-error">Gagal menghubungi server.</span>';
            }
        };

        // Repopulasi bahan saat update
        @foreach (($model?->has_items ?? collect()) as $item)
            window.addWoItem({ product_id: {{ $item->production_item_id_product }}, qty: {{ $item->production_item_qty }} });
        @endforeach
    })();
</script>
@endpush
