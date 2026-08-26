<?php /** @var Modules\So\Models\Consignment $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Titip Jual'], ['url' => '', 'label' => 'Tarik Uang — '.$model->code]]" />

    <x-form :action="route('so-consignment.postSettle', ['id' => $model->id])">
        <x-card label="Ringkasan Titipan">
            <div class="col-span-12 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div class="p-3 rounded-xl bg-surface-container">
                    <p class="text-xs text-on-surface-variant">Kode</p>
                    <p class="font-bold font-mono">{{ $model->code }}</p>
                </div>
                <div class="p-3 rounded-xl bg-surface-container">
                    <p class="text-xs text-on-surface-variant">Reseller</p>
                    <p class="font-bold truncate">{{ $model->has_reseller?->name ?? '-' }}</p>
                </div>
                <div class="p-3 rounded-xl bg-surface-container">
                    <p class="text-xs text-on-surface-variant">Tanggal Titip</p>
                    <p class="font-bold">{{ $model->consignment_date->format('d/m/Y') }}</p>
                </div>
                <div class="p-3 rounded-xl bg-surface-container">
                    <p class="text-xs text-on-surface-variant">Total Dititipkan</p>
                    <p class="font-bold">{{ number_format((float) $model->total_qty, 0, ',', '.') }}</p>
                </div>
            </div>
        </x-card>

        <x-card label="Hitung Penjualan (malam hari)" class="mt-5">
            <div class="col-span-12 space-y-3">
                @foreach($model->has_details as $i => $d)
                    <div class="grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50">
                        <input type="hidden" name="rows[{{ $d->id }}][detail_id]" value="{{ $d->id }}">
                        <div class="col-span-12 md:col-span-4">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Produk</label>
                            <p class="h-9 flex items-center font-medium text-sm truncate">{{ $loop->iteration }}. {{ $d->has_product?->product_nama ?? '-' }}</p>
                        </div>
                        <div class="col-span-4 md:col-span-2">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Titip</label>
                            <p class="h-9 flex items-center font-mono text-sm">{{ number_format((float) $d->qty, 0, ',', '.') }}</p>
                            <input type="hidden" name="rows[{{ $d->id }}][max]" value="{{ $d->qty }}">
                        </div>
                        <div class="col-span-4 md:col-span-2">
                            <label class="text-xs font-bold text-primary block mb-1">Terjual *</label>
                            <input type="number" name="rows[{{ $d->id }}][qty_sold]" value="{{ old('rows.'.$d->id.'.qty_sold', $d->qty_sold ?? 0) }}" min="0" max="{{ $d->qty }}" step="any"
                                class="tj-sold w-full h-10 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div class="col-span-4 md:col-span-2">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Sisa / Kembali</label>
                            <input type="number" name="rows[{{ $d->id }}][qty_returned]" value="{{ old('rows.'.$d->id.'.qty_returned', $d->qty_returned ?? 0) }}" min="0" max="{{ $d->qty }}" step="any" class="w-full h-10 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none">
                        </div>
                        <div class="col-span-12 md:col-span-2">
                            <label class="text-xs font-bold text-on-surface-variant block mb-1">Subtotal</label>
                            <p class="h-9 flex items-center justify-end font-mono font-bold text-sm tj-subtotal" data-price="{{ $d->price }}">Rp 0</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="col-span-12 flex items-center justify-between mt-5 pt-4 border-t border-outline-variant">
                <span class="font-bold text-on-surface">Total Uang Ditarik</span>
                <span id="tj-total" class="font-extrabold text-2xl font-mono text-primary">Rp 0</span>
            </div>
        </x-card>

        <x-action :action="['save', 'cancel']"/>
    </x-form>

    <script>
        function recalcTj() {
            let total = 0;
            document.querySelectorAll('.tj-sold').forEach(function(input) {
                const row = input.closest('.grid');
                const price = parseFloat(row.querySelector('.tj-subtotal').dataset.price || 0);
                const sold = parseInt(input.value || 0, 10) || 0;
                const sub = sold * price;
                total += sub;
                row.querySelector('.tj-subtotal').textContent = 'Rp ' + sub.toLocaleString('id-ID');
            });
            document.getElementById('tj-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        document.addEventListener('input', e => { if (e.target.classList.contains('tj-sold')) recalcTj(); });
        document.addEventListener('DOMContentLoaded', recalcTj);
    </script>
</x-layouts::app>
