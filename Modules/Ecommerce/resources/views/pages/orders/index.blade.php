<x-ecommerce::account-layout :title="'Pesanan'">
    <div class="space-y-5">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-on-surface">{{ $isReseller ? 'Pesanan Customer' : 'Pesanan Saya' }}</h2>
            <a href="{{ route('shop.index') }}" class="btn btn-soft btn-sm">
                <span class="material-symbols-outlined text-base">add_shopping_cart</span> Belanja Lagi
            </a>
        </div>

        @if($data->isEmpty())
            <div class="p-8 rounded-xl border border-outline-variant bg-surface-container-lowest text-center">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant/40">receipt_long</span>
                <p class="mt-3 text-on-surface-variant">Belum ada pesanan.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($data as $so)
                    <a href="{{ route('ecommerce.orders.show', ['id' => $so->id]) }}"
                        class="block p-4 rounded-xl border border-outline-variant bg-surface-container-lowest hover:border-primary transition-colors">
                        <div class="flex items-center justify-between mb-2 gap-3">
                            <span class="font-mono font-bold text-on-surface">{{ $so->so_code }}</span>
                            <span class="badge badge-soft text-xs px-2 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant">{{ \Modules\So\Enums\SoStatusEnum::getDescription($so->so_status) }}</span>
                        </div>
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 text-xs text-on-surface-variant">
                            <span>{{ formatDate($so->so_tanggal) }} — {{ $so->has_details->count() }} produk — {{ \Modules\So\Enums\ShippingMethodEnum::getDescription($so->so_shipping_method) }}</span>
                            <span class="font-mono font-bold text-primary text-sm">{{ formatAngka((int) $so->so_grand_total, 'Rp') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <x-pagination :paginator="$data" />
        @endif
    </div>
</x-ecommerce::account-layout>
