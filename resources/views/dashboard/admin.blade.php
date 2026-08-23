<x-layouts::app title="Dashboard">
    <div>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-on-surface">Dashboard Admin</h2>
            <p class="text-sm text-on-surface-variant mt-1">Ringkasan penjualan & operasional toko.</p>
        </div>

        {{-- Statistik utama --}}
        <x-stat-widget :items="[
            [
                'value' => $stats['total_orders'],
                'label' => 'Total Penjualan',
                'icon_name' => 'receipt_long',
                'bg_color' => 'bg-primary/10',
                'icon_color' => 'text-primary',
            ],
            [
                'value' => 'Rp ' . formatAngka($stats['revenue']),
                'label' => 'Total Pendapatan',
                'icon_name' => 'payments',
                'bg_color' => 'bg-success/10',
                'icon_color' => 'text-success',
            ],
            [
                'value' => $stats['to_prepare'],
                'label' => 'Barang Harus Di-prepare',
                'icon_name' => 'inventory_2',
                'bg_color' => 'bg-warning/10',
                'icon_color' => 'text-warning',
            ],
            [
                'value' => $stats['unpaid'],
                'label' => 'Belum Bayar',
                'icon_name' => 'money_off',
                'bg_color' => 'bg-error/10',
                'icon_color' => 'text-error',
            ],
        ]" />

        <x-stat-widget :items="[
            [
                'value' => $stats['total_customers'],
                'label' => 'Total Customer',
                'icon_name' => 'group',
                'bg_color' => 'bg-info/10',
                'icon_color' => 'text-info',
            ],
            [
                'value' => $stats['total_resellers'],
                'label' => 'Total Reseller',
                'icon_name' => 'storefront',
                'bg_color' => 'bg-tertiary/10',
                'icon_color' => 'text-tertiary',
            ],
            [
                'value' => $stats['total_products'],
                'label' => 'Total Produk',
                'icon_name' => 'category',
                'bg_color' => 'bg-primary/10',
                'icon_color' => 'text-primary',
            ],
            [
                'value' => $stats['to_prepare'],
                'label' => 'Perlu Diproses',
                'icon_name' => 'local_shipping',
                'bg_color' => 'bg-warning/10',
                'icon_color' => 'text-warning',
            ],
        ]" />

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
            <div class="lg:col-span-2 bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">trending_up</span>
                    Pendapatan Penjualan
                </h3>
                <div class="min-w-0">
                    {!! $salesChart->container() !!}
                </div>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">pie_chart</span>
                    Status Pesanan
                </h3>
                <div class="bg-surface-container rounded-lg p-4 min-w-0 overflow-hidden">
                    {!! $statusChart->container() !!}
                </div>
            </div>
        </div>

        {{-- Barang yang harus di-prepare --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card mt-5">
            <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-warning text-xl">inventory_2</span>
                Barang Yang Harus Di-prepare
            </h3>
            @if($toPrepare->isNotEmpty())
            <div class="overflow-x-auto">
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
            @else
            <div class="text-center py-8 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 block">check_circle</span>
                <p class="text-sm">Tidak ada pesanan yang perlu di-prepare.</p>
            </div>
            @endif
        </div>

        {{-- Pesanan terbaru --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card mt-5">
            <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">receipt_long</span>
                Pesanan Terbaru
            </h3>
            <div class="overflow-x-auto">
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
        </div>
    </div>

    @push('scripts')
        {!! $salesChart->script() !!}
        {!! $statusChart->script() !!}
    @endpush
</x-layouts::app>
