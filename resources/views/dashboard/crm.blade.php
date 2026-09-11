<x-layouts::app title="CRM Dashboard">
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-on-surface">CRM Dashboard</h2>
                <p class="text-sm text-on-surface-variant mt-1">Order tidak bayar, frekuensi belanja, churn & loyalitas.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-soft h-9 px-4 text-sm">← Dashboard Utama</a>
        </div>

        {{-- Unpaid --}}
        <x-stat-widget :items="[
            [
                'value' => $stats['unpaidCount'],
                'label' => 'Order Belum Bayar',
                'icon_name' => 'money_off',
                'bg_color' => 'bg-error/10',
                'icon_color' => 'text-error',
                'url' => url('admin/so/so/table?filters[so_status][$eq]=pending'),
            ],
            [
                'value' => $stats['unpaidPeople'] . ' orang',
                'label' => 'Orang Belum Bayar',
                'icon_name' => 'group_off',
                'bg_color' => 'bg-warning/10',
                'icon_color' => 'text-warning',
                'url' => url('admin/so/so/table?filters[so_status][$eq]=pending'),
            ],
            [
                'value' => 'Rp ' . formatAngka($stats['unpaidSum']),
                'label' => 'Nominal Tertahan',
                'icon_name' => 'payments',
                'bg_color' => 'bg-error/10',
                'icon_color' => 'text-error',
                'url' => url('admin/so/so/table?filters[so_status][$eq]=pending'),
            ],
            [
                'value' => $stats['aging']['>7d'] . ' >7h',
                'label' => 'Aging >7 hari',
                'icon_name' => 'hourglass_bottom',
                'bg_color' => 'bg-tertiary/10',
                'icon_color' => 'text-tertiary',
                'url' => url('admin/so/so/table?filters[so_status][$eq]=pending'),
            ],
        ]" />

        {{-- Frekuensi 7 hari --}}
        <x-stat-widget :items="[
            [
                'value' => $stats['active7'] . ' orang',
                'label' => 'Aktif 7 hari',
                'icon_name' => 'bolt',
                'bg_color' => 'bg-success/10',
                'icon_color' => 'text-success',
                'url' => url('admin/so/so/table'),
            ],
            [
                'value' => $stats['freqOnce'] . ' orang',
                'label' => 'Cuma 1×/minggu',
                'icon_name' => 'looks_one',
                'bg_color' => 'bg-primary/10',
                'icon_color' => 'text-primary',
                'url' => url('admin/so/so/table'),
            ],
            [
                'value' => $stats['churnCount'] . ' orang',
                'label' => 'Churn risk (hilang 7h)',
                'icon_name' => 'trending_down',
                'bg_color' => 'bg-warning/10',
                'icon_color' => 'text-warning',
                'url' => url('admin/so/customer/table'),
            ],
            [
                'value' => $stats['dormant30'] . ' orang',
                'label' => 'Dormant 30h+',
                'icon_name' => 'bedtime',
                'bg_color' => 'bg-error/10',
                'icon_color' => 'text-error',
                'url' => url('admin/so/customer/table'),
            ],
        ]" />

        <x-stat-widget :items="[
            [
                'value' => $stats['totalCustomers'] . ' orang',
                'label' => 'Total Customer',
                'icon_name' => 'group',
                'bg_color' => 'bg-info/10',
                'icon_color' => 'text-info',
                'url' => route('so-customer.getTable'),
            ],
            [
                'value' => $stats['everOrderedKeys'] . ' orang',
                'label' => 'Pernah Order',
                'icon_name' => 'shopping_bag',
                'bg_color' => 'bg-primary/10',
                'icon_color' => 'text-primary',
                'url' => url('admin/so/so/table'),
            ],
            [
                'value' => $stats['repeatRate'] . '%',
                'label' => 'Repeat 30h (≥2×)',
                'icon_name' => 'repeat',
                'bg_color' => 'bg-success/10',
                'icon_color' => 'text-success',
                'url' => url('admin/so/so/table'),
            ],
            [
                'value' => $stats['freqMulti'] . ' orang',
                'label' => '>2×/minggu (loyal)',
                'icon_name' => 'star',
                'bg_color' => 'bg-tertiary/10',
                'icon_color' => 'text-tertiary',
                'url' => url('admin/so/so/table'),
            ],
        ]" />

        {{-- Charts CRM --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5">
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-error text-xl">schedule</span>
                    Unpaid Aging
                </h3>
                <div class="bg-surface-container rounded-lg p-4 min-w-0 overflow-hidden">
                    {!! $unpaidChart->container() !!}
                </div>
                <p class="text-xs text-on-surface-variant mt-3">0–1h / 1–3h / 3–7h / &gt;7 hari sejak order pending.</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-headline-md text-headline-md text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">bar_chart</span>
                    Frekuensi 7 hari
                </h3>
                <div class="bg-surface-container rounded-lg p-4 min-w-0 overflow-hidden">
                    {!! $freqChart->container() !!}
                </div>
                <p class="text-xs text-on-surface-variant mt-3">1×, 2×, &gt;2× per minggu vs 0× (tidak belanja).</p>
            </div>
        </div>

        {{-- Detail tables --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-5">
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-outline-variant">
                    <h3 class="font-semibold text-on-surface flex items-center gap-2"><span class="material-symbols-outlined text-error">money_off</span> Order Belum Bayar (terlama)</h3>
                    <a href="{{ url('admin/so/so/table?filters[so_status][$eq]=pending') }}" class="text-xs font-semibold text-primary hover:underline">Lihat semua →</a>
                </div>
                <div class="space-y-3">
                    @forelse($unpaidList as $o)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-outline-variant/50 hover:bg-surface-container">
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-semibold truncate">{{ $o->so_code }}</p>
                            <p class="text-xs text-on-surface-variant truncate">{{ $o->so_customer_name ?: $o->has_customer?->name }} • {{ $o->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-mono text-sm font-bold">Rp {{ formatAngka($o->so_grand_total) }}</p>
                            <a href="{{ route('so-so.getPayment', ['id'=>$o->id]) }}" class="text-xs text-success font-semibold hover:underline">Tagih →</a>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-on-surface-variant text-center py-6">Tidak ada unpaid.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-outline-variant">
                    <h3 class="font-semibold text-on-surface flex items-center gap-2"><span class="material-symbols-outlined text-primary">looks_one</span> Cuma 1×/minggu (perlu follow-up)</h3>
                    <span class="text-xs text-on-surface-variant">7 hari terakhir</span>
                </div>
                <div class="space-y-3">
                    @forelse($onceList as $r)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-outline-variant/50">
                        <div class="min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $r->so_customer_name ?: 'Customer #'.$r->so_id_customer }}</p>
                            <p class="text-xs text-on-surface-variant">1× order • Rp {{ formatAngka($r->total) }}</p>
                        </div>
                        <span class="badge badge-soft text-xs">1×</span>
                    </div>
                    @empty
                    <p class="text-sm text-on-surface-variant text-center py-6">Tidak ada.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card mt-5">
            <h3 class="font-semibold text-on-surface flex items-center gap-2 pb-4 mb-4 border-b border-outline-variant"><span class="material-symbols-outlined text-tertiary">leaderboard</span> Top Customer 30 hari (by frekuensi)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-on-surface-variant uppercase border-b border-outline-variant"><th class="pb-3 pr-4 text-left">Customer</th><th class="pb-3 pr-4 text-center">Order</th><th class="pb-3 text-right">Total</th></tr></thead>
                    <tbody>
                    @foreach($top30 as $t)
                    <tr class="border-b border-outline-variant/50"><td class="py-3 pr-4 font-medium truncate">{{ $t->so_customer_name ?: 'Cust #'.$t->so_id_customer }}</td><td class="py-3 pr-4 text-center">{{ $t->cnt }}×</td><td class="py-3 text-right font-mono">Rp {{ formatAngka($t->total) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        {!! $unpaidChart->script() !!}
        {!! $freqChart->script() !!}
    @endpush
</x-layouts::app>
