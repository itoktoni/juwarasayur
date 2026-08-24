<x-layouts::app title="Dashboard">
    <div>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-on-surface">Dashboard Reseller</h2>
            <p class="text-sm text-on-surface-variant mt-1">Ringkasan penjualan & customer Anda.</p>
        </div>

        {{-- Statistik utama --}}
        <x-stat-widget :items="[
            [
                'value' => $stats['sales_today'],
                'label' => 'Penjualan Hari Ini',
                'icon_name' => 'today',
                'bg_color' => 'bg-primary/10',
                'icon_color' => 'text-primary',
            ],
            [
                'value' => 'Rp ' . formatAngka($stats['revenue_today']),
                'label' => 'Pendapatan Hari Ini',
                'icon_name' => 'payments',
                'bg_color' => 'bg-success/10',
                'icon_color' => 'text-success',
            ],
            [
                'value' => $stats['unpaid'],
                'label' => 'Belum Bayar',
                'icon_name' => 'money_off',
                'bg_color' => 'bg-error/10',
                'icon_color' => 'text-error',
            ],
            [
                'value' => $stats['customers'],
                'label' => 'Jumlah Customer',
                'icon_name' => 'group',
                'bg_color' => 'bg-info/10',
                'icon_color' => 'text-info',
            ],
        ]" />

        <x-stat-widget :items="[
            [
                'value' => $stats['total_orders'],
                'label' => 'Total Pesanan',
                'icon_name' => 'receipt_long',
                'bg_color' => 'bg-primary/10',
                'icon_color' => 'text-primary',
            ],
            [
                'value' => $stats['to_prepare'],
                'label' => 'Perlu Di-prepare',
                'icon_name' => 'inventory_2',
                'bg_color' => 'bg-warning/10',
                'icon_color' => 'text-warning',
            ],
            [
                'value' => 'Rp ' . formatAngka($stats['revenue_total']),
                'label' => 'Total Pendapatan',
                'icon_name' => 'savings',
                'bg_color' => 'bg-success/10',
                'icon_color' => 'text-success',
            ],
            [
                'value' => $stats['customers'],
                'label' => 'Total Customer',
                'icon_name' => 'person',
                'bg_color' => 'bg-tertiary/10',
                'icon_color' => 'text-tertiary',
            ],
        ]" />

        {{-- Chart --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card mt-5 min-w-0 overflow-hidden">
            <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-success text-xl">trending_up</span>
                Penjualan Saya
            </h3>
            <div class="min-w-0">
                {!! $salesChart->container() !!}
            </div>
        </div>

        {{-- Pesanan terbaru --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card mt-5">
            <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">receipt_long</span>
                Pesanan Terbaru
            </h3>
            @if($recentOrders->isNotEmpty())
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
            @else
            <div class="text-center py-8 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl mb-2 block">receipt_long</span>
                <p class="text-sm">Belum ada pesanan.</p>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
        {!! $salesChart->script() !!}
    @endpush
</x-layouts::app>
