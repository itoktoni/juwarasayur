<x-ecommerce::account-layout :title="'Dashboard Affiliator'">
    @php
        $user = auth()->user();
    @endphp

    <div class="space-y-6">
        {{-- Greeting --}}
        <div class="relative overflow-hidden p-6 rounded-2xl bg-primary text-on-primary">
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10"></div>
            <p class="text-sm text-on-primary/80">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-extrabold mt-1">Halo, {{ $user->name }} 👋</h1>
            <p class="text-sm text-on-primary/80 mt-1">Pantau performa penjualan tokohmu di sini.</p>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="material-symbols-outlined text-primary text-3xl">today</span>
                    <span class="text-[10px] font-bold uppercase tracking-wide text-on-surface-variant">Hari ini</span>
                </div>
                <p class="text-2xl font-extrabold text-on-surface mt-2">{{ number_format($stats['orders_today'], 0, ',', '.') }}</p>
                <p class="text-xs text-on-surface-variant">Order masuk</p>
            </div>
            <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="material-symbols-outlined text-primary text-3xl">payments</span>
                    <span class="text-[10px] font-bold uppercase tracking-wide text-on-surface-variant">Hari ini</span>
                </div>
                <p class="text-xl font-extrabold text-on-surface mt-2">Rp {{ number_format($stats['revenue_today'], 0, ',', '.') }}</p>
                <p class="text-xs text-on-surface-variant">Omzet hari ini</p>
            </div>
            <a href="{{ route('ecommerce.orders.index') }}" class="p-5 rounded-2xl bg-warning/10 border border-warning/30 shadow-sm hover:bg-warning/20 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="material-symbols-outlined text-warning text-3xl">hourglass_top</span>
                    <span class="text-[10px] font-bold uppercase tracking-wide text-warning">Perlu aksi</span>
                </div>
                <p class="text-2xl font-extrabold text-on-surface mt-2">{{ $stats['unpaid'] }}</p>
                <p class="text-xs text-on-surface-variant">Belum bayar</p>
            </a>
            <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="material-symbols-outlined text-primary text-3xl">inventory</span>
                    <span class="text-[10px] font-bold uppercase tracking-wide text-on-surface-variant">Siap kirim</span>
                </div>
                <p class="text-2xl font-extrabold text-on-surface mt-2">{{ $stats['to_prepare'] }}</p>
                <p class="text-xs text-on-surface-variant">Sedang diproses</p>
            </div>
        </div>

        {{-- Tren 7 hari --}}
        @php
            $weekTotal = $dailySales->sum('total');
            $weekAvg = $dailySales->avg('total');
        @endphp
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            {{-- Chart (2 kolom) --}}
            <div class="lg:col-span-2 p-5 sm:p-6 rounded-2xl bg-white border border-outline-variant/50 shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-on-surface">Omzet 7 Hari Terakhir</h3>
                        <p class="text-xs text-on-surface-variant mt-0.5">Rata-rata harian <span class="font-semibold text-on-surface">Rp {{ number_format($weekAvg, 0, ',', '.') }}</span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-extrabold text-primary font-mono">Rp {{ number_format($weekTotal, 0, ',', '.') }}</p>
                        <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Total minggu ini</p>
                    </div>
                </div>

                {{-- Larapex bar chart --}}
                {!! str_replace('"__Y_FORMAT__"', 'function(value) { return "Rp " + Number(value).toLocaleString("id-ID"); }', $salesChart->script()) !!}
                {!! $salesChart->container() !!}
            </div>

            {{-- Ringkasan + quick actions (1 kolom) --}}
            <div class="space-y-6">
                <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-primary/10 grid place-items-center"><span class="material-symbols-outlined text-primary">group</span></span>
                            <div>
                                <p class="font-bold text-on-surface">{{ $stats['customers'] }}</p>
                                <p class="text-xs text-on-surface-variant">Customer terdaftar</p>
                            </div>
                        </div>
                        <a href="{{ route('account.customers') }}" class="text-primary hover:underline text-sm font-semibold">Lihat</a>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-outline-variant/50">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-primary/10 grid place-items-center"><span class="material-symbols-outlined text-primary">receipt_long</span></span>
                            <div>
                                <p class="font-bold text-on-surface">{{ $stats['total_orders'] }}</p>
                                <p class="text-xs text-on-surface-variant">Total order all time</p>
                            </div>
                        </div>
                        <a href="{{ route('ecommerce.orders.index') }}" class="text-primary hover:underline text-sm font-semibold">Lihat</a>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-primary text-on-primary shadow-sm">
                    <h3 class="font-bold">Aksi Cepat</h3>
                    <div class="mt-4 space-y-2">
                        <button type="button" onclick="goWithdraw()"
                            class="w-full flex items-center gap-2 p-3 rounded-xl bg-white text-primary hover:bg-white/90 transition-colors text-sm font-bold">
                            <span class="material-symbols-outlined text-lg">payments</span> Tarik Komisi
                        </button>
                        <a href="{{ route('shop.index') }}" class="flex items-center gap-2 p-3 rounded-xl bg-white/15 hover:bg-white/25 transition-colors text-sm font-semibold">
                            <span class="material-symbols-outlined text-lg">add_shopping_cart</span> Buat Order Baru
                        </a>
                        <a href="{{ route('account.customers.create') }}" class="flex items-center gap-2 p-3 rounded-xl bg-white/15 hover:bg-white/25 transition-colors text-sm font-semibold">
                            <span class="material-symbols-outlined text-lg">person_add</span> Tambah Customer
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Komisi & Withdraw --}}
        <div id="komisi-card" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 p-5 rounded-2xl bg-primary text-on-primary shadow-sm">
                <p class="text-sm text-on-primary/80">Saldo Komisi Bisa Dicairkan</p>
                <p class="text-3xl font-extrabold mt-1">Rp {{ number_format($commissionBalance, 0, ',', '.') }}</p>
                <p class="text-xs text-on-primary/80 mt-2">Komisi {{ rtrim(rtrim((string) $commissionRate, '0'), '.') }}% dari omzet • Total terhasil: Rp {{ number_format($commissionEarned, 0, ',', '.') }}</p>
                @if($commissionBalance <= 0)
                    <p class="mt-4 inline-flex items-center gap-2 text-xs bg-white/15 px-3 py-2 rounded-lg">
                        <span class="material-symbols-outlined text-base">info</span> Belum ada komisi yang bisa dicairkan. Mulai order untuk menghasilkan komisi.
                    </p>
                @elseif(!empty($user->bank_account_no))
                    <button type="button" onclick="document.getElementById('withdraw-form').classList.toggle('hidden')"
                        class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-primary font-bold text-sm hover:bg-white/90 transition-colors">
                        <span class="material-symbols-outlined text-lg">payments</span> Withdraw
                    </button>
                @else
                    <p class="mt-4 inline-flex items-center gap-2 text-xs bg-white/15 px-3 py-2 rounded-lg">
                        <span class="material-symbols-outlined text-base">info</span> Lengkapi rekening di bawah untuk bisa withdraw
                    </p>
                @endif
            </div>

            {{-- Form withdraw --}}
            <div id="withdraw-form" class="{{ empty($user->bank_account_no) ? 'hidden' : '' }} p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
                <h3 class="font-bold text-on-surface">Ajukan Withdraw</h3>
                <form method="POST" action="{{ route('account.withdraw') }}" class="mt-3 space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1">Jumlah (min Rp {{ number_format(config('commission.min_withdraw', 50000), 0, ',', '.') }} • maks Rp {{ number_format($commissionBalance, 0, ',', '.') }})</label>
                        <input type="number" name="amount" min="{{ config('commission.min_withdraw', 50000) }}" step="1" required value="{{ old('amount') }}"
                            class="w-full h-11 px-3 bg-white border {{ $errors->has('amount') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        @error('amount')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                    </div>
                    <p class="text-[11px] text-on-surface-variant">Dana dikirim ke: <span class="font-semibold">{{ $user->bank_name ?? '-' }} • {{ $user->bank_account_no ?? '-' }} a.n. {{ $user->bank_account_name ?? '-' }}</span></p>
                    <button type="submit" class="btn btn-primary btn-sm w-full gap-1">
                        <span class="material-symbols-outlined text-base">send</span> Ajukan
                    </button>
                </form>
            </div>
        </div>

        {{-- Riwayat withdraw --}}
        @if($withdrawals->isNotEmpty())
            <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
                <h3 class="font-bold text-on-surface mb-3">Riwayat Withdraw</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-on-surface-variant border-b border-outline-variant/50">
                                <th class="py-2 pr-4">Tanggal</th>
                                <th class="py-2 pr-4">Jumlah</th>
                                <th class="py-2 pr-4 hidden sm:table-cell">Rekening</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @foreach($withdrawals as $w)
                                <tr>
                                    <td class="py-2.5 pr-4 text-on-surface-variant">{{ $w->created_at->format('d/m/Y') }}</td>
                                    <td class="py-2.5 pr-4 font-mono font-bold">Rp {{ number_format((float) $w->amount, 0, ',', '.') }}</td>
                                    <td class="py-2.5 pr-4 hidden sm:table-cell text-on-surface-variant">{{ $w->bank_name }} • {{ $w->bank_account_no }}</td>
                                    <td class="py-2.5">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide
                                            {{ $w->status === \App\Models\Withdrawal::STATUS_PAID ? 'bg-success/10 text-success'
                                                : ($w->status === \App\Models\Withdrawal::STATUS_REJECTED ? 'bg-error/10 text-error' : 'bg-warning/15 text-warning') }}">
                                            {{ ucfirst($w->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Data rekening bank --}}
        <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
            <h3 class="font-bold text-on-surface mb-1">Rekening Pencairan</h3>
            <p class="text-xs text-on-surface-variant mb-4">Rekening tujuan transfer komisi Anda.</p>
            <form method="POST" action="{{ route('account.bank.update') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">Nama Bank</label>
                    <input type="text" name="bank_name" list="bank-list" value="{{ old('bank_name', $user->bank_name) }}" required placeholder="cth: BCA"
                        class="w-full h-11 px-3 bg-white border {{ $errors->has('bank_name') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    <datalist id="bank-list">
                        @foreach(['BCA', 'BRI', 'BNI', 'Mandiri', 'BSI', 'CIMB Niaga', 'Permata', 'Danamon', 'GoPay', 'OVO', 'DANA'] as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </datalist>
                    @error('bank_name')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">No. Rekening</label>
                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $user->bank_account_no) }}" required
                        class="w-full h-11 px-3 bg-white border {{ $errors->has('bank_account_no') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('bank_account_no')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface mb-1">Nama Pemilik Rekening</label>
                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', $user->bank_account_name) }}" required
                        class="w-full h-11 px-3 bg-white border {{ $errors->has('bank_account_name') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('bank_account_name')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button type="submit" class="btn btn-primary btn-sm gap-1">
                        <span class="material-symbols-outlined text-base">save</span> Simpan Rekening
                    </button>
                </div>
            </form>
        </div>

        {{-- Recent orders + fee snapshot --}}
        <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-on-surface">Order Terbaru</h3>
                <a href="{{ route('ecommerce.orders.index') }}" class="text-primary hover:underline text-sm font-semibold">Semua →</a>
            </div>
            @if($recentOrders->isEmpty())
                <div class="text-center py-10">
                    <span class="material-symbols-outlined text-5xl text-outline">inbox</span>
                    <p class="text-sm text-on-surface-variant mt-2">Belum ada order. Mulai jualan sekarang!</p>
                    <div class="mt-4 flex items-center justify-center gap-2 text-xs">
                        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary text-on-primary font-semibold hover:opacity-90 transition">
                            <span class="material-symbols-outlined text-sm">storefront</span> Buka Katalog
                        </a>
                        <a href="{{ route('account.customers.create') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/10 text-primary font-semibold hover:bg-primary/20 transition">
                            <span class="material-symbols-outlined text-sm">person_add</span> Tambah Customer
                        </a>
                    </div>
                </div>
            @else
                {{-- Mobile: stacked cards (no horizontal scroll) --}}
                <div class="space-y-3 sm:hidden">
                    @foreach($recentOrders as $order)
                        @php $orderFee = (float) $order->has_details->sum('fee_amount'); @endphp
                        <div class="p-3 rounded-xl border border-outline-variant/40 bg-white">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-mono text-xs font-bold text-primary truncate">{{ $order->so_code }}</p>
                                    <p class="text-sm text-on-surface truncate">{{ $order->has_customer?->name ?? $order->so_customer_name ?? '-' }}</p>
                                </div>
                                <span class="shrink-0 inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide
                                    {{ $order->so_status === \Modules\So\Enums\SoStatusEnum::CANCELLED ? 'bg-error/10 text-error'
                                        : ($order->so_status === \Modules\So\Enums\SoStatusEnum::DELIVERED ? 'bg-success/10 text-success' : 'bg-primary/10 text-primary') }}">
                                    {{ \Modules\So\Enums\SoStatusEnum::getDescription($order->so_status) }}
                                </span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-xs text-on-surface-variant">
                                <span>{{ \Illuminate\Support\Carbon::parse($order->so_tanggal)->format('d/m/Y') }}</span>
                                <span class="font-mono text-on-surface font-semibold">Rp {{ number_format((float) $order->so_grand_total, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-2 pt-2 border-t border-outline-variant/30 flex items-center justify-between">
                                <span class="text-xs text-on-surface-variant">Fee Kamu <span class="ml-1 font-mono font-semibold text-success">+ Rp {{ number_format($orderFee, 0, ',', '.') }}</span></span>
                                <button type="button"
                                    data-fee-popup="{{ $order->id }}"
                                    class="text-xs text-primary font-semibold inline-flex items-center gap-1 hover:underline">
                                    Detail fee <span class="material-symbols-outlined text-base">open_in_new</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop: tabel ringkas --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-on-surface-variant border-b border-outline-variant/50">
                                <th class="py-2 pr-4">Kode</th>
                                <th class="py-2 pr-4">Customer</th>
                                <th class="py-2 pr-4 hidden sm:table-cell">Tanggal</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4 text-right">Total</th>
                                <th class="py-2 pr-4 text-right">Fee Kamu</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @foreach($recentOrders as $order)
                                @php $orderFee = (float) $order->has_details->sum('fee_amount'); @endphp
                                <tr class="hover:bg-surface-container/60 transition-colors">
                                    <td class="py-3 pr-4 font-mono text-xs font-bold text-primary">{{ $order->so_code }}</td>
                                    <td class="py-3 pr-4">{{ $order->has_customer?->name ?? $order->so_customer_name ?? '-' }}</td>
                                    <td class="py-3 pr-4 hidden sm:table-cell text-on-surface-variant">{{ \Illuminate\Support\Carbon::parse($order->so_tanggal)->format('d/m/Y') }}</td>
                                    <td class="py-3 pr-4">
                                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide
                                            {{ $order->so_status === \Modules\So\Enums\SoStatusEnum::CANCELLED ? 'bg-error/10 text-error'
                                                : ($order->so_status === \Modules\So\Enums\SoStatusEnum::DELIVERED ? 'bg-success/10 text-success' : 'bg-primary/10 text-primary') }}">
                                            {{ \Modules\So\Enums\SoStatusEnum::getDescription($order->so_status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4 text-right font-mono font-bold">Rp {{ number_format((float) $order->so_grand_total, 0, ',', '.') }}</td>
                                    <td class="py-3 pr-4 text-right font-mono font-semibold text-success">+ Rp {{ number_format($orderFee, 0, ',', '.') }}</td>
                                    <td class="py-3 text-right">
                                        <button type="button"
                                            data-fee-popup="{{ $order->id }}"
                                            class="text-xs text-primary font-semibold inline-flex items-center gap-1 hover:underline">
                                            <span class="material-symbols-outlined text-base">open_in_new</span> Detail fee
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal: detail fee per order --}}
    <div id="fee-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-sm items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between px-5 py-4 border-b border-outline-variant">
                <div>
                    <h3 class="font-bold text-on-surface">Detail Fee</h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">Order <span id="fee-modal-code" class="font-mono font-semibold text-primary"></span></p>
                </div>
                <button type="button" id="fee-modal-close" class="p-1.5 rounded-lg hover:bg-surface-container text-on-surface-variant">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>
            </div>
            <div class="px-5 py-4 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-on-surface-variant border-b border-outline-variant/60">
                            <th class="py-2 pr-2">Produk</th>
                            <th class="py-2 pr-2 text-center">Qty</th>
                            <th class="py-2 pr-2 text-right">%</th>
                            <th class="py-2 text-right">Fee</th>
                        </tr>
                    </thead>
                    <tbody id="fee-modal-body" class="divide-y divide-outline-variant/40"></tbody>
                    <tfoot>
                        <tr class="border-t-2 border-outline-variant/60 font-bold">
                            <td class="py-2.5 pr-2" colspan="3">Total Fee</td>
                            <td id="fee-modal-total" class="py-2.5 text-right font-mono text-success"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Template baris fee (clone per popup) --}}
    <template id="fee-modal-row-tpl">
        <tr>
            <td class="py-2.5 pr-2 text-on-surface" data-name></td>
            <td class="py-2.5 pr-2 text-center text-on-surface-variant" data-qty></td>
            <td class="py-2.5 pr-2 text-right font-mono text-on-surface-variant" data-pct></td>
            <td class="py-2.5 text-right font-mono font-semibold text-success" data-amount></td>
        </tr>
    </template>

    <script>
        function goWithdraw() {
            var form = document.getElementById('withdraw-form');
            var target = document.getElementById('komisi-card');
            if (form && !emptyBank) {
                form.classList.remove('hidden');
            }
            (target || form)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        const emptyBank = {{ empty($user->bank_account_no) ? 'true' : 'false' }};

        // Data fee per order — dihitung di controller (feePopupData) lalu
        // di-encode sekali di sini agar Blade tidak men-parse struktur arrow-fn.
        window.__feePopupData = {!! json_encode($feePopupData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};

        (function () {
            const modal = document.getElementById('fee-modal');
            const body = document.getElementById('fee-modal-body');
            const total = document.getElementById('fee-modal-total');
            const codeEl = document.getElementById('fee-modal-code');
            const closeBtn = document.getElementById('fee-modal-close');
            const tpl = document.getElementById('fee-modal-row-tpl');
            const fmtPct = (v) => v.toFixed(2).replace(/\.?0+$/, '');
            const fmtRp = (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(v);

            function open(orderId) {
                const data = window.__feePopupData?.[String(orderId)];
                if (! data) return;
                body.innerHTML = '';
                (data.rows || []).forEach((r) => {
                    const row = tpl.content.firstElementChild.cloneNode(true);
                    row.querySelector('[data-name]').textContent = r.name;
                    row.querySelector('[data-qty]').textContent = '×' + r.qty;
                    row.querySelector('[data-pct]').textContent = fmtPct(r.pct) + '%';
                    row.querySelector('[data-amount]').textContent = fmtRp(r.amount);
                    body.appendChild(row);
                });
                codeEl.textContent = data.code;
                total.textContent = fmtRp(data.total);
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function close() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('[data-fee-popup]').forEach((btn) => {
                btn.addEventListener('click', () => open(btn.dataset.feePopup));
            });
            closeBtn.addEventListener('click', close);
            modal.addEventListener('click', (e) => { if (e.target === modal) close(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
        })();
    </script>

    @push('scripts')
        {!! $salesChart->script() !!}
    @endpush
</x-ecommerce::account-layout>
