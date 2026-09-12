<x-layouts::app title="CRM Referral">
    <div>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-on-surface">CRM Referral Analytics</h2>
                <p class="text-sm text-on-surface-variant mt-1">Berapa kali link dibuka, konversi ke customer & order, top affiliator.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('crm.dashboard') }}" class="btn btn-soft h-9 px-4 text-sm">CRM Dashboard</a>
                <a href="{{ route('dashboard') }}" class="btn btn-soft h-9 px-4 text-sm">← Dashboard</a>
            </div>
        </div>

        {{-- Filter range --}}
        <div class="mb-5 flex items-center gap-2 text-sm">
            <span class="text-on-surface-variant">Range:</span>
            @foreach(['7'=>'7 hari','30'=>'30 hari','90'=>'90 hari','all'=>'Semua'] as $k=>$label)
                <a href="{{ route('crm.referral', ['range'=>$k]) }}" class="px-3 py-1.5 rounded-xl border text-xs font-semibold {{ $range===$k ? 'bg-primary text-on-primary border-primary' : 'bg-white border-outline-variant hover:bg-surface-container' }}">{{ $label }}</a>
            @endforeach
        </div>

        {{-- Stat cards --}}
        <x-stat-widget :items="[
            ['value' => number_format($stats['totalHits'],0,',','.'), 'label' => 'Link dibuka', 'icon_name' => 'ads_click', 'bg_color' => 'bg-primary/10', 'icon_color' => 'text-primary'],
            ['value' => number_format($stats['hitsToday'],0,',','.') .' hari ini', 'label' => $stats['hits7'].' /7 hari', 'icon_name' => 'today', 'bg_color' => 'bg-success/10', 'icon_color' => 'text-success'],
            ['value' => $stats['totalRegistrations'].' daftar', 'label' => $stats['conversionReg'].'% konversi klik→daftar', 'icon_name' => 'person_add', 'bg_color' => 'bg-info/10', 'icon_color' => 'text-info'],
            ['value' => $stats['totalOrders'].' order', 'label' => $stats['conversionOrder'].'% klik→order • Rp '.formatAngka($stats['orderRevenue']), 'icon_name' => 'receipt_long', 'bg_color' => 'bg-warning/10', 'icon_color' => 'text-warning'],
        ]" />

        <x-stat-widget :items="[
            ['value' => $stats['withCode'].'/'.$stats['totalAffiliators'], 'label' => 'Affiliator punya kode', 'icon_name' => 'link', 'bg_color' => 'bg-tertiary/10', 'icon_color' => 'text-tertiary'],
            ['value' => $stats['uniqueAffiliators'].' affiliator', 'label' => 'Link pernah dibuka', 'icon_name' => 'group', 'bg_color' => 'bg-primary/10', 'icon_color' => 'text-primary'],
            ['value' => $stats['totalRegistrations'], 'label' => 'Customer dari referral', 'icon_name' => 'diversity_3', 'bg_color' => 'bg-success/10', 'icon_color' => 'text-success'],
            ['value' => '—', 'label' => 'Klik tanpa konversi: '.max(0,$stats['totalHits']-$stats['totalOrders']), 'icon_name' => 'trending_down', 'bg_color' => 'bg-error/10', 'icon_color' => 'text-error'],
        ]" />

        {{-- Chart tren klik --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
            <div class="lg:col-span-2 bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card min-w-0 overflow-hidden">
                <h3 class="font-semibold text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-xl">monitoring</span> Tren Klik Link (14 hari)
                </h3>
                <div class="bg-surface-container rounded-lg p-4 min-w-0 overflow-hidden">
                    {!! $hitsChart->container() !!}
                </div>
                <p class="text-xs text-on-surface-variant mt-3">Dari <code>referral_hits</code> — setiap buka <code>/r/CODE</code> atau <code>?ref=CODE</code> = 1 hit (cookie 30 hari).</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card">
                <h3 class="font-semibold text-on-surface pb-4 mb-4 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-success text-xl">conversion_path</span> Funnel Sederhana
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-primary/10"><span>Klik link</span><span class="font-mono font-bold">{{ number_format($stats['totalHits'],0,',','.') }}</span></div>
                    <div class="flex items-center justify-center text-on-surface-variant">↓ {{ $stats['conversionReg'] }}%</div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-info/10"><span>Daftar (reference_id)</span><span class="font-mono font-bold">{{ $stats['totalRegistrations'] }}</span></div>
                    <div class="flex items-center justify-center text-on-surface-variant">↓ {{ $stats['conversionOrder'] }}%</div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-success/10"><span>Order (so_id_reseller)</span><span class="font-mono font-bold">{{ $stats['totalOrders'] }}</span></div>
                    <p class="text-xs text-on-surface-variant">Revenue referral: <span class="font-mono font-bold text-success">Rp {{ formatAngka($stats['orderRevenue']) }}</span></p>
                </div>
            </div>
        </div>

        {{-- Top affiliators --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card mt-5">
            <h3 class="font-semibold text-on-surface flex items-center gap-2 pb-4 mb-4 border-b border-outline-variant">
                <span class="material-symbols-outlined text-tertiary">leaderboard</span> Top Affiliator by Klik ({{ $range==='all' ? 'semua' : $range.' hari' }})
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-on-surface-variant uppercase border-b border-outline-variant">
                        <th class="pb-3 pr-4 text-left">Affiliator</th><th class="pb-3 pr-4">Kode</th><th class="pb-3 pr-4 text-center">Klik</th><th class="pb-3 pr-4 text-center">Customer</th><th class="pb-3 pr-4 text-center">Order</th><th class="pb-3 text-right">Revenue</th><th class="pb-3"></th>
                    </tr></thead>
                    <tbody>
                    @forelse($topAffiliators as $row)
                        <tr class="border-b border-outline-variant/50">
                            <td class="py-3 pr-4"><div class="font-medium">{{ $row['user']->name }}</div><div class="text-xs text-on-surface-variant">{{ $row['user']->email }}</div></td>
                            <td class="py-3 pr-4 font-mono text-xs">{{ $row['user']->referral_code ?? '-' }}</td>
                            <td class="py-3 pr-4 text-center font-mono font-bold">{{ $row['hits'] }}</td>
                            <td class="py-3 pr-4 text-center">{{ $row['customers'] }}</td>
                            <td class="py-3 pr-4 text-center">{{ $row['orders'] }}</td>
                            <td class="py-3 text-right font-mono">Rp {{ formatAngka($row['revenue']) }}</td>
                            <td class="py-3 text-right"><a href="{{ route('crm.referral', ['range'=>$range,'affiliator_id'=>$row['user']->id]) }}" class="text-xs text-primary hover:underline">Customer →</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-on-surface-variant text-sm">Belum ada affiliator dengan klik.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($affiliatorFilter && $filteredCustomers)
                <div class="mt-4 p-3 rounded-xl bg-surface-container border border-outline-variant">
                    <p class="text-xs font-semibold">Customer dari affiliator #{{ $affiliatorFilter }} ({{ $filteredCustomers->count() }})</p>
                    <div class="mt-2 space-y-1 text-xs">
                        @foreach($filteredCustomers as $c)
                            <div class="flex justify-between"><span>{{ $c->name }} — {{ $c->email }}</span><span class="text-on-surface-variant">{{ $c->created_at->format('d/m/Y') }}</span></div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Recent hits --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 sm:p-6 form-card mt-5">
            <h3 class="font-semibold text-on-surface flex items-center gap-2 pb-4 mb-4 border-b border-outline-variant">
                <span class="material-symbols-outlined text-primary">history</span> 20 Klik Terbaru
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-xs text-on-surface-variant uppercase border-b border-outline-variant">
                        <th class="pb-3 pr-4 text-left">Waktu</th><th class="pb-3 pr-4">Kode</th><th class="pb-3 pr-4">Affiliator</th><th class="pb-3 pr-4 hidden sm:table-cell">IP</th><th class="pb-3 text-left">Landing URL</th>
                    </tr></thead>
                    <tbody>
                    @forelse($recentHits as $h)
                        <tr class="border-b border-outline-variant/50">
                            <td class="py-2.5 pr-4 text-xs">{{ \Carbon\Carbon::parse($h->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="py-2.5 pr-4 font-mono text-xs font-bold">{{ $h->referral_code }}</td>
                            <td class="py-2.5 pr-4">{{ $h->affiliator_name ?? '-' }}</td>
                            <td class="py-2.5 pr-4 hidden sm:table-cell text-xs">{{ $h->ip }}</td>
                            <td class="py-2.5 text-xs truncate max-w-[280px]">{{ Str::limit($h->landing_url, 60) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-on-surface-variant text-sm">Belum ada hit. Share link <code>/r/CODE</code> dulu.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        {!! $hitsChart->script() !!}
    @endpush
</x-layouts::app>
