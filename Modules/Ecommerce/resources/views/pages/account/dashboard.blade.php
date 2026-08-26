<x-ecommerce::account-layout :title="'Dashboard Reseller'">
    @php
        $user = auth()->user();
    @endphp

    <div class="space-y-6">
        {{-- Greeting --}}
        <div class="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-r from-primary to-green-600 text-on-primary">
            <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10"></div>
            <p class="text-sm text-white/80">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="text-2xl font-extrabold mt-1">Halo, {{ $user->name }} 👋</h1>
            <p class="text-sm text-white/80 mt-1">Pantau performa penjualan tokohmu di sini.</p>
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
                @if(!empty($user->bank_account_no))
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
                        <input type="number" name="amount" min="{{ config('commission.min_withdraw', 50000) }}" step="0.01" required value="{{ old('amount') }}"
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

        {{-- Recent orders --}}
        <div class="p-5 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-on-surface">Order Terbaru</h3>
                <a href="{{ route('ecommerce.orders.index') }}" class="text-primary hover:underline text-sm font-semibold">Semua →</a>
            </div>
            @if($recentOrders->isEmpty())
                <div class="text-center py-10">
                    <span class="material-symbols-outlined text-5xl text-outline">inbox</span>
                    <p class="text-sm text-on-surface-variant mt-2">Belum ada order. Mulai jualan sekarang!</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-on-surface-variant border-b border-outline-variant/50">
                                <th class="py-2 pr-4">Kode</th>
                                <th class="py-2 pr-4">Customer</th>
                                <th class="py-2 pr-4 hidden sm:table-cell">Tanggal</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @foreach($recentOrders as $order)
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
                                    <td class="py-3 text-right font-mono font-bold">Rp {{ number_format((float) $order->so_grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

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
    </script>

    @push('scripts')
        {!! $salesChart->script() !!}
    @endpush
</x-ecommerce::account-layout>
