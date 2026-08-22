@include('ecommerce::components.brand')
<?php /** @var \Illuminate\Support\Collection $items */ ?>
<x-layouts::app :title="'Checkout'">
    <div class="content mt-4 lg:mt-0">
        <div class="mb-6 flex items-center gap-2">
            <h2 class="text-2xl font-bold text-on-surface">Checkout</h2>
            <span class="text-sm text-on-surface-variant">— pesanan akan masuk ke Sales Order</span>
        </div>

        <form method="POST" action="{{ route('checkout.placeOrder') }}" class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @csrf

            {{-- Data pemesan --}}
            <div class="md:col-span-2 p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                <h3 class="font-bold text-on-surface mb-1">Data Pemesan</h3>
                <p class="text-xs text-on-surface-variant mb-4">Pesanan diambil di gudang — cukup isi nama dan nomor HP.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">Nama <span class="text-error">*</span></label>
                        <input type="text" name="customer_name" value="{{ old('customer_name', $customer->name) }}" required
                            placeholder="Nama lengkap"
                            class="w-full h-12 px-4 bg-white border {{ $errors->has('customer_name') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        @error('customer_name')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface mb-1">No. HP / WhatsApp <span class="text-error">*</span></label>
                        <input type="tel" name="customer_phone" value="{{ old('customer_phone', $customer->phone) }}" required
                            placeholder="cth: 081234567890"
                            class="w-full h-12 px-4 bg-white border {{ $errors->has('customer_phone') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        @error('customer_phone')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                    </div>
                </div>
                @error('cart')<p class="text-error text-xs mt-3">{{ $message }}</p>@enderror
            </div>

            {{-- Ringkasan --}}
            <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest h-fit">
                <h3 class="font-bold text-on-surface mb-3">Ringkasan ({{ $items->count() }} produk)</h3>
                <div class="divide-y divide-outline-variant/60 text-sm">
                    @foreach($items as $item)
                        <div class="flex items-center justify-between py-2 gap-2">
                            <span class="truncate text-on-surface">{{ $item->has_product?->product_nama }} <span class="text-on-surface-variant">× {{ $item->qty }}</span></span>
                            <span class="font-mono shrink-0">{{ formatAngka((int) ($item->qty * (float) ($item->has_product?->product_harga ?? 0)), 'Rp') }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between pt-3 mt-1 border-t border-outline-variant text-xs text-on-surface-variant">
                    <span>Pengambilan di gudang</span><span>Ongkir Rp 0</span>
                </div>
                <div class="flex justify-between pt-2 font-bold">
                    <span>Total Bayar</span>
                    <span id="co-summary-grand" class="font-mono text-primary text-base">{{ formatAngka((int) $subtotal, 'Rp') }}</span>
                </div>
                <button type="submit" class="btn btn-primary w-full h-12 mt-4 text-base">
                    <span class="material-symbols-outlined text-base">shopping_cart_checkout</span> Buat Pesanan
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
