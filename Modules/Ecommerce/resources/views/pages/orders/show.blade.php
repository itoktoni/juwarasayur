<?php /** @var \Modules\So\Models\So $model */ ?>
<x-ecommerce::account-layout :title="'Detail Pesanan'">
    <div class="space-y-5">
        <div class="mb-6">
            <a href="{{ route('ecommerce.orders.index') }}" class="text-sm text-primary hover:underline inline-flex items-center gap-1 mb-2">
                <span class="material-symbols-outlined text-base">arrow_back</span> Pesanan Saya
            </a>
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-2xl font-bold text-on-surface font-mono">{{ $model->so_code }}</h2>
                <span class="badge badge-soft text-xs px-3 py-1 rounded-full bg-surface-container-high text-on-surface-variant">{{ $statusLabel }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Produk --}}
            <div class="md:col-span-2 p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                <h3 class="font-bold text-on-surface mb-3">Produk</h3>
                <div class="divide-y divide-outline-variant/60">
                    @foreach($model->has_details as $detail)
                        <div class="flex items-center justify-between py-2 gap-3">
                            <div class="min-w-0">
                                <p class="text-sm text-on-surface truncate">{{ $detail->has_product?->product_nama ?? '-' }}</p>
                                <p class="text-xs text-on-surface-variant font-mono">{{ formatAngka((int) $detail->so_detail_qty, '') }} × {{ formatAngka((int) $detail->so_detail_harga, 'Rp') }}</p>
                            </div>
                            <span class="text-sm font-mono text-on-surface shrink-0">{{ formatAngka((int) ($detail->so_detail_qty * (float) $detail->so_detail_harga), 'Rp') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-1.5 pt-3 mt-3 border-t border-outline-variant text-sm">
                    <div class="flex justify-between"><span class="text-on-surface-variant">Subtotal</span><span class="font-mono">{{ formatAngka((int) $model->so_subtotal, 'Rp') }}</span></div>
                    @if((float) $model->so_discount > 0)
                        <div class="flex justify-between"><span class="text-on-surface-variant">Diskon</span><span class="font-mono">- {{ formatAngka((int) $model->so_discount, 'Rp') }}{{ $model->so_discount_type === 'percent' ? ' ('.rtrim(rtrim((string) (float) $model->so_discount, '0'), '.').'%)' : '' }}</span></div>
                    @endif
                    @if((float) $model->so_ppn > 0)
                        <div class="flex justify-between"><span class="text-on-surface-variant">PPN</span><span class="font-mono">{{ formatAngka((int) $model->so_ppn, 'Rp') }}</span></div>
                    @endif
                    @if((float) $model->so_pph > 0)
                        <div class="flex justify-between"><span class="text-on-surface-variant">PPH</span><span class="font-mono">{{ formatAngka((int) $model->so_pph, 'Rp') }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-on-surface-variant">Ongkir</span><span class="font-mono">{{ formatAngka((int) $model->so_shipping_fee, 'Rp') }}</span></div>
                    <div class="flex justify-between border-t border-outline-variant pt-2 mt-1 font-bold">
                        <span>Total Bayar</span><span class="font-mono text-primary">{{ formatAngka((int) $model->so_grand_total, 'Rp') }}</span>
                    </div>
                </div>
            </div>

            {{-- Info pengiriman --}}
            <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest h-fit">
                <h3 class="font-bold text-on-surface mb-3">Pengiriman</h3>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-xs text-on-surface-variant uppercase tracking-wide">Metode</dt>
                        <dd class="text-on-surface">{{ $methodLabel }}{{ $model->so_cod_location ? ' — '.$model->so_cod_location : '' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-on-surface-variant uppercase tracking-wide">Tanggal</dt>
                        <dd class="text-on-surface">{{ formatDate($model->so_tanggal) }}</dd>
                    </div>
                    @if($model->so_address)
                        <div>
                            <dt class="text-xs text-on-surface-variant uppercase tracking-wide">Alamat</dt>
                            <dd class="text-on-surface">{{ $model->so_address }}</dd>
                        </div>
                    @endif
                    @if($model->so_distance_km)
                        <div>
                            <dt class="text-xs text-on-surface-variant uppercase tracking-wide">Jarak</dt>
                            <dd class="text-on-surface font-mono">{{ rtrim(rtrim((string) (float) $model->so_distance_km, '0'), '.') }} km</dd>
                        </div>
                    @endif
                    @if(!empty($model->has_reseller?->name))
                        <div>
                            <dt class="text-xs text-on-surface-variant uppercase tracking-wide">Reseller</dt>
                            <dd class="text-on-surface">{{ $model->has_reseller->name }}</dd>
                        </div>
                    @endif
                    @if($model->so_keterangan)
                        <div>
                            <dt class="text-xs text-on-surface-variant uppercase tracking-wide">Catatan</dt>
                            <dd class="text-on-surface">{{ $model->so_keterangan }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-ecommerce::account-layout>
