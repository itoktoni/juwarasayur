<?php /** @var Modules\So\Models\Consignment $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Titip Jual'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Titip Barang ke Reseller">
            @bind($model ?? null)
                <x-select col="4" name="user_id" label="Reseller" :options="$resellerOptions" class="search" />
                <x-input col="4" name="consignment_date" type="date" label="Tanggal Titip" />
                <x-input col="4" name="note" label="Keterangan" placeholder="cth: toko bu ani, sayur keliling" />
            @endbind
        </x-card>

        <x-card label="Produk yang Dititipkan" class="mt-5">
            <div class="col-span-12">
                <div id="tj-details" class="space-y-3">
                    @php
                        $rows = isset($model) && $model->exists ? $model->has_details : collect([['product_id' => '', 'qty' => 1, 'price' => '']]);
                    @endphp
                    @foreach($rows as $idx => $row)
                        @php
                            $rowId = is_object($row) ? $row->id : ($row['id'] ?? '');
                            $rowProduct = is_object($row) ? $row->product_id : ($row['product_id'] ?? '');
                            $rowQty = is_object($row) ? $row->qty : ($row['qty'] ?? 1);
                            $rowPrice = trim(rtrim(rtrim((string) (is_object($row) ? $row->price : ($row['price'] ?? '')), '0'), '.'), '.');
                        @endphp
                        <div class="grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50">
                            <input type="hidden" name="details[{{ $idx }}][id]" value="{{ $rowId }}">
                            <div class="col-span-12 md:col-span-6">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Produk *</label>
                                <select name="details[{{ $idx }}][product_id]" class="tj-product w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none search" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($productOptions as $pid => $pname)
                                        <option value="{{ $pid }}" @selected((string)$rowProduct === (string)$pid)>{{ $pname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-6 md:col-span-2">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Jumlah *</label>
                                <input type="number" name="details[{{ $idx }}][qty]" value="{{ $rowQty }}" min="1" step="any"
                                    class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none" required>
                            </div>
                            <div class="col-span-6 md:col-span-3">
                                <label class="text-xs font-bold text-on-surface-variant block mb-1">Harga Jual</label>
                                <input type="number" name="details[{{ $idx }}][price]" value="{{ $rowPrice }}" min="0" step="1" placeholder="Otomatis"
                                    class="tj-price w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none">
                            </div>
                            <div class="col-span-12 md:col-span-1 flex justify-end">
                                <button type="button" onclick="this.closest('.grid').remove()" class="btn btn-soft w-full h-12 text-error" title="Hapus baris">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="addTjRow()" class="btn btn-soft btn-sm mt-3">
                    <span class="material-symbols-outlined text-base">add</span> Tambah Produk
                </button>
                @error('details')<p class="text-error text-xs mt-2">{{ $message }}</p>@enderror
            </div>
        </x-card>

        <x-action :model="$model" :action="isset($model) && $model->exists ? ['save'] : ['save', 'cancel']"/>
    </x-form>

    <template id="tj-row-template">
        <div class="grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50">
            <input type="hidden" name="details[__IDX__][id]" value="">
            <div class="col-span-12 md:col-span-6">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Produk *</label>
                <select name="details[__IDX__][product_id]" class="tj-product w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($productOptions as $pid => $pname)
                        <option value="{{ $pid }}">{{ $pname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-6 md:col-span-2">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Jumlah *</label>
                <input type="number" name="details[__IDX__][qty]" value="1" min="1" step="any" class="w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none">
            </div>
            <div class="col-span-6 md:col-span-3">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Harga Jual</label>
                <input type="number" name="details[__IDX__][price]" min="0" step="1" placeholder="Otomatis" class="tj-price w-full h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none">
            </div>
            <div class="col-span-12 md:col-span-1 flex justify-end">
                <button type="button" onclick="this.closest('.grid').remove()" class="btn btn-soft w-full h-12 text-error" title="Hapus baris">
                    <span class="material-symbols-outlined text-lg">delete</span>
                </button>
            </div>
        </div>
    </template>

    <script>
        const TJ_PRICES = @json($productPrices);
        function addTjRow() {
            const wrap = document.getElementById('tj-details');
            const idx = wrap.children.length;
            const html = document.getElementById('tj-row-template').innerHTML.replaceAll('__IDX__', idx);
            wrap.insertAdjacentHTML('beforeend', html);
        }
        // Auto-isi harga dari produk terpilih
        document.addEventListener('change', e => {
            if (!e.target.classList.contains('tj-product')) return;
            const row = e.target.closest('.grid');
            const priceInput = row?.querySelector('.tj-price');
            if (priceInput && (priceInput.value === '' || priceInput.value === '0') && e.target.value && TJ_PRICES[e.target.value] != null) {
                priceInput.value = parseFloat(TJ_PRICES[e.target.value]);
            }
        });
    </script>
</x-layouts::app>
