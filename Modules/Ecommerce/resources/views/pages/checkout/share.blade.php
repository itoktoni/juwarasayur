<?php /** @var Modules\So\Models\So $so */ ?>

<x-ecommerce::public-layout :title="'Bagikan Link Pembayaran'">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-on-surface">Pesanan {{ $so->so_code }}</h2>
            <p class="text-sm text-on-surface-variant mt-1">Salin atau bagikan link pembayaran ke customer.</p>
        </div>

        {{-- Info pesanan: nama | SO / QRIS | pengiriman --}}
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
            <div class="mt-4 pt-3 border-t border-outline-variant/60 flex items-center justify-between">
                <span class="badge badge-soft">{{ \Modules\So\Enums\SoStatusEnum::getDescription($so->so_status) }}</span>
                <span class="font-bold font-mono text-primary text-lg">Rp {{ formatAngka((int) $so->so_grand_total) }}</span>
            </div>
        </div>

        {{-- QR Siap Bayar + timer: langsung tampil di bawah info customer --}}
        <div class="p-5 rounded-xl border border-outline-variant bg-surface-container-lowest mb-5 text-center">
            <p class="text-sm font-semibold text-on-surface inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">qr_code_2</span> QR Code Siap Bayar
            </p>
            <p class="text-xs text-on-surface-variant mt-1">Scan untuk membuka halaman pembayaran — berlaku dalam</p>

            <div id="pay-timer" data-seconds="{{ $secondsLeft }}"
                class="inline-flex items-center gap-2 mt-2 px-4 py-1.5 rounded-full bg-error/10 border border-error/30 text-error font-bold font-mono text-lg">
                <span class="material-symbols-outlined text-base">timer</span>
                <span id="pay-timer-text">{{ sprintf('%02d:%02d', intdiv($secondsLeft, 60), $secondsLeft % 60) }}</span>
            </div>

            @if($secondsLeft > 0)
                <div id="qr-box" class="mt-5 mx-auto w-56 p-3 bg-white rounded-xl border-2 border-outline-variant shadow-sm select-none">
                    <div class="flex items-center justify-between px-1 mb-2">
                        <span class="text-[10px] font-black tracking-widest text-red-700">QRIS</span>
                        <span class="text-[9px] text-on-surface-variant font-semibold">{{ strtoupper($so->so_code) }}</span>
                    </div>
                    <div class="w-full aspect-square flex items-center justify-center">
                        {!! DNS2D::getBarcodeSVG($link, 'QRCODE', 5, 5) !!}
                    </div>
                    <p class="text-[10px] text-on-surface-variant mt-2 font-mono text-right">{{ formatAngka((float) $so->so_grand_total, 'Rp') }}</p>
                </div>
            @else
                <div id="qr-box" class="mt-4 mx-auto w-56 p-6 bg-surface-container rounded-xl border-2 border-dashed border-outline-variant select-none">
                    <span class="material-symbols-outlined text-5xl text-on-surface-variant/40">qr_code_scanner</span>
                    <p class="text-sm font-semibold text-error mt-3">Waktu Pembayaran Habis</p>
                    <p class="text-xs text-on-surface-variant mt-1">QRCode sudah tidak berlaku.</p>
                </div>
            @endif

            <div class="mt-5 p-4 rounded-lg bg-primary/5 border border-primary/30">
                <p class="text-xs text-on-surface-variant">Total Pembayaran</p>
                <p class="text-2xl font-bold font-mono text-primary">{{ formatAngka((float) $so->so_grand_total, 'Rp') }}</p>
            </div>

            <p class="text-[11px] text-on-surface-variant mt-3">
                Customer cukup scan QR ini untuk langsung membuka halaman pembayaran pesanan.
            </p>
        </div>

        <div class="p-5 rounded-xl border border-outline-variant bg-surface-container-lowest mb-5">
            <label class="block text-sm font-semibold text-on-surface mb-2">Link Pembayaran</label>
            <div class="flex gap-2">
                <input id="shareLink" type="text" readonly value="{{ $link }}"
                    class="flex-1 h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none">
                <button type="button" onclick="copyLink()" id="copyBtn"
                    class="h-12 px-4 rounded-lg bg-primary text-on-primary font-semibold text-sm active:scale-95 transition">
                    Salin
                </button>
            </div>
            <p id="copyMsg" class="text-xs text-success mt-2 hidden">Link berhasil disalin!</p>
        </div>

        {{-- Daftar produk --}}
        <div class="p-5 rounded-xl border border-outline-variant bg-surface-container-lowest mb-5">
            <button type="button" onclick="toggleProducts()"
                class="w-full flex items-center justify-between gap-2 text-left">
                <span class="inline-flex items-center gap-2 text-sm font-semibold text-on-surface">
                    <span class="material-symbols-outlined text-primary text-xl">inventory_2</span> Daftar Produk ({{ $so->has_details->count() }})
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
                    $isPercentDiscount = $so->so_discount_type === 'percent';
                @endphp
                <div class="flex items-center justify-between pt-3 border-t border-outline-variant/60 text-sm text-on-surface-variant">
                    <span>Subtotal</span>
                    <span class="font-mono">{{ formatAngka($subtotalProduk, 'Rp') }}</span>
                </div>
                @if((float) $so->so_discount > 0)
                    <div class="flex items-center justify-between text-sm text-on-surface-variant">
                        <span>Diskon{{ $isPercentDiscount ? ' (' . rtrim(rtrim((string) $so->so_discount, '0'), '.') . '%)' : '' }}</span>
                        <span class="font-mono text-success">- {{ formatAngka($isPercentDiscount ? $subtotalProduk * (float) $so->so_discount / 100 : (float) $so->so_discount, 'Rp') }}</span>
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

    </div>

    <script>
        function copyLink() {
            const input = document.getElementById('shareLink');
            // Clipboard API hanya ada di secure context (HTTPS) — fallback ke execCommand untuk HTTP
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).then(showCopied).catch(() => legacyCopy(input));
            } else {
                legacyCopy(input);
            }
        }
        function legacyCopy(input) {
            input.select();
            input.setSelectionRange(0, 99999);
            document.execCommand('copy');
            showCopied();
        }
        function showCopied() {
            document.getElementById('copyMsg').classList.remove('hidden');
            const btn = document.getElementById('copyBtn');
            btn.textContent = 'Tersalin';
            setTimeout(() => { btn.textContent = 'Salin'; }, 2000);
        }

        function toggleSection(sectionId, chevronId) {
            const section = document.getElementById(sectionId);
            const chevron = document.getElementById(chevronId);
            section.classList.toggle('hidden');
            chevron.style.transform = section.classList.contains('hidden') ? '' : 'rotate(180deg)';
        }
        function toggleProducts() { toggleSection('products-section', 'products-chevron'); }

        // Timer QR siap bayar — jalan otomatis saat halaman dibuka
        (function () {
            const timerBox = document.getElementById('pay-timer');
            if (!timerBox) return;
            const timerText = document.getElementById('pay-timer-text');
            const qrBox = document.getElementById('qr-box');
            let left = parseInt(timerBox.dataset.seconds || '0', 10);

            if (left <= 0) { timerText.textContent = '00:00'; return; }

            const t = setInterval(tick, 1000);
            function tick() {
                left--;
                if (left <= 0) { clearInterval(t); expire(); return; }
                timerText.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
            }
            function expire() {
                timerText.textContent = '00:00';
                if (!qrBox) return;
                qrBox.className = 'mt-4 mx-auto w-56 p-6 bg-surface-container rounded-xl border-2 border-dashed border-outline-variant select-none';
                qrBox.innerHTML =
                    '<span class="material-symbols-outlined text-5xl text-on-surface-variant/40">qr_code_scanner</span>' +
                    '<p class="text-sm font-semibold text-error mt-3">Waktu Pembayaran Habis</p>' +
                    '<p class="text-xs text-on-surface-variant mt-1">QRCode sudah tidak berlaku.</p>';
            }
        })();
    </script>
</x-ecommerce::public-layout>
