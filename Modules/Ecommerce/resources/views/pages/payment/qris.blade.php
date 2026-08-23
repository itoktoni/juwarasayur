@php
    // QRIS asli dari .env (QRIS=...) dengan nominal sesuai total pembayaran
    $qrisPayload = ! empty(config('ecommerce.qris_payload'))
        ? nominalQRIS(config('ecommerce.qris_payload'), (float) $so->so_grand_total)
        : null;
@endphp
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
                            <span class="material-symbols-outlined text-base">storefront</span> Belanja Lagi
                        </a>
                        <a href="{{ route('payment.invoice', ['token' => $so->so_payment_token]) }}" target="_blank"
                            class="btn btn-soft flex-1 h-11 justify-center">
                            <span class="material-symbols-outlined text-base">print</span> Print Invoice
                        </a>
                        @auth
                            <a href="{{ route('ecommerce.orders.show', ['id' => $so->id]) }}" class="btn btn-soft flex-1 h-11 justify-center">
                                <span class="material-symbols-outlined text-base">receipt_long</span> Lihat Pesanan
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
                {{-- ================= QRIS + TIMER ================= --}}
                <div class="p-6 rounded-xl border border-outline-variant bg-surface-container-lowest text-center">
                    <h2 class="text-xl font-bold text-on-surface">Bayar via QRIS</h2>
                    <p class="text-xs text-on-surface-variant mt-1">Pesanan <strong class="font-mono">{{ $so->so_code }}</strong> — scan & bayar dalam</p>

                    {{-- Timer 5 menit --}}
                    <div id="pay-timer" data-seconds="{{ $secondsLeft }}"
                        class="inline-flex items-center gap-2 mt-3 px-4 py-1.5 rounded-full bg-error/10 border border-error/30 text-error font-bold font-mono text-lg">
                        <span class="material-symbols-outlined text-base">timer</span>
                        <span id="pay-timer-text">{{ sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) }}</span>
                    </div>

                    @if($secondsLeft > 0)
                        {{-- QRIS asli — scan sesuai total pembayaran --}}
                        <div id="qris-box" class="mt-5 mx-auto w-56 p-3 bg-white rounded-xl border-2 border-outline-variant shadow-sm select-none">
                            <div class="flex items-center justify-between px-1 mb-2">
                                <span class="text-[10px] font-black tracking-widest text-red-700">QRIS</span>
                                <span class="text-[9px] text-on-surface-variant font-semibold">{{ strtoupper($so->so_code) }}</span>
                            </div>
                            @if($qrisPayload)
                                <div class="w-full aspect-square flex items-center justify-center">
                                    {!! DNS2D::getBarcodeSVG($qrisPayload, 'QRCODE', 5, 5) !!}
                                </div>
                            @else
                                <div class="w-full aspect-square flex items-center justify-center text-xs text-error text-center p-2">
                                    QRIS belum dikonfigurasi (set QRIS di .env)
                                </div>
                            @endif
                            <p class="text-[10px] text-on-surface-variant mt-2 font-mono text-right">{{ formatAngka((float) $so->so_grand_total, 'Rp') }}</p>
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

                    <script>
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
