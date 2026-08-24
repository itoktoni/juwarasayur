<?php /** @var \Illuminate\Support\Collection $items */ ?>
<x-ecommerce::public-layout :title="'Keranjang'">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-on-surface">Keranjang Belanja</h2>
        </div>

        @if($items->isEmpty())
            <div class="p-8 rounded-xl border border-outline-variant bg-surface-container-lowest text-center">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant/40">shopping_cart</span>
                <p class="mt-3 text-on-surface-variant">Keranjang masih kosong.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-primary mt-4 py-2 px-3 inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">storefront</span> Belanja Sekarang
                </a>
            </div>
        @else
            @if($isReseller)
                <div class="mb-5 p-4 rounded-xl border border-primary/40 bg-primary/5">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-semibold text-on-surface">Pesan untuk Customer</p>
                        <span id="customer-status" class="hidden text-xs font-medium"></span>
                    </div>
                    <p class="text-xs text-on-surface-variant mb-3">Pilih customer tujuan pesanan ini. Kosongkan jika belanja untuk diri sendiri.</p>
                    <form method="POST" action="{{ route('cart.setCustomer') }}" id="set-customer-form">
                        @csrf
                        <x-select name="customer_id" label="Pilih Customer" col="12" class="search"
                            placeholder="-- Belanja untuk Diri Sendiri --"
                            :options="$customers->pluck('name', 'id')"
                            :default="$selectedCustomerId ?: null" />
                    </form>
                </div>
            @endif

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
                                    data-price="{{ (float) ($item->has_product?->product_harga ?? 0) }}"
                                    data-row-subtotal="row-subtotal-{{ $item->id }}"
                                    class="cart-qty w-full h-10 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            </div>
                            <div class="text-right w-32">
                                <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Subtotal</p>
                                <p class="font-mono font-bold text-on-surface" id="row-subtotal-{{ $item->id }}">{{ formatAngka((int) ($item->qty * (float) ($item->has_product?->product_harga ?? 0)), 'Rp') }}</p>
                            </div>
                            <button type="button" title="Hapus"
                                onclick="if(confirm('Hapus produk ini dari keranjang?')){ document.getElementById('remove-form-{{ $item->id }}').submit(); }"
                                class="btn btn-soft h-10 w-10 !px-0 text-error shrink-0">
                                <span class="material-symbols-outlined text-base">delete</span>
                            </button>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                    <button type="submit" class="btn btn-soft px-4 py-2">
                        <span class="material-symbols-outlined text-base">refresh</span> Update Keranjang
                    </button>
                    <div class="text-right">
                        <p class="text-xs text-on-surface-variant">Total sementara (belum termasuk ongkir)</p>
                        <p class="text-xl font-bold font-mono text-primary" id="cart-total">{{ formatAngka((int) $subtotal, 'Rp') }}</p>
                    </div>
                    <a href="{{ route('checkout.show') }}" class="btn btn-primary px-4 py-2">
                        Lanjut ke Checkout <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </a>
                </div>
            </form>

            {{-- form hapus per item — di luar form update agar HTML valid (form bersarang dibuang browser) --}}
            @foreach($items as $item)
                <form method="POST" action="{{ route('cart.remove') }}" id="remove-form-{{ $item->id }}" class="hidden">
                    @csrf
                    <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                </form>
            @endforeach
        @endif
    </div>

    <script>
        (function () {
            const fmtRp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
            const inputs = document.querySelectorAll('.cart-qty');
            if (!inputs.length) return;
            const totalEl = document.getElementById('cart-total');

            function recalc() {
                let total = 0;
                inputs.forEach(input => {
                    const price = parseFloat(input.dataset.price) || 0;
                    const qty = Math.max(0, parseInt(input.value || '0', 10) || 0);
                    const row = document.getElementById(input.dataset.rowSubtotal);
                    if (row) row.textContent = fmtRp(price * qty);
                    total += price * qty;
                });
                if (totalEl) totalEl.textContent = fmtRp(total);
            }

            inputs.forEach(input => input.addEventListener('input', recalc));

            // AJAX: simpan pilihan customer tanpa refresh
            const customerForm = document.getElementById('set-customer-form');
            if (customerForm) {
                const statusEl = document.getElementById('customer-status');
                const select = customerForm.querySelector('select[name="customer_id"]');

                const showStatus = (text, ok = true) => {
                    if (!statusEl) return;
                    statusEl.textContent = text;
                    statusEl.classList.remove('hidden', 'text-success', 'text-error');
                    statusEl.classList.add(ok ? 'text-success' : 'text-error');
                    clearTimeout(showStatus._t);
                    showStatus._t = setTimeout(() => statusEl.classList.add('hidden'), 3000);
                };

                select?.addEventListener('change', async () => {
                    const body = new FormData(customerForm);
                    try {
                        statusEl?.classList.remove('hidden');
                        if (statusEl) statusEl.textContent = 'Menyimpan…';
                        const res = await fetch(customerForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': customerForm.querySelector('_token')?.value || '',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body,
                        });
                        const json = await res.json();
                        if (!json.status) throw new Error(json.message ?? 'Gagal');
                        showStatus('✓ ' + json.message);
                    } catch (e) {
                        showStatus('Gagal menyimpan pilihan.', false);
                    }
                });
            }
        })();
    </script>
</x-ecommerce::public-layout>
