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
                                <span class="block font-semibold text-on-surface text-sm"><span class="material-symbols-outlined align-middle text-base">local_shipping</span> COD — Diantar ke Rumah</span>
                                <span class="block text-xs text-on-surface-variant mt-0.5">Ongkir dihitung dari jarak rumah ke titik COD terdekat.</span>
                            </span>
                        </label>
                    </div>

                    {{-- Panel lokasi COD --}}
                    <div id="cod-panel" class="hidden mt-4 pt-4 border-t border-outline-variant">
                        <p class="text-sm font-semibold text-on-surface mb-2">Lokasi Rumah Anda <span class="text-error">*</span></p>
                        <button type="button" id="btn-gps"
                            class="btn btn-soft w-full h-11 justify-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-base">my_location</span> Gunakan Lokasi Saya
                        </button>
                        <p id="gps-status" class="text-xs text-on-surface-variant mt-2"></p>

                        <div id="cod-result" class="hidden mt-3 p-3 rounded-lg bg-primary/5 border border-primary/30 text-sm">
                            <p><span class="font-semibold">Titik COD terdekat:</span> <span id="cod-name"></span></p>
                            <p class="text-on-surface-variant text-xs mt-0.5"><span id="cod-distance"></span> km dari titik tersebut</p>
                            <p class="mt-1">Ongkir: <strong id="cod-fee" class="font-mono"></strong></p>
                        </div>

                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-on-surface mb-1">Detail Alamat (opsional)</label>
                            <textarea name="so_address" rows="2" placeholder="Patokan, nomor rumah, dll."
                                class="w-full px-4 py-2 bg-white border {{ $errors->has('so_address') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">{{ old('so_address') }}</textarea>
                            @error('shipping')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                            @error('so_lat')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
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

    <script>
        (function () {
            const fmtRp = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n));
            const subtotal = {{ (int) $subtotal }};
            let codFee = 0;
            let gpsBusy = false;

            const panel = document.getElementById('cod-panel');
            const radios = document.querySelectorAll('.shipping-toggle');
            const btnGps = document.getElementById('btn-gps');
            const gpsStatus = document.getElementById('gps-status');
            const resultBox = document.getElementById('cod-result');
            const feeEl = document.getElementById('co-shipping-fee');
            const submitBtn = document.getElementById('btn-submit');

            function isCod() {
                return document.querySelector('.shipping-toggle:checked')?.value === 'cod';
            }

            function togglePanel() {
                panel.classList.toggle('hidden', !isCod());
                updateFee();
            }

            function updateFee() {
                codFee = isCod() ? codFee : 0;
                if (!isCod()) {
                    resultBox.classList.add('hidden');
                    gpsStatus.textContent = '';
                }
                feeEl.textContent = fmtRp(codFee);
            }

            // Total baris terakhir: hitung ulang teks total
            function updateTotal() {
                const totalEl = document.querySelector('.font-bold .font-mono');
                if (totalEl) totalEl.textContent = fmtRp(subtotal + codFee);
            }

            radios.forEach(r => r.addEventListener('change', () => { togglePanel(); updateTotal(); }));

            btnGps.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    gpsStatus.textContent = 'Browser tidak mendukung geolokasi.';
                    return;
                }
                gpsBusy = true;
                gpsStatus.textContent = 'Mengambil lokasi…';
                navigator.geolocation.getCurrentPosition(async pos => {
                    try {
                        const res = await fetch('{{ route("checkout.quoteCod") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude })
                        });
                        const json = await res.json();
                        if (json.status) {
                            document.getElementById('so_lat').remove();
                            addHidden('so_lat', pos.coords.latitude);
                            addHidden('so_lng', pos.coords.longitude);
                            document.getElementById('cod-name').textContent = json.location_name;
                            document.getElementById('cod-distance').textContent = json.distance_km;
                            document.getElementById('cod-fee').textContent = fmtRp(json.shipping_fee);
                            codFee = json.shipping_fee;
                            resultBox.classList.remove('hidden');
                            gpsStatus.textContent = '';
                            updateFee(); updateTotal();
                        } else {
                            resultBox.classList.add('hidden');
                            codFee = 0;
                            gpsStatus.textContent = json.message || 'Gagal menghitung ongkir.';
                            updateFee(); updateTotal();
                        }
                    } catch (e) {
                        gpsStatus.textContent = 'Gagal menghubungi server.';
                    } finally {
                        gpsBusy = false;
                    }
                }, err => {
                    gpsStatus.textContent = 'Izin lokasi ditolak. Aktifkan GPS & izinkan akses lokasi.';
                    gpsBusy = false;
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
                if (isCod() && !document.querySelector('#checkout-form input[name="so_lat"]')) {
                    e.preventDefault();
                    alert('Silakan ambil lokasi Anda dulu untuk menghitung ongkir COD.');
                }
            });

            togglePanel();
        })();
    </script>
</x-ecommerce::public-layout>
