<x-layouts::app title="Dashboard">
    <div>
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-on-surface">Dashboard Admin</h2>
                <p class="text-sm text-on-surface-variant mt-1">Ringkasan penjualan & operasional toko.</p>
            </div>
            <a href="{{ route('crm.dashboard') }}" class="btn bg-primary text-on-primary h-10 px-4 inline-flex items-center gap-2 shrink-0">
                <span class="material-symbols-outlined text-base">group</span> CRM Dashboard
            </a>
        </div>

        {{-- Statistik utama — Finansial (klik menuju menu) --}}
        <x-stat-widget :items="[
            [
                'value' => $stats['total_orders'],
                'label' => 'Total Order (SO)',
                'icon_name' => 'receipt_long',
                'bg_color' => 'bg-primary/10',
                'icon_color' => 'text-primary',
                'url' => url('admin/so/so/table'),
            ],
            [
                'value' => 'Rp ' . formatAngka($stats['revenue']),
                'label' => 'Total Pendapatan',
                'icon_name' => 'payments',
                'bg_color' => 'bg-success/10',
                'icon_color' => 'text-success',
                'url' => url('admin/so/so/table'),
            ],
            [
                'value' => 'Rp ' . formatAngka($stats['pengeluaran']),
                'label' => 'Total Pengeluaran (PO)',
                'icon_name' => 'shopping_cart',
                'bg_color' => 'bg-error/10',
                'icon_color' => 'text-error',
                'url' => url('admin/po/po/table'),
            ],
            [
                'value' => 'Rp ' . formatAngka($stats['laba']),
                'label' => 'Laba Rugi (' . $stats['laba_margin'] . '%)',
                'icon_name' => $stats['laba'] >= 0 ? 'trending_up' : 'trending_down',
                'bg_color' => $stats['laba'] >= 0 ? 'bg-success/10' : 'bg-error/10',
                'icon_color' => $stats['laba'] >= 0 ? 'text-success' : 'text-error',
                'url' => url('admin/dashboard'),
            ],
        ]" />

        <x-stat-widget :items="[
            [
                'value' => $stats['to_prepare'],
                'label' => 'Barang Harus Di-prepare',
                'icon_name' => 'inventory_2',
                'bg_color' => 'bg-warning/10',
                'icon_color' => 'text-warning',
                'url' => url('admin/so/so/table?filters[so_status][$eq]=paid'),
            ],
            [
                'value' => $stats['unpaid'],
                'label' => 'Belum Bayar',
                'icon_name' => 'money_off',
                'bg_color' => 'bg-error/10',
                'icon_color' => 'text-error',
                'url' => url('admin/so/so/table?filters[so_status][$eq]=pending'),
            ],
            [
                'value' => $stats['total_po'],
                'label' => 'Total PO',
                'icon_name' => 'request_quote',
                'bg_color' => 'bg-info/10',
                'icon_color' => 'text-info',
                'url' => url('admin/po/po/table'),
            ],
            [
                'value' => $stats['po_pending'] . ' / ' . $stats['po_closed'],
                'label' => 'PO Pending / Closed',
                'icon_name' => 'pending_actions',
                'bg_color' => 'bg-tertiary/10',
                'icon_color' => 'text-tertiary',
                'url' => url('admin/po/po/table?filters[po_status][$eq]=pending'),
            ],
        ]" />

        <x-stat-widget :items="[
            [
                'value' => $stats['total_customers'],
                'label' => 'Total Customer',
                'icon_name' => 'group',
                'bg_color' => 'bg-info/10',
                'icon_color' => 'text-info',
                'url' => route('so-customer.getTable'),
            ],
            [
                'value' => $stats['total_resellers'],
                'label' => 'Total Reseller',
                'icon_name' => 'storefront',
                'bg_color' => 'bg-tertiary/10',
                'icon_color' => 'text-tertiary',
                'url' => route('so-reseller.getTable'),
            ],
            [
                'value' => $stats['total_products'],
                'label' => 'Total Produk',
                'icon_name' => 'category',
                'bg_color' => 'bg-primary/10',
                'icon_color' => 'text-primary',
                'url' => route('catalog-product.getTable'),
            ],
            [
                'value' => $stats['po_ordered'],
                'label' => 'PO Ordered',
                'icon_name' => 'local_shipping',
                'bg_color' => 'bg-warning/10',
                'icon_color' => 'text-warning',
                'url' => url('admin/po/po/table?filters[po_status][$eq]=ordered'),
            ],
        ]" />

        {{-- Cash Flow — Arus Kas --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card mt-5">
            <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">account_balance</span>
                Cash Flow — Arus Kas
            </h3>
            <x-stat-widget :items="[
                [
                    'value' => 'Rp ' . formatAngka($stats['cash_in_30']),
                    'label' => 'Cash In 30 hari (SO)',
                    'icon_name' => 'arrow_upward',
                    'bg_color' => 'bg-success/10',
                    'icon_color' => 'text-success',
                    'url' => url('admin/so/so/table'),
                ],
                [
                    'value' => 'Rp ' . formatAngka($stats['cash_out_30']),
                    'label' => 'Cash Out 30 hari (PO)',
                    'icon_name' => 'arrow_downward',
                    'bg_color' => 'bg-error/10',
                    'icon_color' => 'text-error',
                    'url' => url('admin/po/po/table'),
                ],
                [
                    'value' => 'Rp ' . formatAngka($stats['net_30']),
                    'label' => 'Net 30 hari',
                    'icon_name' => $stats['net_30'] >= 0 ? 'trending_up' : 'trending_down',
                    'bg_color' => $stats['net_30'] >= 0 ? 'bg-primary/10' : 'bg-error/10',
                    'icon_color' => $stats['net_30'] >= 0 ? 'text-primary' : 'text-error',
                    'url' => url('admin/dashboard'),
                ],
                [
                    'value' => 'Rp ' . formatAngka($stats['net_month']),
                    'label' => 'Net Bulan Ini',
                    'icon_name' => 'calendar_month',
                    'bg_color' => 'bg-info/10',
                    'icon_color' => 'text-info',
                    'url' => url('admin/dashboard'),
                ],
            ]" />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-on-surface-variant mt-3">
                <p>Bulan ini: In <span class="font-mono font-semibold text-success">Rp {{ formatAngka($stats['cash_in_month']) }}</span> — Out <span class="font-mono font-semibold text-error">Rp {{ formatAngka($stats['cash_out_month']) }}</span></p>
                <p class="sm:text-right">Klik kartu untuk masuk ke menu terkait.</p>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
            <div class="lg:col-span-2 bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">trending_up</span>
                    Pendapatan Penjualan
                </h3>
                <div class="min-w-0">
                    {!! $salesChart->container() !!}
                </div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">pie_chart</span>
                    Status Pesanan
                </h3>
                <div class="bg-surface-container rounded-lg p-4 min-w-0 overflow-hidden">
                    {!! $statusChart->container() !!}
                </div>
            </div>
        </div>

        {{-- Laba Rugi & PO Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
            <div class="lg:col-span-2 bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-success text-xl">account_balance_wallet</span>
                    Laba Rugi — Revenue vs Pengeluaran
                </h3>
                <div class="min-w-0">
                    {!! $profitChart->container() !!}
                </div>
                <p class="text-xs text-on-surface-variant mt-3">Revenue = SO paid/confirmed/shipped/delivered. Pengeluaran = PO ordered/partial/closed. Laba = Revenue − Pengeluaran. 7 hari terakhir.</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-info text-xl">request_quote</span>
                    Status PO
                </h3>
                <div class="bg-surface-container rounded-lg p-4 min-w-0 overflow-hidden">
                    {!! $poStatusChart->container() !!}
                </div>
            </div>
        </div>

        {{-- Barang yang harus di-prepare --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card mt-5">
            <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-warning text-xl">inventory_2</span>
                Barang Yang Harus Di-prepare
            </h3>
            @if($toPrepare->isNotEmpty())
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-on-surface-variant uppercase border-b border-outline-variant">
                            <th class="pb-3 pr-4">Kode SO</th>
                            <th class="pb-3 pr-4">Customer</th>
                            <th class="pb-3 pr-4">Reseller</th>
                            <th class="pb-3 pr-4">Total</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($toPrepare as $order)
                        <tr class="border-b border-outline-variant/50">
                            <td class="py-3 pr-4 font-medium">{{ $order->so_code }}</td>
                            <td class="py-3 pr-4 text-on-surface-variant">{{ $order->so_customer_name ?: ($order->has_customer?->name ?? '-') }}</td>
                            <td class="py-3 pr-4 text-on-surface-variant">{{ $order->has_reseller?->name ?? '-' }}</td>
                            <td class="py-3 pr-4">Rp {{ formatAngka($order->so_grand_total) }}</td>
                            <td class="py-3">
                                <span class="bg-warning/10 text-warning text-xs px-2 py-1 rounded-full uppercase">{{ $order->so_status }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="md:hidden space-y-3">
                @foreach($toPrepare as $order)
                <div class="border border-outline-variant rounded-2xl p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-medium text-on-surface">{{ $order->so_code }}</span>
                        <span class="bg-warning/10 text-warning text-xs px-2 py-1 rounded-full uppercase shrink-0">{{ $order->so_status }}</span>
                    </div>
                    <p class="text-sm text-on-surface-variant mt-2 truncate">{{ $order->so_customer_name ?: ($order->has_customer?->name ?? '-') }}</p>
                    <div class="flex items-center justify-between gap-2 mt-3">
                        <span class="text-xs text-on-surface-variant truncate">Reseller: {{ $order->has_reseller?->name ?? '-' }}</span>
                        <span class="font-semibold text-on-surface shrink-0">Rp {{ formatAngka($order->so_grand_total) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 block">check_circle</span>
                <p class="text-sm">Tidak ada pesanan yang perlu di-prepare.</p>
            </div>
            @endif
        </div>

        {{-- PO Terbaru (Pengeluaran) --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card mt-5">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-outline-variant">
                <h3 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-error text-xl">request_quote</span>
                    PO Terbaru — Pengeluaran
                </h3>
                <a href="{{ url('admin/po/po/table') }}" class="text-xs font-semibold text-primary hover:underline">Lihat semua →</a>
            </div>
            @if($recentPos->isNotEmpty())
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-on-surface-variant uppercase border-b border-outline-variant">
                            <th class="pb-3 pr-4">Kode PO</th>
                            <th class="pb-3 pr-4">Supplier</th>
                            <th class="pb-3 pr-4">Total</th>
                            <th class="pb-3 pr-4">Tanggal</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPos as $po)
                        <tr class="border-b border-outline-variant/50">
                            <td class="py-3 pr-4 font-medium">{{ $po->po_code }}</td>
                            <td class="py-3 pr-4 text-on-surface-variant">{{ $po->has_supplier?->supplier_nama ?? '-' }}</td>
                            <td class="py-3 pr-4">Rp {{ formatAngka($po->po_grand_total) }}</td>
                            <td class="py-3 pr-4 text-on-surface-variant">{{ optional($po->po_tanggal)->format('d M Y') }}</td>
                            <td class="py-3"><span class="bg-surface-container text-xs px-2 py-1 rounded-full uppercase">{{ $po->po_status }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="md:hidden space-y-3">
                @foreach($recentPos as $po)
                <div class="border border-outline-variant rounded-2xl p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-medium text-on-surface">{{ $po->po_code }}</span>
                        <span class="bg-surface-container text-xs px-2 py-1 rounded-full uppercase shrink-0">{{ $po->po_status }}</span>
                    </div>
                    <p class="text-sm text-on-surface-variant mt-2 truncate">{{ $po->has_supplier?->supplier_nama ?? '-' }}</p>
                    <div class="flex items-center justify-between gap-2 mt-3">
                        <span class="text-xs text-on-surface-variant">{{ optional($po->po_tanggal)->format('d M Y') }}</span>
                        <span class="font-semibold text-error shrink-0">Rp {{ formatAngka($po->po_grand_total) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 block">inbox</span>
                <p class="text-sm">Belum ada Purchase Order.</p>
            </div>
            @endif
        </div>

        {{-- Pesanan terbaru --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card mt-5">
            <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">receipt_long</span>
                Pesanan Terbaru
            </h3>
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-on-surface-variant uppercase border-b border-outline-variant">
                            <th class="pb-3 pr-4">Kode SO</th>
                            <th class="pb-3 pr-4">Customer</th>
                            <th class="pb-3 pr-4">Total</th>
                            <th class="pb-3 pr-4">Tanggal</th>
                            <th class="pb-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        <tr class="border-b border-outline-variant/50">
                            <td class="py-3 pr-4 font-medium">{{ $order->so_code }}</td>
                            <td class="py-3 pr-4 text-on-surface-variant">{{ $order->so_customer_name ?: ($order->has_customer?->name ?? '-') }}</td>
                            <td class="py-3 pr-4">Rp {{ formatAngka($order->so_grand_total) }}</td>
                            <td class="py-3 pr-4 text-on-surface-variant">{{ optional($order->so_tanggal)->format('d M Y') }}</td>
                            <td class="py-3">
                                <span class="bg-surface-container text-xs px-2 py-1 rounded-full uppercase">{{ $order->so_status }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="md:hidden space-y-3">
                @foreach($recentOrders as $order)
                <div class="border border-outline-variant rounded-2xl p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-medium text-on-surface">{{ $order->so_code }}</span>
                        <span class="bg-surface-container text-xs px-2 py-1 rounded-full uppercase shrink-0">{{ $order->so_status }}</span>
                    </div>
                    <p class="text-sm text-on-surface-variant mt-2 truncate">{{ $order->so_customer_name ?: ($order->has_customer?->name ?? '-') }}</p>
                    <div class="flex items-center justify-between gap-2 mt-3">
                        <span class="text-xs text-on-surface-variant">{{ optional($order->so_tanggal)->format('d M Y') }}</span>
                        <span class="font-semibold text-on-surface shrink-0">Rp {{ formatAngka($order->so_grand_total) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
        {!! $salesChart->script() !!}
        {!! $statusChart->script() !!}
        {!! $profitChart->script() !!}
        {!! $poStatusChart->script() !!}
    @endpush
</x-layouts::app>
