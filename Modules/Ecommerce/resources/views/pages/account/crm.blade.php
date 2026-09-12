<x-ecommerce::account-layout :title="'CRM Referral'">
    @php $user = auth()->user(); @endphp
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold text-on-surface">CRM</h1>
                <p class="text-sm text-on-surface-variant">Klik link referral vs user baru yang daftar via kamu.</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                @foreach(['7'=>'7 hari','30'=>'30 hari','90'=>'90 hari','all'=>'Semua'] as $k=>$label)
                    <a href="{{ route('account.crm', ['range'=>$k]) }}" class="px-3 py-1.5 rounded-xl border font-semibold {{ $range===$k ? 'bg-primary text-on-primary border-primary' : 'bg-white border-outline-variant hover:bg-surface-container' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-white border border-outline-variant/50 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-on-surface-variant">Klik Link</p>
                <p class="text-2xl font-extrabold mt-1">{{ number_format($stats['totalHits'],0,',','.') }}</p>
                <p class="text-xs text-on-surface-variant">Hari ini {{ $stats['hitsToday'] }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-white border border-outline-variant/50 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-on-surface-variant">User Baru via link</p>
                <p class="text-2xl font-extrabold mt-1">{{ number_format($stats['totalCustomers'],0,',','.') }}</p>
                <p class="text-xs text-success">+{{ $stats['newCustomers7'] }} /7 hari</p>
            </div>
            <div class="p-5 rounded-2xl bg-white border border-outline-variant/50 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-on-surface-variant">Order dari referral</p>
                <p class="text-2xl font-extrabold mt-1">{{ $stats['totalOrders'] }}</p>
                <p class="text-xs text-on-surface-variant">{{ $stats['pendingOrders'] }} pending</p>
            </div>
            <div class="p-5 rounded-2xl bg-primary text-on-primary shadow-sm">
                <p class="text-xs text-on-primary/80 uppercase tracking-wide">Komisi (Fee) • {{ $range==='all' ? 'semua' : $range.' hari' }}</p>
                <p class="text-2xl font-extrabold mt-1">Rp {{ number_format($stats['revenue'],0,',','.') }}</p>
                <p class="text-xs text-on-primary/80">Real fee dari</p>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-outline-variant/50 shadow-sm">
            <h3 class="font-bold mb-3 flex items-center gap-2"><span class="material-symbols-outlined text-primary">monitoring</span> Tren 14 hari — Klik vs Register Baru</h3>
            <div class="bg-surface-container rounded-xl p-4">{!! $chart->container() !!}</div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="p-5 rounded-2xl bg-white border border-outline-variant/50 shadow-sm">
                <h3 class="font-bold mb-3">10 Klik Terbaru</h3>
                <div class="space-y-2 text-xs">
                    @forelse($recentHits as $h)
                        <div class="flex justify-between gap-2 p-2 rounded-lg border border-outline-variant/40">
                            <span class="font-mono">{{ \Carbon\Carbon::parse($h->created_at)->format('d/m H:i') }}</span>
                            <span class="truncate max-w-[180px]">{{ Str::limit($h->landing_url,40) }}</span>
                            <span class="text-on-surface-variant">{{ $h->ip }}</span>
                        </div>
                    @empty <p class="text-on-surface-variant text-center py-6">Belum ada klik. Share link <span class="font-mono">{{ url('/r/'.($user->referral_code??'')) }}</span></p> @endforelse
                </div>
            </div>
            <div class="p-5 rounded-2xl bg-white border border-outline-variant/50 shadow-sm">
                <h3 class="font-bold mb-3">10 Register Baru via Kamu</h3>
                <div class="space-y-2 text-sm">
                    @forelse($recentCustomers as $c)
                        <div class="flex justify-between gap-2 p-2 rounded-lg border border-outline-variant/40">
                            <div><p class="font-semibold">{{ $c->name }}</p><p class="text-xs text-on-surface-variant">{{ $c->email }}</p></div>
                            <span class="text-xs text-on-surface-variant">{{ $c->created_at->format('d/m/Y') }}</span>
                        </div>
                    @empty <p class="text-xs text-on-surface-variant text-center py-6">Belum ada register baru.</p> @endforelse
                </div>
                <a href="{{ route('account.customers') }}" class="mt-3 inline-flex text-xs text-primary font-semibold hover:underline">Lihat semua customer →</a>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-surface-container-low border border-outline-variant/50 text-xs text-on-surface-variant">
            Link kamu: <code class="font-mono bg-white px-2 py-1 rounded">{{ url('/r/'.($user->referral_code??'-')) }}</code> · Alternatif <code class="font-mono bg-white px-2 py-1 rounded">{{ url('/?ref='.($user->referral_code??'-')) }}</code>
        </div>
    </div>
    @push('scripts') {!! $chart->script() !!} @endpush
</x-ecommerce::account-layout>
