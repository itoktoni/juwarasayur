@include('ecommerce::components.brand')
<?php /** @var \Illuminate\Support\Collection $items */ ?>
<x-layouts::app :title="'Keranjang'">
    <div class="content mt-4 lg:mt-0">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-on-surface">Keranjang Belanja</h2>
        </div>

        @if($items->isEmpty())
            <div class="p-8 rounded-xl border border-outline-variant bg-surface-container-lowest text-center">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant/40">shopping_cart</span>
                <p class="mt-3 text-on-surface-variant">Keranjang masih kosong.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary mt-4 inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">storefront</span> Belanja Sekarang
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('cart.update') }}">
                @csrf
                <div class="space-y-3">
                    @foreach($items as $item)
                        <div class="flex flex-col md:flex-row md:items-center gap-3 p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                            <img src="{{ $item->has_product?->product_gambar_url ?: asset('images/placeholder.png') }}" alt=""
                                class="w-16 h-16 rounded-lg object-cover border border-outline-variant shrink-0"
                                onerror="this.style.display='none'">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('shop.show', $item->has_product?->product_slug) }}" class="font-semibold text-on-surface hover:text-primary truncate block">{{ $item->has_product?->product_nama }}</a>
                                <p class="text-xs font-mono text-on-surface-variant mt-0.5">{{ formatAngka((int) ($item->has_product?->product_harga ?? 0), 'Rp') }} / {{ $item->has_product?->has_satuan?->satuan_nama ?? 'pcs' }}</p>
                            </div>
                            <div class="w-24">
                                <label class="text-[10px] uppercase tracking-wide text-on-surface-variant block mb-1">Qty</label>
                                <input type="number" name="qty[{{ $item->id }}]" value="{{ $item->qty }}" min="0" max="999"
                                    class="w-full h-10 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            </div>
                            <div class="text-right w-32">
                                <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Subtotal</p>
                                <p class="font-mono font-bold text-on-surface">{{ formatAngka((int) ($item->qty * (float) ($item->has_product?->product_harga ?? 0)), 'Rp') }}</p>
                            </div>
                            <button type="button" title="Hapus"
                                onclick="if(confirm('Hapus produk ini dari keranjang?')){ document.getElementById('remove-form-{{ $item->id }}').submit(); }"
                                class="btn btn-soft h-10 w-10 !px-0 text-error shrink-0">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </div>

                        {{-- form terpisah untuk hapus per item --}}
                        <form method="POST" action="{{ route('cart.remove') }}" id="remove-form-{{ $item->id }}" class="hidden">
                            @csrf
                            <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                        </form>
                    @endforeach
                </div>

                <div class="mt-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                    <button type="submit" class="btn btn-soft">
                        <span class="material-symbols-outlined text-base">refresh</span> Update Keranjang
                    </button>
                    <div class="text-right">
                        <p class="text-xs text-on-surface-variant">Total sementara (belum termasuk ongkir)</p>
                        <p class="text-xl font-bold font-mono text-primary">{{ formatAngka((int) $subtotal, 'Rp') }}</p>
                    </div>
                    <a href="{{ route('checkout.show') }}" class="btn btn-primary">
                        Lanjut ke Checkout <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </a>
                </div>
            </form>
        @endif
    </div>
</x-layouts::app>
