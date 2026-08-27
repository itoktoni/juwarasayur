<x-ecommerce::public-layout :title="'Pembayaran'">
    <div class="content mt-4 lg:mt-0">
        <div class="max-w-md mx-auto">

            @if($so->so_status === \Modules\So\Enums\SoStatusEnum::PAID)
                {{-- ================= SUKSES ================= --}}
                <div class="p-6 rounded-xl border border-outline-variant bg-surface-container-lowest text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-green-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-green-600 text-4xl">check_circle</span>
                    </div>
                    <h2 class="text-xl font-bold text-on-surface mt-4">Pembayaran Berhasil!</h2>
                    <p class="text-sm text-on-surface-variant mt-1">Pesanan Anda sudah dibayar dan sedang diproses.</p>

                    <div class="mt-5 p-4 rounded-lg bg-primary/5 border border-primary/30 text-left text-sm space-y-2">
                        <div class="flex justify-between"><span class="text-on-surface-variant">Kode Pesanan</span><strong class="font-mono">{{ $so->so_code }}</strong></div>
                        <div class="flex justify-between"><span class="text-on-surface-variant">Total Dibayar</span><strong class="font-mono text-primary">{{ formatAngka((float) $so->so_grand_total, 'Rp') }}</strong></div>
                        <div class="flex justify-between"><span class="text-on-surface-variant">Metode Bayar</span><span>QRIS</span></div>
                        <div class="flex justify-between"><span class="text-on-surface-variant">Pengiriman</span><span>{{ $methodLabel }}@if($so->so_cod_location) — {{ $so->so_cod_location }}@endif</span></div>
                        <div class="flex justify-between"><span class="text-on-surface-variant">Atas Nama</span><span>{{ $so->so_customer_name }}</span></div>
                    </div>

                    <div class="mt-4 p-3 rounded-lg border border-outline-variant text-left text-sm divide-y divide-outline-variant/60">
                        @foreach($so->has_details as $d)
                            <div class="flex items-center justify-between py-1.5 gap-2">
                                <span class="truncate">{{ $d->has_product?->product_nama }} × {{ $d->so_detail_qty }}</span>
                                <span class="font-mono shrink-0">{{ formatAngka((int) ($d->so_detail_qty * (float) $d->so_detail_harga), 'Rp') }}</span>
                            </div>
                        @endforeach
                        @if((float) $so->so_shipping_fee > 0)
                            <div class="flex items-center justify-between py-1.5 gap-2 text-on-surface-variant">
                                <span>Ongkir</span><span class="font-mono">{{ formatAngka((float) $so->so_shipping_fee, 'Rp') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 mt-5">
                        <a href="{{ route('shop.index') }}" class="btn btn-primary flex-1 h-11 justify-center">
                            <span class="material-symbols-outlined text-base">storefront</span> Belanja
                        </a>
                        <a href="{{ route('payment.invoice', ['token' => $so->so_payment_token]) }}" target="_blank"
                            class="btn btn-soft flex-1 h-11 justify-center">
                            <span class="material-symbols-outlined text-base">print</span> Invoice
                        </a>
                        @auth
                            <a href="{{ route('ecommerce.orders.show', ['id' => $so->id]) }}" class="btn btn-soft flex-1 h-11 justify-center">
                                <span class="material-symbols-outlined text-base">receipt_long</span> Pesanan
                            </a>
                        @endauth
                    </div>

                    @guest
                        <p class="text-xs text-on-surface-variant mt-4">
                            Simpan kode pesanan <strong class="font-mono">{{ $so->so_code }}</strong> sebagai bukti. Login untuk melihat riwayat.
                        </p>
                    @endguest
                </div>
            @else
                {{-- ================= INFO PESANAN: nama | SO / QRIS | pengiriman ================= --}}
                <div class="p-5 rounded-xl border border-outline-variant bg-surface-container-lowest mb-5">
                    <div class="grid grid-cols-2 gap-x-4 gap-y-5">
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Atas Nama</p>
                            <p class="font-bold text-on-surface truncate">{{ $so->so_customer_name ?: 'Tamu' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Kode SO</p>
                            <p class="font-bold font-mono text-on-surface">{{ $so->so_code }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Pembayaran</p>
                            <p class="font-semibold text-on-surface inline-flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-primary text-base">qr_code_2</span> QRIS
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Pengiriman</p>
                            <p class="font-semibold text-on-surface">{{ $methodLabel }}@if($so->so_cod_location) — {{ $so->so_cod_location }}@endif</p>
                        </div>
                    </div>
                </div>

                {{-- ================= QRIS + TIMER ================= --}}
                <div class="p-6 rounded-xl border border-outline-variant bg-surface-container-lowest text-center">
                    <h2 class="text-xl font-bold text-on-surface">Bayar via QRIS</h2>
                    <p class="text-xs text-on-surface-variant mt-1">Scan & bayar dalam</p>

                    {{-- Timer 5 menit --}}
                    <div id="pay-timer" data-seconds="{{ $secondsLeft }}"
                        class="inline-flex items-center gap-2 mt-3 px-4 py-1.5 rounded-full bg-error/10 border border-error/30 text-error font-bold font-mono text-lg">
                        <span class="material-symbols-outlined text-base">timer</span>
                        <span id="pay-timer-text">{{ sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) }}</span>
                    </div>

                    @if($secondsLeft > 0)
                        {{-- QRIS asli — scan sesuai total pembayaran --}}
                        <div id="qris-box" class="mt-5 mx-auto w-80 bg-white rounded-xl border-2 border-outline-variant shadow-sm select-none">
                            <div class="w-full aspect-square flex items-center justify-center p-0 m-0">
                                @if($qrisPayload)
                                    <img src="{{ qrCodeDataUri($qrisPayload, 400) }}"
                                        alt="QRIS Pembayaran"
                                        class="block w-full h-full object-contain select-none">
                                @else
                                    <div class="w-full aspect-square flex items-center justify-center text-xs text-error text-center p-2">
                                        QRIS belum dikonfigurasi (set QRIS di .env)
                                    </div>
                                @endif
                            </div>
                            @if($qrisPayload)
                                <div class="px-3 pb-3">
                                    <a href="{{ $qrDownload }}"
                                        download="qris-{{ strtolower($so->so_code) }}.png"
                                        class="mt-2 flex w-full items-center justify-center gap-2 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:opacity-90">
                                        <span class="material-symbols-outlined text-base">download</span>
                                        Unduh QR
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Waktu habis: QR disembunyikan --}}
                        <div class="mt-5 mx-auto w-56 p-6 bg-surface-container rounded-xl border-2 border-dashed border-outline-variant select-none">
                            <span class="material-symbols-outlined text-5xl text-on-surface-variant/40">qr_code_scanner</span>
                            <p class="text-sm font-semibold text-error mt-3">Waktu Pembayaran Habis</p>
                            <p class="text-xs text-on-surface-variant mt-1">QRCode sudah tidak berlaku.</p>
                        </div>
                    @endif

                    <div class="mt-5 p-4 rounded-lg bg-primary/5 border border-primary/30">
                        <p class="text-xs text-on-surface-variant">Total Pembayaran</p>
                        <p class="text-2xl font-bold font-mono text-primary">{{ formatAngka((float) $so->so_grand_total, 'Rp') }}</p>
                    </div>

                    <p class="text-[11px] text-on-surface-variant mt-3 leading-relaxed">
                        Scan QRIS di atas dengan aplikasi pembayaran Anda. Halaman ini akan
                        <strong>otomatis terupdate</strong> saat pembayaran diterima.
                    </p>
                </div>

                {{-- ================= DAFTAR PRODUK (collapse) ================= --}}
                <div class="mt-5 p-5 rounded-xl border border-outline-variant bg-surface-container-lowest">
                    <button type="button" onclick="toggleProducts()"
                        class="w-full flex items-center justify-between gap-2 text-left">
                        <span class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface">
                            <span class="material-symbols-outlined text-primary text-xl">inventory_2</span>
                            Daftar Produk ({{ $so->has_details->count() }})
                        </span>
                        <span id="products-chevron" class="material-symbols-outlined text-on-surface-variant">expand_more</span>
                    </button>

                    <div id="products-section" class="hidden mt-4 pt-4 border-t border-outline-variant/60 space-y-3">
                        @foreach($so->has_details as $d)
                            <div class="flex items-center gap-3">
                                <img src="{{ $d->has_product?->product_gambar_url ?: asset('images/placeholder.png') }}" alt=""
                                    class="w-14 h-14 rounded-lg object-cover border border-outline-variant shrink-0"
                                    onerror="this.style.display='none'">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-on-surface text-sm truncate">{{ $d->has_product?->product_nama }}</p>
                                    <p class="text-xs font-mono text-on-surface-variant">
                                        {{ formatAngka((float) $d->so_detail_harga, 'Rp') }} × {{ $d->so_detail_qty }}
                                    </p>
                                </div>
                                <p class="font-mono font-bold text-on-surface text-sm shrink-0">
                                    {{ formatAngka((int) ($d->so_detail_qty * (float) $d->so_detail_harga), 'Rp') }}
                                </p>
                            </div>
                        @endforeach

                        {{-- Rincian biaya --}}
                        @php
                            $subtotalProduk = (float) $so->has_details->sum(fn ($d) => $d->so_detail_qty * (float) $d->so_detail_harga);
                        @endphp
                        <div class="flex items-center justify-between pt-3 border-t border-outline-variant/60 text-sm text-on-surface-variant">
                            <span>Subtotal</span>
                            <span class="font-mono">{{ formatAngka($subtotalProduk, 'Rp') }}</span>
                        </div>
                        @if((float) $so->so_discount > 0)
                            <div class="flex items-center justify-between text-sm text-on-surface-variant">
                                <span>Diskon{{ $so->so_discount_note ? ' ('.$so->so_discount_note.')' : '' }}</span>
                                <span class="font-mono text-success">- {{ formatAngka((float) $so->so_discount, 'Rp') }}</span>
                            </div>
                        @endif
                        @if((float) $so->so_ppn > 0)
                            <div class="flex items-center justify-between text-sm text-on-surface-variant">
                                <span>PPN {{ rtrim(rtrim((string) $so->so_ppn_rate, '0'), '.') !== '' ? '(' . rtrim(rtrim((string) $so->so_ppn_rate, '0'), '.') . '%)' : '' }}</span>
                                <span class="font-mono">{{ formatAngka((float) $so->so_ppn, 'Rp') }}</span>
                            </div>
                        @endif
                        @if((float) $so->so_pph > 0)
                            <div class="flex items-center justify-between text-sm text-on-surface-variant">
                                <span>PPh {{ rtrim(rtrim((string) $so->so_pph_rate, '0'), '.') !== '' ? '(' . rtrim(rtrim((string) $so->so_pph_rate, '0'), '.') . '%)' : '' }}</span>
                                <span class="font-mono">{{ formatAngka((float) $so->so_pph, 'Rp') }}</span>
                            </div>
                        @endif
                        @if((float) $so->so_shipping_fee > 0)
                            <div class="flex items-center justify-between text-sm text-on-surface-variant">
                                <span>Ongkos Kirim</span>
                                <span class="font-mono">{{ formatAngka((float) $so->so_shipping_fee, 'Rp') }}</span>
                            </div>
                        @endif
                        <div class="flex items-center justify-between pt-3 border-t border-outline-variant text-sm">
                            <span class="font-bold text-on-surface">Total</span>
                            <span class="font-bold font-mono text-primary">{{ formatAngka((float) $so->so_grand_total, 'Rp') }}</span>
                        </div>
                    </div>
                </div>

                    <script>
                        function toggleProducts() {
                            const section = document.getElementById('products-section');
                            const chevron = document.getElementById('products-chevron');
                            section.classList.toggle('hidden');
                            chevron.style.transform = section.classList.contains('hidden') ? '' : 'rotate(180deg)';
                        }

                        (function () {
                            const timerBox = document.getElementById('pay-timer');
                            const timerText = document.getElementById('pay-timer-text');
                            const qrisBox = document.getElementById('qris-box');
                            let left = parseInt(timerBox.dataset.seconds || '0', 10);

                            if (left <= 0) {
                                timerText.textContent = '00:00';
                                return;
                            }

                            const t = setInterval(tick, 1000);

                            function tick() {
                                left--;
                                if (left <= 0) { clearInterval(t); expire(); return; }
                                const m = Math.floor(left / 60), s = left % 60;
                                timerText.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                            }

                            // Waktu habis → QR disembunyikan, diganti placeholder
                            function expire() {
                                timerText.textContent = '00:00';
                                if (!qrisBox) return;
                                qrisBox.className = 'mt-5 mx-auto w-56 p-6 bg-surface-container rounded-xl border-2 border-dashed border-outline-variant select-none';
                                qrisBox.innerHTML =
                                    '<span class="material-symbols-outlined text-5xl text-on-surface-variant/40">qr_code_scanner</span>' +
                                    '<p class="text-sm font-semibold text-error mt-3">Waktu Pembayaran Habis</p>' +
                                    '<p class="text-xs text-on-surface-variant mt-1">QRCode sudah tidak berlaku.</p>';
                            }
                        })();

                    // Polling status pembayaran setiap 2 detik — jika status di DB
                    // berubah menjadi PAID (mis. dikonfirmasi admin), halaman otomatis reload.
                    (function () {
                        const url = '{{ route("payment.status", ["token" => $so->so_payment_token]) }}';
                        let stopped = false;

                        async function check() {
                            if (stopped) return;
                            try {
                                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                                const json = await res.json();
                                if (json.paid) {
                                    stopped = true;
                                    location.reload();
                                }
                            } catch (e) { /* abaikan error sementara */ }
                        }

                        setInterval(check, 2000);
                    })();
                </script>
            @endif

            <div class="text-center mt-4">
                <a href="{{ route('shop.index') }}" class="text-sm text-on-surface-variant hover:text-primary inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali belanja
                </a>
            </div>
        </div>
    </div>
</x-ecommerce::public-layout>
