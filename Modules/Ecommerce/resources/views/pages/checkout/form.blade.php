<x-ecommerce::public-layout :title="'Checkout'">
    <div class="content mt-4 lg:mt-0">
        <div class="mb-6 flex items-center gap-2">
            <h2 class="text-2xl font-bold text-on-surface">Checkout</h2>
        </div>

        <form method="POST" action="{{ route('checkout.placeOrder') }}" id="checkout-form" class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @csrf

            <div class="md:col-span-2 space-y-5">
                {{-- Data pemesan --}}
                <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                    <h3 class="font-bold text-on-surface mb-1">Data Pemesan</h3>
                    <p class="text-xs text-on-surface-variant mb-4">Isi nama dan nomor HP untuk konfirmasi pesanan.</p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-on-surface mb-1">Nama <span class="text-error">*</span></label>
                            <input type="text" name="customer_name" value="{{ old('customer_name', $customer?->name) }}" required
                                placeholder="Nama lengkap"
                                class="w-full h-12 px-4 bg-white border {{ $errors->has('customer_name') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            @error('customer_name')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-on-surface mb-1">No. HP / WhatsApp <span class="text-error">*</span></label>
                            <input type="tel" name="customer_phone" value="{{ old('customer_phone', $customer?->phone) }}" required
                                placeholder="cth: 081234567890"
                                class="w-full h-12 px-4 bg-white border {{ $errors->has('customer_phone') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            @error('customer_phone')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    @error('cart')<p class="text-error text-xs mt-3">{{ $message }}</p>@enderror
                </div>

                {{-- Pengiriman --}}
                <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
                    <h3 class="font-bold text-on-surface mb-1">Pengiriman</h3>
                    <p class="text-xs text-on-surface-variant mb-4">Pilih cara penerimaan pesanan.</p>

                    {{-- Accordion: 1. Pickup, 2. COD, 3. Diantar ke Rumah --}}
                    @php $shippingCfg = config('frontend.shipping', ['pickup'=>true,'cod'=>true,'delivery'=>true]); @endphp
                    <div class="space-y-2" id="shipping-accordion">

                        {{-- 1. Ambil di Gudang --}}
                        @if($shippingCfg['pickup'])
                        <div class="ship-opt rounded-lg border overflow-hidden {{ old('shipping_method', 'pickup') === 'pickup' ? 'border-primary bg-primary/5' : 'border-outline-variant' }}" data-method="pickup">
                            <label class="flex items-start gap-3 p-3 cursor-pointer hover:bg-surface-container ship-head">
                                <input type="radio" name="shipping_method" value="pickup" {{ old('shipping_method', 'pickup') === 'pickup' ? 'checked' : '' }}
                                    class="mt-1 accent-primary shipping-toggle">
                                <span>
                                    <span class="block font-semibold text-on-surface text-sm"><span class="material-symbols-outlined align-middle text-base">store</span> 1. Ambil di Gudang (Pickup)</span>
                                    <span class="block text-xs text-on-surface-variant mt-0.5">Gratis — pesanan diambil sendiri.</span>
                                </span>
                            </label>
                            <div class="ship-pane px-3 pb-3">
                                <div class="p-3 rounded-lg border border-outline-variant bg-surface-container-low/50 text-sm space-y-1">
                                    <p class="text-on-surface"><span class="material-symbols-outlined align-middle text-base text-primary">warehouse</span> <strong>{{ $warehouse['name'] ?? 'Gudang Utama' }}</strong></p>
                                    @if(!empty($warehouse['address']))
                                        <p class="text-on-surface-variant"><span class="material-symbols-outlined align-middle text-base text-primary">location_on</span> {{ $warehouse['address'] }}</p>
                                    @endif
                                    <p class="text-xs text-on-surface-variant">Buka setiap hari 08.00 – 17.00 WIB. Tunjukkan kode pesanan saat mengambil.</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- 2. COD --}}
                        @if($shippingCfg['cod'])
                        <div class="ship-opt rounded-lg border overflow-hidden {{ old('shipping_method') === 'cod' ? 'border-primary bg-primary/5' : 'border-outline-variant' }}" data-method="cod">
                            <label class="flex items-start gap-3 p-3 cursor-pointer hover:bg-surface-container ship-head">
                                <input type="radio" name="shipping_method" value="cod" {{ old('shipping_method') === 'cod' ? 'checked' : '' }}
                                    class="mt-1 accent-primary shipping-toggle">
                                <span>
                                    <span class="block font-semibold text-on-surface text-sm"><span class="material-symbols-outlined align-middle text-base">local_shipping</span> 2. COD — Titik Kumpu Terdekat</span>
                                    <span class="block text-xs text-on-surface-variant mt-0.5">Pilih lokasi COD dengan harga ongkirnya.</span>
                                </span>
                            </label>
                            <div class="ship-pane px-3 pb-3">
                                <p class="text-sm font-semibold text-on-surface mb-2">Pilih Lokasi COD <span class="text-error">*</span></p>
                                @if($codLocations->isEmpty())
                                    <p class="text-xs text-error p-3 rounded-lg bg-error/5 border border-error/30">Belum ada lokasi COD tersedia. Silakan pilih metode lain.</p>
                                @else
                                <div class="space-y-2">
                                    @foreach($codLocations as $loc)
                                    <label class="flex items-center justify-between gap-3 p-3 rounded-lg border border-outline-variant cursor-pointer hover:bg-surface-container has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <span class="flex items-center gap-3">
                                            <input type="radio" name="so_cod_location_pick" value="{{ $loc->location_name }}"
                                                {{ old('so_cod_location') === $loc->location_name ? 'checked' : '' }}
                                                class="accent-primary cod-pick">
                                            <span>
                                                <span class="block font-semibold text-on-surface text-sm">{{ $loc->location_name }}</span>
                                                @if($loc->address)<span class="block text-xs text-on-surface-variant mt-0.5">{{ $loc->address }}</span>@endif
                                            </span>
                                        </span>
                                        <span class="font-mono text-sm shrink-0 text-on-surface">
                                            {{ $loc->fee !== null ? formatAngka((float) $loc->fee, 'Rp') : 'Hitung dari jarak' }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                                @endif

                                <div id="cod-result" class="hidden mt-3 p-3 rounded-lg bg-primary/5 border border-primary/30 text-sm">
                                    <p><span class="font-semibold">Lokasi COD:</span> <span id="cod-name"></span></p>
                                    <p class="mt-1">Ongkir: <strong id="cod-fee" class="font-mono"></strong></p>
                                </div>

                                @error('shipping')<span class="text-xs text-error block mt-2">{{ $message }}</span>@enderror
                                @error('so_cod_location')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        @endif

                        {{-- 3. Diantar ke Rumah --}}
                        @if($shippingCfg['delivery'])
                        <div class="ship-opt rounded-lg border overflow-hidden {{ old('shipping_method') === 'delivery' ? 'border-primary bg-primary/5' : 'border-outline-variant' }}" data-method="delivery">
                            <label class="flex items-start gap-3 p-3 cursor-pointer hover:bg-surface-container ship-head">
                                <input type="radio" name="shipping_method" value="delivery" {{ old('shipping_method') === 'delivery' ? 'checked' : '' }}
                                    class="mt-1 accent-primary shipping-toggle">
                                <span>
                                    <span class="block font-semibold text-on-surface text-sm"><span class="material-symbols-outlined align-middle text-base">home</span> 3. Diantar ke Rumah</span>
                                    @php $delCfg = config('frontend.delivery', ['free_km'=>10,'price_per_km'=>2500,'min_fee'=>10000]); @endphp
                                    <span class="block text-xs text-on-surface-variant mt-0.5">Gratis ongkir hingga {{ (int) $delCfg['free_km'] }} km. Di atas itu, {{ formatAngka((int) $delCfg['price_per_km'], 'Rp') }}/km.</span>
                                </span>
                            </label>
                            <div class="ship-pane px-3 pb-3">
                                <p class="text-sm font-semibold text-on-surface mb-2">Titik Lokasi Rumah <span class="text-error">*</span></p>
                                <button type="button" id="btn-deliv-gps"
                                    class="btn btn-soft w-full h-11 justify-center gap-2 text-sm mb-3">
                                    <span class="material-symbols-outlined text-base">my_location</span> Gunakan Lokasi Saya
                                </button>
                                <p class="text-xs text-on-surface-variant mb-2">Klik peta atau geser pin untuk menandai lokasi rumah Anda.</p>
                                <div id="delivery-map" class="w-full h-64 rounded-lg border border-outline-variant z-0"></div>
                                <p id="deliv-status" class="text-xs text-on-surface-variant mt-2"></p>

                                <div id="delivery-result" class="hidden mt-3 p-3 rounded-lg bg-primary/5 border border-primary/30 text-sm">
                                    <p><span class="font-semibold">Jarak ke gudang utama:</span> <span id="delivery-distance"></span> km</p>
                                    <p class="mt-1">Ongkir: <strong id="delivery-fee" class="font-mono"></strong></p>
                                </div>

                                <div class="mt-3">
                                    <label class="block text-sm font-semibold text-on-surface mb-1">Alamat Lengkap <span class="text-error">*</span></label>
                                    <textarea name="so_address" rows="2" placeholder="Nama jalan, nomor rumah, patokan, dll."
                                        class="w-full px-4 py-2 bg-white border {{ $errors->has('so_address') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">{{ old('so_address') }}</textarea>
                                    @error('so_address')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest h-fit md:sticky md:top-20">
                <h3 class="font-bold text-on-surface mb-3">Ringkasan ({{ $items->count() }} produk)</h3>
                <div class="divide-y divide-outline-variant/60 text-sm max-h-64 overflow-auto">
                    @foreach($items as $item)
                        @php
                            $itemHarga = (float) ($item->has_product?->product_harga ?? 0);
                            $itemResellerPct = $isReseller ? (float) ($item->has_product?->reseller_fee_percent ?? 0) : 0;
                            $itemHargaEfektif = $itemResellerPct > 0 ? $itemHarga * (1 - $itemResellerPct / 100) : $itemHarga;
                        @endphp
                        <div class="flex items-center justify-between py-2 gap-2">
                            <span class="truncate text-on-surface">{{ $item->has_product?->product_nama }} <span class="text-on-surface-variant">× {{ $item->qty }}</span></span>
                            <span class="font-mono shrink-0">
                                @if($isReseller && $itemResellerPct > 0)
                                    <span class="line-through opacity-60 text-xs">{{ formatAngka((int) ($item->qty * $itemHarga), 'Rp') }}</span>
                                    <span class="text-primary font-semibold">{{ formatAngka((int) ($item->qty * $itemHargaEfektif), 'Rp') }}</span>
                                @else
                                    {{ formatAngka((int) ($item->qty * $itemHarga), 'Rp') }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between pt-3 mt-1 border-t border-outline-variant text-sm text-on-surface-variant">
                    <span>Subtotal</span><span class="font-mono">{{ formatAngka((int) $subtotal, 'Rp') }}</span>
                </div>

                {{-- Kode Diskon --}}
                <div class="pt-2">
                    <div class="flex gap-2" id="discount-input-row">
                        <input type="text" id="discount-code" placeholder="Kode diskon (mis. HEMAT50K)" maxlength="50"
                            value="{{ $discount?->discount_code }}"
                            class="flex-1 h-10 px-3 bg-white border {{ $errors->has('cart') || $errors->has('discount') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm uppercase outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <button type="button" id="btn-redeem" class="btn btn-soft h-10 px-4 text-sm shrink-0">Pakai</button>
                    </div>
                    <p id="discount-msg" class="text-xs mt-1 {{ $discount ? 'text-success hidden' : 'hidden' }}"></p>
                    <input type="hidden" id="discount-amount" value="{{ (float) ($discount?->hitungPotongan((float) $subtotal) ?? 0) }}">
                </div>

                <div id="discount-row" class="hidden justify-between pt-1 text-sm text-on-surface-variant">
                    <span>Diskon <span id="discount-row-code"></span></span>
                    <span class="font-mono text-success" id="discount-row-amount"></span>
                </div>

                <div class="flex justify-between pt-1 text-sm text-on-surface-variant">
                    <span>Ongkir</span><span class="font-mono" id="co-shipping-fee">{{ formatAngka(0, 'Rp') }}</span>
                </div>
                <div class="flex justify-between pt-2 mt-1 border-t border-outline-variant font-bold">
                    <span>Total Bayar</span>
                    <span class="font-mono text-primary text-base">{{ formatAngka((int) $subtotal, 'Rp') }}</span>
                </div>
                <button type="submit" id="btn-submit" class="btn bg-green-600 hover:bg-green-700 text-white w-full h-12 mt-4 text-base">
                    <span class="material-symbols-outlined text-base">qr_code_2</span> Buat Pesanan &amp; Bayar
                </button>
                <p class="text-[11px] text-center text-on-surface-variant mt-2 flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-sm">verified_user</span> Pembayaran via QRIS
                </p>
            </div>
        </form>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script>
        (function () {
            const fmtRp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
            const subtotal = {{ (int) $subtotal }};
            const DEF_LAT = {{ (float) ($warehouse['lat'] ?? -7.644872) }};
            const DEF_LNG = {{ (float) ($warehouse['lng'] ?? 112.904528) }};
            // [nama lokasi => fee flat] — null = hitung dari jarak
            const codFees = @json($codLocations->mapWithKeys(fn ($l) => [$l->location_name => $l->fee !== null ? (float) $l->fee : null]));
            let codFee = 0;
            let delivFee = 0;
            let delivMap = null;
            let delivMarker = null;

            const shipOpts = document.querySelectorAll('.ship-opt');
            const radios = document.querySelectorAll('.shipping-toggle');
            const resultBox = document.getElementById('cod-result');
            const delivStatus = document.getElementById('deliv-status');
            const delivResult = document.getElementById('delivery-result');
            const feeEl = document.getElementById('co-shipping-fee');
            const submitBtn = document.getElementById('btn-submit');

            function selectedMethod() {
                return document.querySelector('.shipping-toggle:checked')?.value || 'pickup';
            }

            // ================= Leaflet loader =================
            let leafletLoading = false;
            function whenLeaflet(cb) {
                if (window.L) { cb(); return; }
                whenLeaflet._queue = whenLeaflet._queue || [];
                whenLeaflet._queue.push(cb);
                if (leafletLoading) return;
                leafletLoading = true;
                const s = document.createElement('script');
                s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                s.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
                s.crossOrigin = '';
                s.onload = () => { (whenLeaflet._queue || []).forEach(fn => fn()); whenLeaflet._queue = []; };
                s.onerror = () => console.error('Gagal memuat Leaflet — peta tidak tersedia.');
                document.head.appendChild(s);
            }

            // ================= Peta delivery =================
            function setPinInputs(lat, lng) {
                addHidden('so_lat', lat);
                addHidden('so_lng', lng);
            }

            function initDeliveryMap(lat, lng) {
                const el = document.getElementById('delivery-map');
                if (!el || typeof L === 'undefined') return;

                if (delivMap) { try { delivMap.remove(); } catch (e) {} delivMap = null; }

                delivMap = L.map(el).setView([lat, lng], 14);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(delivMap);
                delivMarker = L.marker([lat, lng], { draggable: true }).addTo(delivMap);

                delivMarker.on('dragend', () => { const p = delivMarker.getLatLng(); pinMoved(p.lat, p.lng); });
                delivMap.on('click', e => { delivMarker.setLatLng(e.latlng); pinMoved(e.latlng.lat, e.latlng.lng); });

                if (!document.querySelector('#checkout-form input[name="so_lat"]')) {
                    setPinInputs(lat, lng);
                    fetchDelivQuote(lat, lng);
                }
            }

            function pinMoved(lat, lng) {
                setPinInputs(lat, lng);
                fetchDelivQuote(lat, lng);
            }

            async function fetchDelivQuote(lat, lng) {
                delivStatus.textContent = 'Menghitung ongkir…';
                try {
                    const res = await fetch('{{ route("checkout.quoteDelivery") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ lat: lat, lng: lng })
                    });
                    const json = await res.json();
                    if (json.status) {
                        document.getElementById('delivery-distance').textContent = json.distance_km;
                        document.getElementById('delivery-fee').textContent = fmtRp(json.shipping_fee);
                        delivFee = json.shipping_fee;
                        delivResult.classList.remove('hidden');
                        delivStatus.textContent = '';
                    } else {
                        delivResult.classList.add('hidden');
                        delivFee = 0;
                        delivStatus.textContent = json.message || 'Gagal menghitung ongkir.';
                    }
                    updateFee(); updateTotal();
                } catch (e) {
                    delivStatus.textContent = 'Gagal menghubungi server.';
                }
            }

            // ================= Accordion & ongkir =================
            function togglePanels() {
                const method = selectedMethod();

                shipOpts.forEach(opt => {
                    const active = opt.dataset.method === method;
                    opt.querySelector('.ship-pane').classList.toggle('hidden', !active);
                    opt.classList.toggle('border-primary', active);
                    opt.classList.toggle('bg-primary/5', active);
                });

                if (method === 'delivery' && !delivMap) {
                    whenLeaflet(() => initDeliveryMap(DEF_LAT, DEF_LNG));
                }
                if (method !== 'delivery') { delivFee = 0; delivResult.classList.add('hidden'); }
                if (method !== 'cod') { codFee = 0; resultBox.classList.add('hidden'); }
                updateFee(); updateTotal();
            }

            function currentFee() {
                const method = selectedMethod();
                if (method === 'cod') return codFee;
                if (method === 'delivery') return delivFee;
                return 0;
            }

            function updateFee() {
                feeEl.textContent = fmtRp(currentFee());
            }

            function currentDiscount() {
                return parseFloat(document.getElementById('discount-amount')?.value || '0') || 0;
            }

            function updateTotal() {
                const totalEl = document.querySelector('.font-bold .font-mono');
                if (totalEl) totalEl.textContent = fmtRp(Math.max(0, subtotal - currentDiscount() + currentFee()));
            }

            // ================= Kode Diskon =================
            const discountInput = document.getElementById('discount-code');
            const btnRedeem = document.getElementById('btn-redeem');
            const discountMsg = document.getElementById('discount-msg');
            const discountRow = document.getElementById('discount-row');

            function showDiscountRow(code, amount) {
                document.getElementById('discount-amount').value = amount;
                document.getElementById('discount-row-code').textContent = code ? '(' + code + ')' : '';
                document.getElementById('discount-row-amount').textContent = '- ' + fmtRp(amount);
                if (amount > 0) { discountRow.classList.remove('hidden'); discountRow.classList.add('flex'); }
                else { discountRow.classList.add('hidden'); discountRow.classList.remove('flex'); }
                updateTotal();
            }

            async function redeemDiscount() {
                const code = discountInput.value.trim();
                if (!code) return;
                btnRedeem.disabled = true;
                try {
                    const res = await fetch('{{ route("checkout.discount.redeem") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ code })
                    });
                    const json = await res.json();
                    discountMsg.classList.remove('hidden');
                    if (json.status) {
                        discountMsg.textContent = '✓ ' + json.label + ': -' + fmtRp(json.amount);
                        discountMsg.classList.add('text-success'); discountMsg.classList.remove('text-error');
                        showDiscountRow(json.code, json.amount);
                    } else {
                        discountMsg.textContent = json.message || 'Kode tidak valid.';
                        discountMsg.classList.add('text-error'); discountMsg.classList.remove('text-success');
                        showDiscountRow('', 0);
                    }
                } catch (e) {
                    discountMsg.classList.remove('hidden');
                    discountMsg.textContent = 'Gagal menghubungi server.';
                } finally {
                    btnRedeem.disabled = false;
                }
            }

            btnRedeem?.addEventListener('click', redeemDiscount);
            discountInput?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); redeemDiscount(); } });

            // Tampilkan diskon yang sudah diredeem sebelumnya
            if (currentDiscount() > 0 && discountInput.value) {
                showDiscountRow(discountInput.value, currentDiscount());
                discountMsg.classList.remove('hidden');
                discountMsg.textContent = '✓ Kode dipakai';
                discountMsg.classList.add('text-success'); discountMsg.classList.remove('text-error');
            }

            radios.forEach(r => r.addEventListener('change', togglePanels));

            // ================= Pilihan lokasi COD =================
            function pickedCodLocation() {
                const pick = document.querySelector('.cod-pick:checked');
                return pick ? pick.value : null;
            }

            function applyPickedCod() {
                const name = pickedCodLocation();
                if (!name) { codFee = 0; resultBox.classList.add('hidden'); updateFee(); updateTotal(); return; }

                addHidden('so_cod_location', name);
                document.getElementById('cod-name').textContent = name;

                const flat = codFees[name];
                if (flat !== null && flat !== undefined) {
                    // Fee flat — langsung tampil
                    codFee = flat;
                    document.getElementById('cod-fee').textContent = fmtRp(flat);
                    resultBox.classList.remove('hidden');
                } else {
                    // Tanpa harga tetap — estimasi ongkir dari server (jarak gudang → titik COD)
                    fetch('{{ route("checkout.quoteCodLocation") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ location: name })
                    }).then(res => res.json()).then(json => {
                        if (json.status) {
                            codFee = json.shipping_fee;
                            document.getElementById('cod-fee').textContent = fmtRp(json.shipping_fee);
                            resultBox.classList.remove('hidden');
                        } else {
                            codFee = 0;
                            resultBox.classList.add('hidden');
                        }
                        updateFee(); updateTotal();
                    }).catch(() => {});
                }
                updateFee(); updateTotal();
            }

            document.querySelectorAll('.cod-pick').forEach(r => r.addEventListener('change', applyPickedCod));

            // ================= GPS untuk Delivery =================
            document.getElementById('btn-deliv-gps').addEventListener('click', function () {
                if (!navigator.geolocation) {
                    delivStatus.textContent = 'Browser tidak mendukung geolokasi.';
                    return;
                }
                delivStatus.textContent = 'Mengambil lokasi…';
                navigator.geolocation.getCurrentPosition(pos => {
                    delivStatus.textContent = '';
                    if (delivMap && delivMarker) {
                        delivMarker.setLatLng([pos.coords.latitude, pos.coords.longitude]);
                        delivMap.setView([pos.coords.latitude, pos.coords.longitude], 15);
                        pinMoved(pos.coords.latitude, pos.coords.longitude);
                    } else {
                        setPinInputs(pos.coords.latitude, pos.coords.longitude);
                        fetchDelivQuote(pos.coords.latitude, pos.coords.longitude);
                        whenLeaflet(() => initDeliveryMap(pos.coords.latitude, pos.coords.longitude));
                    }
                }, err => {
                    delivStatus.textContent = 'Izin lokasi ditolak. Tandai lokasi rumah langsung di peta.';
                }, { enableHighAccuracy: true, timeout: 10000 });
            });

            function addHidden(name, value) {
                document.querySelectorAll(`#checkout-form input[type=hidden][name="${name}"]`).forEach(el => el.remove());
                const el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                el.value = value;
                document.getElementById('checkout-form').appendChild(el);
            }

            submitBtn.addEventListener('click', e => {
                const method = selectedMethod();
                if (method === 'cod') {
                    const picked = pickedCodLocation() || document.querySelector('#checkout-form input[name="so_cod_location"]')?.value;
                    if (!picked) {
                        e.preventDefault();
                        alert('Silakan pilih lokasi COD terlebih dahulu.');
                        return;
                    }
                    // Fee belum diketahui (lokasi tanpa harga tetap) → wajib titik lokasi
                    if (!codFee && !document.querySelector('#checkout-form input[name="so_lat"]')) {
                        e.preventDefault();
                        alert('Lokasi COD ini dihitung dari jarak — gunakan "Gunakan Lokasi Saya" terlebih dahulu.');
                    }
                } else if (method === 'delivery' && !document.querySelector('#checkout-form input[name="so_lat"]')) {
                    e.preventDefault();
                    alert('Silakan tandai lokasi rumah Anda dulu di peta.');
                }
            });

            togglePanels();
            // Repopulasi setelah validation error
            applyPickedCod();
        })();
    </script>
</x-ecommerce::public-layout>
