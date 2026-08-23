<?php /** @var Modules\Production\Models\Production $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Produksi dari SO'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        @if(isset($model) && $model->exists)
            {{-- ===== UPDATE: ubah status/catatan saja, detail readonly ===== --}}
            <x-card label="Work Order: {{ $model->production_code }}">
                <div class="col-span-12 p-4 rounded-lg border border-outline-variant bg-surface-container-low/50 text-sm space-y-1">
                    <p><span class="material-symbols-outlined align-middle text-base text-primary">inventory_2</span>
                        <strong>{{ $model->has_product?->product_nama ?? '-' }}</strong> — total qty
                        <strong class="font-mono">{{ formatQty($model->production_qty_output) }}</strong></p>
                    <p class="text-xs text-on-surface-variant">
                        Sumber: {{ count($model->production_orders ?? []) }} SO
                        ({{ \Modules\So\Models\So::whereIn('id', $model->production_orders ?? [])->orderBy('so_code')->pluck('so_code')->implode(', ') }})
                    </p>
                </div>

                @bind($model ?? null)
                    <x-select col="6" name="production_status" label="Status" :options="$statusOptions" />
                    <div class="md:col-span-6 col-span-12">
                        <label class="font-label-md text-label-md text-on-surface-variant block mb-2">Catatan</label>
                        <textarea name="production_note" rows="1" class="w-full px-3 py-2.5 border border-outline-variant rounded-lg bg-white text-sm">{{ old('production_note', $model?->production_note) }}</textarea>
                    </div>
                    {{-- Dipertahankan agar validasi rules() tetap lolos --}}
                    <input type="hidden" name="production_type" value="order">
                    <input type="hidden" name="production_id_product" value="{{ $model->production_id_product }}">
                    <input type="hidden" name="production_qty_output" value="{{ $model->production_qty_output }}">
                    @foreach($model->production_orders ?? [] as $soId)
                        <input type="hidden" name="production_orders[]" value="{{ $soId }}">
                    @endforeach
                @endbind

                <div class="col-span-12 mt-2 p-3 rounded-lg bg-surface-container-low/60 border border-outline-variant/60">
                    <p class="text-xs text-on-surface-variant">Mengubah status menjadi <strong>Selesai</strong> akan mengurangi stok barang sesuai qty work order.</p>
                </div>
            </x-card>
        @else
            {{-- ===== CREATE: pilih SO → grouping → bulk WO per barang ===== --}}
            <x-card label="Buat Work Order dari Pesanan (SO)">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <x-select col="12" name="production_orders[]" label="Sumber Pesanan (SO)" :options="$soOptions"
                            :multiple="true" class="search" placeholder="-- Pilih beberapa pesanan --" required />
                        <p class="text-xs text-on-surface-variant mt-1 -mt-2">Work order akan dibuat otomatis <strong>per barang</strong> dari hasil pengelompokan.</p>
                    </div>
                    <div id="wo-group-result" class="hidden w-full p-3 rounded-lg bg-surface-container-low border border-outline-variant text-sm"></div>
                </div>
            </x-card>
        @endif

        @if(isset($model) && $model->exists)
            <x-action :model="$model" :action="['save']"/>
        @endif
    </x-form>

    @push('scripts')
    <script>
    (() => {
        // ================= CREATE: preview & buat WO per barang =================
        window.woPreviewOrders = async function () {
            const ids = [...document.querySelectorAll("select[name='production_orders[]'] option:checked")].map(o => o.value);
            const box = document.getElementById('wo-group-result');

            if (!ids.length) {
                box.classList.remove('hidden');
                box.innerHTML = '<span class="text-error text-xs">Pilih minimal satu pesanan (SO) dulu.</span>';
                return;
            }

            box.classList.remove('hidden');
            box.innerHTML = '<span class="text-xs text-on-surface-variant">Menghitung…</span>';

            try {
                const res = await fetch('{{ route("production-order.getGroupOrders") }}', {
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

                let html = '<p class="font-semibold mb-2">Akan dibuat work order per barang:</p><table class="w-full text-xs"><thead><tr class="text-left text-on-surface-variant"><th>Barang</th><th class="text-right">Total Qty</th></tr></thead><tbody>';
                json.data.forEach(r => {
                    html += `<tr class="border-t border-outline-variant/50"><td>${r.product_nama}</td><td class="text-right font-mono">${r.total_qty}</td></tr>`;
                });
                html += '</tbody></table>';
                html += '<button type="button" onclick="window.woCreateOrders && window.woCreateOrders()" class="btn btn-primary btn-sm mt-3">Buat '+json.data.length+' Work Order</button>';
                box.innerHTML = html;
            } catch (e) {
                box.innerHTML = '<span class="text-xs text-error">Gagal menghubungi server.</span>';
            }
        };

        window.woCreateOrders = async function () {
            const ids = [...document.querySelectorAll("select[name='production_orders[]'] option:checked")].map(o => o.value);
            if (!ids.length) return;

            try {
                const res = await fetch('{{ route("production-order.createOrders") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids })
                });

                if (res.url) {
                    window.location.href = res.url;
                }
            } catch (e) {
                alert('Gagal membuat work order.');
            }
        };

        // Auto-preview saat seleksi SO berubah (mode create)
        const soSelect = document.querySelector("select[name='production_orders[]']");
        if (soSelect) {
            soSelect.addEventListener('change', () => window.woPreviewOrders && window.woPreviewOrders());
        }
    })();
</script>
    @endpush
</x-layouts::app>
