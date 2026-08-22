@include('ecommerce::components.brand')
<?php /** @var \Modules\So\Models\So $so */ ?>
<x-layouts::app :title="'Pembayaran'">
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
                {{-- ================= QRIS MOCKUP + TIMER ================= --}}
                <div class="p-6 rounded-xl border border-outline-variant bg-surface-container-lowest text-center">
                    <h2 class="text-xl font-bold text-on-surface">Bayar via QRIS</h2>
                    <p class="text-xs text-on-surface-variant mt-1">Pesanan <strong class="font-mono">{{ $so->so_code }}</strong> — scan & bayar dalam</p>

                    {{-- Timer 5 menit --}}
                    <div id="pay-timer" data-seconds="{{ $secondsLeft }}"
                        class="inline-flex items-center gap-2 mt-3 px-4 py-1.5 rounded-full bg-error/10 border border-error/30 text-error font-bold font-mono text-lg">
                        <span class="material-symbols-outlined text-base">timer</span>
                        <span id="pay-timer-text">{{ sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) }}</span>
                    </div>

                    {{-- QRIS dummy --}}
                    <div class="mt-5 mx-auto w-56 p-3 bg-white rounded-xl border-2 border-outline-variant shadow-sm select-none">
                        <div class="flex items-center justify-between px-1 mb-2">
                            <span class="text-[10px] font-black tracking-widest text-red-700">QRIS</span>
                            <span class="text-[9px] text-on-surface-variant font-semibold">MOCKUP</span>
                        </div>
                        <svg viewBox="0 0 21 21" class="w-full aspect-square" shape-rendering="crispEdges" aria-label="QR Code dummy">
                            <rect width="21" height="21" fill="#fff"/>
                            <g fill="#000">
                                @for($r = 0; $r < 21; $r++)
                                    @for($c = 0; $c < 21; $c++)
                                        @php
                                            $finder = (($r < 7 && $c < 7) || ($r < 7 && $c > 13) || ($r > 13 && $c < 7));
                                            $on = $finder ? !($r % 6 === 3 && $c % 6 === 3) : (($r * 7 + $c * 13 + ($r * $c) % 5) % 3 === 0);
                                        @endphp
                                        @if($on)<rect x="{{ $c }}" y="{{ $r }}" width="1" height="1"/>@endif
                                    @endfor
                                @endfor
                            </g>
                        </svg>
                        <p class="text-[10px] text-on-surface-variant mt-2 font-mono">{{ $so->so_code }}</p>
                    </div>

                    <div class="mt-5 p-4 rounded-lg bg-primary/5 border border-primary/30">
                        <p class="text-xs text-on-surface-variant">Total Pembayaran</p>
                        <p class="text-2xl font-bold font-mono text-primary">{{ formatAngka((float) $so->so_grand_total, 'Rp') }}</p>
                    </div>

                    <form method="POST" action="{{ route('payment.simulate', ['id' => $so->id]) }}" id="simulate-form">
                        @csrf
                        <button type="submit" id="btn-pay"
                            {{ $secondsLeft <= 0 ? 'disabled' : '' }}
                            class="btn btn-primary w-full h-12 mt-4 text-base disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-base">payments</span> Simulasi Bayar
                        </button>
                    </form>
                    <p class="text-[11px] text-on-surface-variant mt-3 leading-relaxed">
                        Halaman ini adalah <strong>mockup</strong> pembayaran. Klik "Simulasi Bayar" untuk menandai pesanan lunas.
                        Jika waktu habis, pesanan tetap tersimpan berstatus <em>Pending</em>.
                    </p>
                </div>

                <script>
                    (function () {
                        const timerBox = document.getElementById('pay-timer');
                        const timerText = document.getElementById('pay-timer-text');
                        const btn = document.getElementById('btn-pay');
                        let left = parseInt(timerBox.dataset.seconds || '0', 10);

                        if (left <= 0) {
                            expire();
                            return;
                        }

                        const t = setInterval(tick, 1000);

                        function tick() {
                            left--;
                            if (left <= 0) { clearInterval(t); expire(); return; }
                            const m = Math.floor(left / 60), s = left % 60;
                            timerText.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                        }

                        function expire() {
                            timerText.textContent = '00:00';
                            if (btn) {
                                btn.disabled = true;
                                btn.innerHTML = '<span class="material-symbols-outlined text-base">block</span> Waktu Habis';
                            }
                        }
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
</x-layouts::app>
