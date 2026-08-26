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
            @if($isAffiliator)
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
                        @php
                            $itemHarga = (float) ($item->has_product?->product_harga ?? 0);
                            $itemResellerPct = $isReseller ? (float) ($item->has_product?->reseller_fee_percent ?? 0) : 0;
                            $itemHargaReseller = $itemResellerPct > 0 ? $itemHarga * (1 - $itemResellerPct / 100) : $itemHarga;
                        @endphp
                        <div class="relative flex gap-3 p-3 rounded-2xl border border-outline-variant/60 bg-white shadow-sm" id="cart-row-{{ $item->id }}">

                            {{-- Hapus button (pojok kanan atas) --}}
                            <button type="button" title="Hapus"
                                onclick="if(confirm('Hapus produk ini dari keranjang?')){ document.getElementById('remove-form-{{ $item->id }}').submit(); }"
                                class="absolute top-2 right-2 p-1 rounded-full hover:bg-error-container/30 text-on-surface-variant hover:text-error transition-colors z-10">
                                <span class="material-symbols-outlined text-lg">close</span>
                            </button>

                            {{-- Gambar --}}
                            <a href="{{ route('shop.show', $item->has_product?->product_slug) }}" class="shrink-0">
                                <img src="{{ $item->has_product?->product_gambar_url ?: asset('images/placeholder.png') }}" alt=""
                                    class="w-20 h-20 rounded-xl object-cover border border-outline-variant"
                                    onerror="this.style.display='none'">
                            </a>

                            {{-- Info produk --}}
                            <div class="flex-1 min-w-0 pr-6">
                                <a href="{{ route('shop.show', $item->has_product?->product_slug) }}" class="font-semibold text-sm text-on-surface hover:text-on-surface-variant line-clamp-2 leading-snug block">{{ $item->has_product?->product_nama }}</a>

                                {{-- Harga --}}
                                <div class="mt-1">
                                    @if($isReseller && $itemResellerPct > 0)
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs text-on-surface-variant line-through">{{ formatAngka((int) $itemHarga, 'Rp') }}</span>
                                            <span class="text-[10px] font-bold text-on-error bg-error/10 rounded px-1 py-0.5 leading-none">-{{ $itemResellerPct }}%</span>
                                        </div>
                                        <p class="text-sm font-bold text-primary">{{ formatAngka((int) $itemHargaReseller, 'Rp') }}</p>
                                    @else
                                        <p class="text-sm font-bold text-primary">{{ formatAngka((int) $itemHarga, 'Rp') }}</p>
                                    @endif
                                    <span class="text-[11px] text-on-surface-variant">/ {{ $item->has_product?->has_satuan?->satuan_nama ?? 'pcs' }}</span>
                                </div>

                                {{-- Qty + Subtotal --}}
                                <div class="mt-2.5 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-0 border border-outline-variant rounded-xl overflow-hidden bg-surface-container-low">
                                        <button type="button" onclick="changeQty({{ $item->id }}, -1)"
                                            class="w-9 h-9 flex items-center justify-center text-on-surface-variant hover:bg-surface-container transition-colors">
                                            <span class="material-symbols-outlined text-lg">remove</span>
                                        </button>
                                        <input type="number" name="qty[{{ $item->id }}]" value="{{ $item->qty }}" min="0" max="999"
                                            data-price="{{ $itemHargaReseller }}"
                                            data-row-subtotal="row-subtotal-{{ $item->id }}"
                                            id="qty-{{ $item->id }}"
                                            class="cart-qty w-12 h-9 text-center text-sm font-semibold bg-transparent outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                        <button type="button" onclick="changeQty({{ $item->id }}, 1)"
                                            class="w-9 h-9 flex items-center justify-center text-on-surface-variant hover:bg-surface-container transition-colors">
                                            <span class="material-symbols-outlined text-lg">add</span>
                                        </button>
                                    </div>
                                    <p class="font-mono font-bold text-sm text-on-surface" id="row-subtotal-{{ $item->id }}">{{ formatAngka((int) ($item->qty * $itemHargaReseller), 'Rp') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </form>

            {{-- Total + Checkout --}}
            <div class="mt-6">
                <div class="bg-white border border-outline-variant rounded-2xl shadow-sm p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs text-on-surface-variant">Total</p>
                            <p class="text-lg font-bold font-mono text-primary truncate" id="cart-total">{{ formatAngka((int) $subtotal, 'Rp') }}</p>
                            <p class="text-[11px] text-on-surface-variant hidden md:block">belum termasuk ongkir</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="submit" form="cart-update-form" class="btn btn-soft btn-sm hidden md:inline-flex">
                                <span class="material-symbols-outlined text-base">refresh</span> Update
                            </button>
                            <a href="{{ route('checkout.show') }}" class="btn bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center gap-1.5">
                                Checkout <span class="material-symbols-outlined text-base">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- form hapus per item --}}
            @foreach($items as $item)
                <form method="POST" action="{{ route('cart.remove') }}" id="remove-form-{{ $item->id }}" class="hidden">
                    @csrf
                    <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                </form>
            @endforeach

            {{-- form update (hidden, triggered from bottom bar) --}}
            <form method="POST" action="{{ route('cart.update') }}" id="cart-update-form" class="hidden">
                @csrf
                @foreach($items as $item)
                    <input type="hidden" name="qty[{{ $item->id }}]" value="{{ $item->qty }}">
                @endforeach
            </form>
        @endif
    </div>

    <script>
        (function () {
            const fmtRp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
            const inputs = document.querySelectorAll('.cart-qty');
            if (!inputs.length) return;
            const totalEl = document.getElementById('cart-total');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

            function recalc() {
                let total = 0;
                inputs.forEach(input => {
                    const price = parseFloat(input.dataset.price) || 0;
                    const qty = Math.max(0, parseInt(input.value || '0', 10) || 0);
                    const row = document.getElementById(input.dataset.rowSubtotal);
                    if (row) row.textContent = fmtRp(price * qty);
                    total += price * qty;

                    // Sync hidden form
                    const hiddenInput = document.querySelector('#cart-update-form input[name="qty[' + input.name.match(/\d+/) + ']"]');
                    if (hiddenInput) hiddenInput.value = qty;
                });
                if (totalEl) totalEl.textContent = fmtRp(total);
            }

            function bumpBadge(count) {
                document.querySelectorAll('[data-cart-count]').forEach(el => {
                    const next = Math.max(0, (parseInt(count, 10) || 0));
                    el.textContent = next;
                    el.classList.toggle('hidden', next <= 0);
                });
            }

            // Auto-save qty ke server (debounce). Jika 0 -> item otomatis terhapus.
            let saveTimer;
            function autoSave(input) {
                const id = input.name.match(/\d+/);
                if (!id) return;
                const qty = Math.max(0, Math.min(999, parseInt(input.value || '0', 10) || 0));
                clearTimeout(saveTimer);
                saveTimer = setTimeout(async () => {
                    const body = new FormData();
                    body.append('_token', csrf);
                    body.append('qty[' + id + ']', qty);
                    try {
                        const res = await fetch('{{ route('cart.update') }}', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            body,
                        });
                        const json = await res.json();
                        if (!json.status) throw new Error(json.message ?? 'Gagal');
                        if (typeof json.cart_count !== 'undefined') bumpBadge(json.cart_count);
                        // Jika qty 0, server menghapus item -> buang baris dari DOM
                        if (qty <= 0) {
                            const row = document.getElementById('cart-row-' + id);
                            if (row) row.remove();
                            const remaining = document.querySelectorAll('.cart-qty');
                            if (remaining.length === 0) location.reload();
                        }
                    } catch (e) {
                        // Biarkan user menekan Update manual jika gagal
                    }
                }, 600);
            }

            inputs.forEach(input => input.addEventListener('input', () => { recalc(); autoSave(input); }));

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

        // Qty +/- buttons
        function changeQty(id, delta) {
            const input = document.getElementById('qty-' + id);
            if (!input) return;
            const newVal = Math.max(0, Math.min(999, parseInt(input.value || '0', 10) + delta));
            input.value = newVal;
            input.dispatchEvent(new Event('input'));
        }
    </script>
</x-ecommerce::public-layout>
