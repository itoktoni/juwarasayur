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

                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-outline-variant cursor-pointer hover:bg-surface-container has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="shipping_method" value="pickup" {{ old('shipping_method', 'pickup') === 'pickup' ? 'checked' : '' }}
                                class="mt-1 accent-primary shipping-toggle">
                            <span>
                                <span class="block font-semibold text-on-surface text-sm"><span class="material-symbols-outlined align-middle text-base">store</span> Ambil di Gudang (Pickup)</span>
                                <span class="block text-xs text-on-surface-variant mt-0.5">Gratis — pesanan diambil sendiri di gudang.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-outline-variant cursor-pointer hover:bg-surface-container has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="shipping_method" value="cod" id="radio-cod" {{ old('shipping_method') === 'cod' ? 'checked' : '' }}
                                class="mt-1 accent-primary shipping-toggle">
                            <span>
                                <span class="block font-semibold text-on-surface text-sm"><span class="material-symbols-outlined align-middle text-base">local_shipping</span> COD — Titik Terdekat</span>
                                <span class="block text-xs text-on-surface-variant mt-0.5">Ongkir dihitung dari jarak rumah ke titik COD terdekat.</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-outline-variant cursor-pointer hover:bg-surface-container has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="radio" name="shipping_method" value="delivery" id="radio-delivery" {{ old('shipping_method') === 'delivery' ? 'checked' : '' }}
                                class="mt-1 accent-primary shipping-toggle">
                            <span>
                                <span class="block font-semibold text-on-surface text-sm"><span class="material-symbols-outlined align-middle text-base">home</span> Diantar ke Rumah</span>
                                <span class="block text-xs text-on-surface-variant mt-0.5">Tandai lokasi rumah di peta — ongkir dihitung dari jarak ke gudang utama.</span>
                            </span>
                        </label>
                    </div>

                    {{-- Panel lokasi COD --}}
                    <div id="cod-panel" class="hidden mt-4 pt-4 border-t border-outline-variant">
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

                        <button type="button" id="btn-gps"
                            class="btn btn-soft w-full h-11 justify-center gap-2 text-sm mt-3">
                            <span class="material-symbols-outlined text-base">my_location</span> Gunakan Lokasi Saya (opsional — hitung ongkir dari jarak)
                        </button>
                        <p id="gps-status" class="text-xs text-on-surface-variant mt-2"></p>

                        <div id="cod-result" class="hidden mt-3 p-3 rounded-lg bg-primary/5 border border-primary/30 text-sm">
                            <p><span class="font-semibold">Lokasi COD:</span> <span id="cod-name"></span></p>
                            <p class="text-on-surface-variant text-xs mt-0.5 hidden" id="cod-distance-row"><span id="cod-distance"></span> km dari lokasi Anda</p>
                            <p class="mt-1">Ongkir: <strong id="cod-fee" class="font-mono"></strong></p>
                        </div>

                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-on-surface mb-1">Detail Alamat (opsional)</label>
                            <textarea name="so_address_cod" rows="2" placeholder="Patokan, nomor rumah, dll."
                                class="w-full px-4 py-2 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">{{ old('so_address_cod') }}</textarea>
                            @error('shipping')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                            @error('so_lat')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                            @error('so_cod_location')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Panel diantar ke rumah (delivery) --}}
                    <div id="delivery-panel" class="hidden mt-4 pt-4 border-t border-outline-variant">
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
                    @error('shipping')
                        <p class="text-error text-xs mt-3">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest h-fit md:sticky md:top-20">
                <h3 class="font-bold text-on-surface mb-3">Ringkasan ({{ $items->count() }} produk)</h3>
                <div class="divide-y divide-outline-variant/60 text-sm max-h-64 overflow-auto">
                    @foreach($items as $item)
                        <div class="flex items-center justify-between py-2 gap-2">
                            <span class="truncate text-on-surface">{{ $item->has_product?->product_nama }} <span class="text-on-surface-variant">× {{ $item->qty }}</span></span>
                            <span class="font-mono shrink-0">{{ formatAngka((int) ($item->qty * (float) ($item->has_product?->product_harga ?? 0)), 'Rp') }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between pt-3 mt-1 border-t border-outline-variant text-sm text-on-surface-variant">
                    <span>Subtotal</span><span class="font-mono">{{ formatAngka((int) $subtotal, 'Rp') }}</span>
                </div>
                <div class="flex justify-between pt-1 text-sm text-on-surface-variant">
                    <span>Ongkir</span><span class="font-mono" id="co-shipping-fee">{{ formatAngka(0, 'Rp') }}</span>
                </div>
                <div class="flex justify-between pt-2 mt-1 border-t border-outline-variant font-bold">
                    <span>Total Bayar</span>
                    <span class="font-mono text-primary text-base">{{ formatAngka((int) $subtotal, 'Rp') }}</span>
                </div>
                <button type="submit" id="btn-submit" class="btn btn-primary w-full h-12 mt-4 text-base">
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

            const panelCod = document.getElementById('cod-panel');
            const panelDeliv = document.getElementById('delivery-panel');
            const radios = document.querySelectorAll('.shipping-toggle');
            const btnGps = document.getElementById('btn-gps');
            const gpsStatus = document.getElementById('gps-status');
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

            // ================= Panel & ongkir =================
            function togglePanels() {
                const method = selectedMethod();
                panelCod.classList.toggle('hidden', method !== 'cod');
                panelDeliv.classList.toggle('hidden', method !== 'delivery');

                if (method === 'delivery' && !delivMap) {
                    whenLeaflet(() => initDeliveryMap(DEF_LAT, DEF_LNG));
                }
                if (method !== 'delivery') { delivFee = 0; delivResult.classList.add('hidden'); }
                if (method !== 'cod') { codFee = 0; resultBox.classList.add('hidden'); gpsStatus.textContent = ''; }
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

            function updateTotal() {
                const totalEl = document.querySelector('.font-bold .font-mono');
                if (totalEl) totalEl.textContent = fmtRp(subtotal + currentFee());
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
                    // Fee flat — langsung tampil tanpa perlu titik customer
                    codFee = flat;
                    document.getElementById('cod-fee').textContent = fmtRp(flat);
                    document.getElementById('cod-distance-row').classList.add('hidden');
                    resultBox.classList.remove('hidden');
                    gpsStatus.textContent = '';
                } else {
                    codFee = 0;
                    resultBox.classList.add('hidden');
                    gpsStatus.textContent = 'Lokasi "' + name + '" tidak punya harga tetap — gunakan Lokasi Saya untuk menghitung ongkir dari jarak.';
                }
                updateFee(); updateTotal();
            }

            document.querySelectorAll('.cod-pick').forEach(r => r.addEventListener('change', applyPickedCod));

            // ================= GPS untuk COD =================
            btnGps.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    gpsStatus.textContent = 'Browser tidak mendukung geolokasi.';
                    return;
                }
                gpsStatus.textContent = 'Mengambil lokasi…';
                navigator.geolocation.getCurrentPosition(async pos => {
                    try {
                        setPinInputs(pos.coords.latitude, pos.coords.longitude);
                        const picked = pickedCodLocation();
                        // Lokasi terpilih: hitung ongkir ke lokasi itu;
                        // tanpa pilihan: cari titik COD terdekat
                        const res = await fetch(picked ? '{{ route("checkout.quoteCodLocation") }}' : '{{ route("checkout.quoteCod") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(picked
                                ? { location: picked, lat: pos.coords.latitude, lng: pos.coords.longitude }
                                : { lat: pos.coords.latitude, lng: pos.coords.longitude })
                        });
                        const json = await res.json();
                        if (json.status) {
                            addHidden('so_cod_location', json.location_name);
                            document.getElementById('cod-name').textContent = json.location_name;
                            if (json.distance_km !== null && json.distance_km !== undefined) {
                                document.getElementById('cod-distance').textContent = json.distance_km;
                                document.getElementById('cod-distance-row').classList.remove('hidden');
                            } else {
                                document.getElementById('cod-distance-row').classList.add('hidden');
                            }
                            document.getElementById('cod-fee').textContent = fmtRp(json.shipping_fee);
                            codFee = json.shipping_fee;
                            resultBox.classList.remove('hidden');
                            gpsStatus.textContent = '';
                            // Sinkronkan radio dengan hasil (mis. terpilih otomatis titik terdekat)
                            document.querySelectorAll('.cod-pick').forEach(r => { r.checked = r.value === json.location_name; });
                        } else {
                            resultBox.classList.add('hidden');
                            codFee = 0;
                            gpsStatus.textContent = json.message || 'Gagal menghitung ongkir.';
                        }
                        updateFee(); updateTotal();
                    } catch (e) {
                        gpsStatus.textContent = 'Gagal menghubungi server.';
                    }
                }, err => {
                    gpsStatus.textContent = 'Izin lokasi ditolak. Aktifkan GPS & izinkan akses lokasi.';
                }, { enableHighAccuracy: true, timeout: 10000 });
            });

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
