<?php /** @var \Modules\So\Models\So $so */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('dashboard'), 'label' => 'Home'], ['url' => route('so-so.getTable'), 'label' => 'Pesanan'], ['url' => '', 'label' => 'Payment '.$so->so_code]]" />

    <div class="content mt-4 lg:mt-0">
        <div class="max-w-3xl mx-auto space-y-5">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">qr_code_2</span> Payment {{ $so->so_code }}
                </h2>
                <a href="{{ route('so-so.getTable') }}" class="btn btn-soft h-9 px-4 text-sm">Kembali</a>
            </div>

            {{-- Info pesanan --}}
            <div class="p-5 rounded-xl border border-outline-variant bg-surface-container-lowest">
                <div class="grid grid-cols-2 gap-x-4 gap-y-4">
                    <div>
                        <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Customer</p>
                        <p class="font-bold text-on-surface truncate">{{ $so->so_customer_name ?: ($so->has_customer?->name ?? 'Tamu') }}</p>
                        @if($so->so_customer_phone)<p class="text-xs text-on-surface-variant">{{ $so->so_customer_phone }}</p>@endif
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Kode SO / Status</p>
                        <p class="font-bold font-mono text-on-surface">{{ $so->so_code }}</p>
                        <span class="badge badge-soft mt-1 inline-block">{{ \Modules\So\Enums\SoStatusEnum::getDescription($so->so_status) }}</span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Pengiriman</p>
                        <p class="font-semibold text-on-surface">{{ $methodLabel }}@if($so->so_cod_location) — {{ $so->so_cod_location }}@endif</p>
                        @if($so->so_address)<p class="text-xs text-on-surface-variant line-clamp-2">{{ $so->so_address }}</p>@endif
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Total Bayar</p>
                        <p class="font-bold font-mono text-primary text-lg">Rp {{ number_format((float) $so->so_unique_amount, 0, ',', '.') }}</p>
                        <p class="text-xs text-on-surface-variant">Grand total Rp {{ number_format((float) $so->so_grand_total, 0, ',', '.') }}</p>
                    </div>
                </div>
                @if($so->so_status === 'pending')
                    <p class="mt-3 text-xs text-warning bg-warning/10 border border-warning/20 rounded-lg px-3 py-2">Status masih Pending — QR berikut aktif untuk dibayar customer.</p>
                @endif
            </div>

            {{-- QR + Link --}}
            <div class="p-5 rounded-xl border border-outline-variant bg-surface-container-lowest text-center">
                <p class="text-sm font-semibold text-on-surface inline-flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">qr_code_2</span> QRIS Pembayaran
                </p>
                <p class="text-xs text-on-surface-variant mt-1">Scan QR untuk membuka halaman pembayaran publik</p>

                <div class="mt-5 mx-auto w-80 bg-white rounded-xl border-2 border-outline-variant shadow-sm overflow-hidden">
                    @if($qrDataUri)
                        <img src="{{ $qrDataUri }}" alt="QRIS Pembayaran" class="block w-full h-full object-contain">
                    @else
                        <div class="w-full aspect-square flex items-center justify-center text-xs text-error text-center p-4">
                            QRIS belum dikonfigurasi<br><span class="text-on-surface-variant">(set QRIS_PAYLOAD di .env)</span>
                        </div>
                    @endif
                    @if($qrDownload)
                        <div class="px-3 pb-3">
                            <a href="{{ $qrDownload }}" download="qris-{{ strtolower($so->so_code) }}.png"
                                class="mt-2 flex w-full items-center justify-center gap-2 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:opacity-90">
                                <span class="material-symbols-outlined text-base">download</span> Unduh QR
                            </a>
                        </div>
                    @endif
                </div>

                <div class="mt-4 p-3 rounded-lg bg-primary/5 border border-primary/20">
                    <p class="text-xs text-on-surface-variant">Nominal unik (untuk verifikasi otomatis)</p>
                    <p class="text-xl font-bold font-mono text-primary">{{ formatAngka((float) $so->so_unique_amount, 'Rp') }}</p>
                </div>

                <div class="mt-5 text-left">
                    <label class="block text-sm font-semibold text-on-surface mb-2">Link Pembayaran (bagikan ke customer)</label>
                    <div class="flex gap-2">
                        <input id="paymentLink" type="text" readonly value="{{ $paymentLink }}"
                            class="flex-1 h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none">
                        <button type="button" onclick="copyPaymentLink()" id="copyBtn"
                            class="h-12 px-4 rounded-lg bg-primary text-on-primary font-semibold text-sm active:scale-95 transition shrink-0">
                            Salin
                        </button>
                    </div>
                    <p id="copyMsg" class="text-xs text-success mt-2 hidden">Link berhasil disalin!</p>

                    {{-- Tagihan WA: format sesuai request, dinamis per customer / no order / total --}}
                    @php
                        $waCustomer = trim($so->so_customer_name ?: ($so->has_customer?->name ?? 'Kak'));
                        // Fallback kalau snapshot kosong/placeholder: pakai has_customer
                        if ($waCustomer === 'Kak' || $waCustomer === '-' || $waCustomer === '') {
                            $waCustomer = trim($so->has_customer?->name ?? 'Kak');
                        }
                        $waTotal = formatAngka((float) $so->so_unique_amount, 'Rp');
                        $waTagihan = "🥬 TAGIHAN JUWARA SAYUR 🥬\n"
                            ."Halo Kak {$waCustomer}, berikut detail tagihan pesanan sayurnya:\n\n"
                            ."🧾 No. Pesanan: {$so->so_code}\n"
                            ."💰 Total Tagihan: {$waTotal}\n\n"
                            ."Pembayaran dapat dilakukan melalui:\n"
                            ."🏦 Transfer Bank\n"
                            ."Bank: BCA\n"
                            ."No. Rekening: 3452301226\n"
                            ."a.n. : Deny Irawan\n"
                            ."atau\n"
                            ."💳 QRIS\n"
                            ."Silakan scan QRIS untuk pembayaran.\n\n"
                            ."🔗 Link Pembayaran:\n{$paymentLink}\n\n"
                            ."Setelah melakukan pembayaran, mohon kirimkan bukti transfernya ya Kak.\n"
                            ."Terima kasih sudah berbelanja di Juwara Sayur 🌱\n"
                            ."Sayur Segar, Pilihan Juara!";
                    @endphp
                    <textarea id="waTagihanText" class="hidden">{{ $waTagihan }}</textarea>

                    <div class="mt-4 p-3 rounded-lg bg-surface-container border border-outline-variant">
                        <p class="text-xs font-semibold text-on-surface flex items-center gap-1.5"><span class="material-symbols-outlined text-base">chat</span> Teks Tagihan WhatsApp</p>
                        <pre id="waTagihanPreview" class="mt-2 whitespace-pre-wrap break-words text-xs leading-relaxed text-on-surface bg-white border border-outline-variant rounded-lg p-3 max-h-56 overflow-auto">{{ $waTagihan }}</pre>
                        <div class="flex gap-2 mt-3">
                            <button type="button" onclick="copyWaTagihan()" id="copyWaBtn"
                                class="flex-1 inline-flex items-center justify-center gap-2 h-10 rounded-lg bg-primary text-on-primary text-sm font-semibold active:scale-95 transition">
                                <span class="material-symbols-outlined text-base">content_copy</span> Salin Teks Tagihan
                            </button>
                            <a id="waShare" href="https://wa.me/?text={{ urlencode($waTagihan) }}"
                                target="_blank" class="flex-1 inline-flex items-center justify-center gap-2 h-10 rounded-lg bg-[#25D366] text-white text-sm font-semibold">
                                <span class="material-symbols-outlined text-base">chat</span> Kirim via WhatsApp
                            </a>
                        </div>
                        <p id="copyWaMsg" class="text-xs text-success mt-2 hidden">Teks tagihan berhasil disalin! Tinggal paste di WhatsApp.</p>
                        <div class="flex gap-2 mt-2">
                            <a href="{{ route('payment.show', ['token' => $so->so_payment_token]) }}" target="_blank"
                                class="flex-1 inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-neutral-800 text-white text-sm font-semibold">
                                <span class="material-symbols-outlined text-base">open_in_new</span> Buka Halaman Pay
                            </a>
                        </div>
                    </div>
                    <p class="text-[11px] text-on-surface-variant mt-2">Link ini memakai token UUID acak, aman dibagikan — siapa pun dengan link bisa membayar, tidak perlu login.</p>
                </div>
            </div>

            {{-- List pemesanan --}}
            <div class="p-5 rounded-xl border border-outline-variant bg-surface-container-lowest">
                <h3 class="font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">receipt_long</span> List Pemesanan ({{ $so->has_details->count() }} item)
                </h3>
                <div class="divide-y divide-outline-variant/50 mt-4">
                    @foreach($so->has_details as $d)
                        <div class="flex items-center gap-3 py-3">
                            <img src="{{ $d->has_product?->product_gambar_url ?? asset('images/placeholder.png') }}" alt=""
                                class="w-12 h-12 rounded-lg object-cover border border-outline-variant shrink-0" onerror="this.style.display='none'">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-on-surface text-sm truncate">{{ $d->has_product?->product_nama ?? '-' }}</p>
                                <p class="text-xs font-mono text-on-surface-variant">{{ formatAngka((float) $d->so_detail_harga, 'Rp') }} × {{ $d->so_detail_qty }}</p>
                                @if($d->so_detail_keterangan)<p class="text-xs text-on-surface-variant truncate">{{ $d->so_detail_keterangan }}</p>@endif
                            </div>
                            <p class="font-mono font-bold text-on-surface text-sm shrink-0">
                                {{ formatAngka((int) ($d->so_detail_qty * (float) $d->so_detail_harga), 'Rp') }}
                            </p>
                        </div>
                    @endforeach
                </div>
                @php $subtotalProduk = (float) $so->has_details->sum(fn ($d) => $d->so_detail_qty * (float) $d->so_detail_harga); @endphp
                <div class="border-t border-outline-variant mt-2 pt-3 space-y-2 text-sm">
                    <div class="flex justify-between text-on-surface-variant"><span>Subtotal</span><span class="font-mono">{{ formatAngka($subtotalProduk, 'Rp') }}</span></div>
                    @if((float) $so->so_discount > 0)<div class="flex justify-between text-on-surface-variant"><span>Diskon{{ $so->so_discount_note ? ' ('.$so->so_discount_note.')' : '' }}</span><span class="font-mono text-success">- {{ formatAngka((float) $so->so_discount, 'Rp') }}</span></div>@endif
                    @if((float) $so->so_ppn > 0)<div class="flex justify-between text-on-surface-variant"><span>PPN</span><span class="font-mono">{{ formatAngka((float) $so->so_ppn, 'Rp') }}</span></div>@endif
                    @if((float) $so->so_pph > 0)<div class="flex justify-between text-on-surface-variant"><span>PPh</span><span class="font-mono">{{ formatAngka((float) $so->so_pph, 'Rp') }}</span></div>@endif
                    @if((float) $so->so_shipping_fee > 0)<div class="flex justify-between text-on-surface-variant"><span>Ongkir ({{ $methodLabel }})</span><span class="font-mono">{{ formatAngka((float) $so->so_shipping_fee, 'Rp') }}</span></div>@endif
                    <div class="flex justify-between font-bold text-on-surface border-t border-outline-variant pt-2"><span>Total</span><span class="font-mono text-primary">{{ formatAngka((float) $so->so_grand_total, 'Rp') }}</span></div>
                    <div class="flex justify-between text-xs text-primary"><span>Nominal unik dibayar</span><span class="font-mono font-bold">{{ formatAngka((float) $so->so_unique_amount, 'Rp') }}</span></div>
                </div>
            </div>

            <div class="flex gap-2">
                <button onclick="window.print()" class="flex-1 h-11 rounded-lg bg-neutral-800 text-white font-semibold inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">print</span> Cetak Halaman Ini
                </button>
                <a href="{{ route('so-so.getTable') }}" class="h-11 px-6 rounded-lg border border-outline-variant bg-white font-semibold inline-flex items-center justify-center">Tutup</a>
            </div>
        </div>
    </div>

    <script>
        function copyPaymentLink() {
            const input = document.getElementById('paymentLink');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).then(showCopied).catch(() => legacyCopy(input));
            } else { legacyCopy(input); }
        }
        function legacyCopy(input) { input.select(); input.setSelectionRange(0, 99999); document.execCommand('copy'); showCopied(); }
        function showCopied() {
            document.getElementById('copyMsg').classList.remove('hidden');
            const btn = document.getElementById('copyBtn'); btn.textContent = 'Tersalin'; setTimeout(()=> { btn.textContent='Salin'; document.getElementById('copyMsg').classList.add('hidden'); }, 2000);
        }
        function copyWaTagihan() {
            const text = document.getElementById('waTagihanText').value;
            const done = () => {
                const msg = document.getElementById('copyWaMsg');
                msg.classList.remove('hidden');
                const btn = document.getElementById('copyWaBtn');
                const orig = btn.innerHTML;
                btn.innerHTML = '<span class="material-symbols-outlined text-base">check</span> Tersalin';
                setTimeout(()=> { btn.innerHTML = orig; msg.classList.add('hidden'); }, 2000);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done).catch(() => waLegacyCopy(text, done));
            } else { waLegacyCopy(text, done); }
        }
        function waLegacyCopy(text, cb) {
            const ta = document.createElement('textarea');
            ta.value = text; ta.setAttribute('readonly',''); ta.style.position='fixed'; ta.style.opacity='0';
            document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); cb();
        }
    </script>
</x-layouts::app>
