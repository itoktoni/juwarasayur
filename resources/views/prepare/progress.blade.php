<x-layouts::app>
    <x-breadcrumb :items="[
        ['url' => route('prepare.index'), 'label' => 'Prepare dari SO'],
        ['url' => '', 'label' => 'Progress'],
    ]" />

    {{-- Statistik ringkas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-5">
        <div class="p-3 md:p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
            <div class="flex items-center justify-between gap-1.5">
                <span class="material-symbols-outlined text-primary text-xl md:text-2xl">inventory_2</span>
                <span class="text-[10px] font-bold uppercase tracking-wide text-on-surface-variant text-right leading-tight">Total<br class="md:hidden"> Item</span>
            </div>
            <p class="text-2xl md:text-2xl font-extrabold text-on-surface mt-1.5">{{ $stats['total'] }}</p>
        </div>
        <div class="p-3 md:p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
            <div class="flex items-center justify-between gap-1.5">
                <span class="material-symbols-outlined text-success text-xl md:text-2xl">check_circle</span>
                <span class="text-[10px] font-bold uppercase tracking-wide text-on-surface-variant text-right">Siap</span>
            </div>
            <p class="text-2xl md:text-2xl font-extrabold text-success mt-1.5">{{ $stats['ready'] }}</p>
        </div>
        <div class="p-3 md:p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
            <div class="flex items-center justify-between gap-1.5">
                <span class="material-symbols-outlined text-warning text-xl md:text-2xl">hourglass_top</span>
                <span class="text-[10px] font-bold uppercase tracking-wide text-on-surface-variant text-right">Sebagian</span>
            </div>
            <p class="text-2xl md:text-2xl font-extrabold text-warning mt-1.5">{{ $stats['partial'] }}</p>
        </div>
        <div class="p-3 md:p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
            <div class="flex items-center justify-between gap-1.5">
                <span class="material-symbols-outlined text-on-surface-variant text-xl md:text-2xl">pending</span>
                <span class="text-[10px] font-bold uppercase tracking-wide text-on-surface-variant text-right">Belum</span>
            </div>
            <p class="text-2xl md:text-2xl font-extrabold text-on-surface mt-1.5">{{ $stats['pending'] }}</p>
        </div>
    </div>

    {{-- Gabungan: Filter + Tabel dalam 1 card --}}
    <x-card label="Progress Persiapan per Item" class="mt-5" :noGrid="true">
        {{-- Filter (collapsible di mobile) --}}
        <div class="border-b border-outline-variant bg-surface-container-low/30">
            <form method="GET" action="{{ route('prepare.progress') }}" class="contents">
                <div class="col-span-12 md:col-span-6 p-3 md:p-0 md:py-4">
                    <label class="text-xs font-bold text-on-surface-variant block mb-1">Cari (Kode SO / Customer / Produk)</label>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode SO, customer, atau produk..."
                        class="w-full h-11 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                </div>
                <div class="col-span-6 md:col-span-3 p-3 pt-0 md:p-0 md:py-4">
                    <label class="text-xs font-bold text-on-surface-variant block mb-1">Status</label>
                    <select name="status" class="w-full h-11 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        <option value="all" {{ $filterStatus === 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="ready" {{ $filterStatus === 'ready' ? 'selected' : '' }}>Siap</option>
                        <option value="partial" {{ $filterStatus === 'partial' ? 'selected' : '' }}>Sebagian</option>
                        <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Belum</option>
                    </select>
                </div>
                <div class="col-span-6 md:col-span-3 p-3 pt-0 md:p-0 md:py-4 flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 h-11 px-4 rounded-lg text-sm font-semibold bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 flex-1 md:flex-none">
                        <span class="material-symbols-outlined text-lg">filter_list</span> Terapkan
                    </button>
                    <a href="{{ route('prepare.progress') }}"
                       class="inline-flex items-center justify-center gap-2 h-11 px-4 rounded-lg text-sm font-semibold bg-surface-container-highest text-on-surface hover:bg-surface-container-low transition-all flex-1 md:flex-none">
                        <span class="material-symbols-outlined text-lg">refresh</span> Reset
                    </a>
                </div>
            </form>
        </div>

        @if($rows->isEmpty())
            <div class="text-center py-10">
                <span class="material-symbols-outlined text-6xl text-outline">inbox</span>
                <p class="text-sm text-on-surface-variant mt-3">Tidak ada item yang cocok dengan filter.</p>
            </div>
        @else
            <x-table :border="false">
                <x-slot:head>
                    <th>SO</th>
                    <th>Customer</th>
                    <th>Produk</th>
                    <th class="text-left">Progress</th>
                    <th>Lokasi</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Aksi</th>
                </x-slot:head>
                <x-slot:body>
                    @foreach($rows as $r)
                        <tr>
                            <td class="font-data-mono text-data-mono text-primary whitespace-nowrap">{{ $r['so']?->so_code ?? '-' }}</td>
                            <td class="text-sm">{{ $r['so']?->so_customer_name ?? $r['so']?->has_customer?->name ?? '-' }}</td>
                            <td>
                                <div class="font-medium text-on-surface">{{ $r['product']?->product_nama ?? '-' }}</div>
                                <div class="text-xs text-on-surface-variant font-data-mono mt-0.5">{{ $r['so_detail']->so_detail_code }}</div>
                            </td>
                            <td class="min-w-[160px]">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 rounded-full bg-surface-container overflow-hidden">
                                        <div class="h-full rounded-full {{ $r['percent'] >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $r['percent'] }}%"></div>
                                    </div>
                                    <span class="text-label-caps text-on-surface-variant w-9 text-right">{{ $r['percent'] }}%</span>
                                </div>
                            </td>
                            <td class="text-xs text-on-surface-variant">{{ $r['lokasi'] }}</td>
                            <td class="text-center">
                                @if($r['status'] === 'ready')
                                    <x-badge type="success">Siap</x-badge>
                                @elseif($r['status'] === 'partial')
                                    <x-badge type="warning">Sebagian</x-badge>
                                @else
                                    <x-badge type="default">Belum</x-badge>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                @if($r['status'] === 'ready')
                                    <a href="{{ route('prepare.printLabel', ['so_id' => $r['so']?->id]) }}"
                                       class="inline-flex items-center justify-center gap-1 h-8 px-3 rounded-lg text-xs font-semibold bg-surface-container-highest text-on-surface hover:bg-surface-container-low transition-all"
                                       title="Print Label untuk SO ini">
                                        <span class="material-symbols-outlined text-base">print</span> Label
                                    </a>
                                @else
                                    <a href="{{ route('prepare.prepareForm', ['product' => $r['product']?->id, 'so_detail_ids' => [$r['so_detail']->id]]) }}"
                                       class="inline-flex items-center justify-center gap-1 h-8 px-3 rounded-lg text-xs font-semibold bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all"
                                       title="Siapkan barang untuk item ini">
                                        <span class="material-symbols-outlined text-base">inventory_2</span> Siapkan
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
                <x-slot:mobile>
                    <div class="divide-y divide-outline-variant/40">
                        @foreach($rows as $r)
                            <div class="px-3 py-3">
                                {{-- Header: Kode SO + status badge --}}
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="font-data-mono text-xs font-bold text-primary truncate">{{ $r['so']?->so_code ?? '-' }}</span>
                                    @if($r['status'] === 'ready')
                                        <x-badge type="success">Siap</x-badge>
                                    @elseif($r['status'] === 'partial')
                                        <x-badge type="warning">Sebagian</x-badge>
                                    @else
                                        <x-badge type="default">Belum</x-badge>
                                    @endif
                                </div>

                                {{-- Body: produk + customer --}}
                                <div class="mb-1.5">
                                    <p class="font-semibold text-on-surface text-sm leading-tight">{{ $r['product']?->product_nama ?? '-' }}</p>
                                    <p class="text-[11px] text-on-surface-variant truncate">{{ $r['so']?->so_customer_name ?? $r['so']?->has_customer?->name ?? '-' }}</p>
                                </div>

                                {{-- Progress + angka --}}
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-1.5 rounded-full bg-surface-container overflow-hidden">
                                        <div class="h-full rounded-full {{ $r['percent'] >= 100 ? 'bg-success' : 'bg-primary' }} transition-all" style="width: {{ $r['percent'] }}%"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-on-surface tabular-nums w-9 text-right">{{ $r['percent'] }}%</span>
                                </div>

                                {{-- Sisa info --}}
                                <div class="flex items-center justify-between text-[11px] mt-1">
                                    <span class="text-on-surface-variant">Sisa</span>
                                    <span class="font-bold {{ $r['sisa'] > 0 ? 'text-warning' : 'text-success' }} tabular-nums">{{ $r['sisa'] }} unit</span>
                                </div>

                                @if($r['lokasi'] !== '—')
                                    <p class="text-[10px] text-on-surface-variant truncate"><span class="material-symbols-outlined text-xs align-middle">place</span> {{ $r['lokasi'] }}</p>
                                @endif

                                {{-- Tombol aksi (footer) --}}
                                <div class="mt-2">
                                    @if($r['status'] === 'ready')
                                        <a href="{{ route('prepare.printLabel', ['so_id' => $r['so']?->id]) }}"
                                           class="flex items-center justify-center gap-1.5 w-full h-9 text-xs font-semibold text-on-surface bg-surface-container-highest active:bg-surface-container transition-colors rounded-lg">
                                            <span class="material-symbols-outlined text-base">print</span> Print Label
                                        </a>
                                    @else
                                        <a href="{{ route('prepare.prepareForm', ['product' => $r['product']?->id, 'so_detail_ids' => [$r['so_detail']->id]]) }}"
                                           class="flex items-center justify-center gap-1.5 w-full h-9 text-xs font-bold text-on-primary bg-primary active:bg-primary/90 transition-colors rounded-lg">
                                            <span class="material-symbols-outlined text-base">inventory_2</span> Siapkan
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-slot:mobile>
            </x-table>
        @endif
    </x-card>
</x-layouts::app>
