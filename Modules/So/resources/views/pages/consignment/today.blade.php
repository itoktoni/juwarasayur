<?php /** @var Illuminate\Support\Collection $resellers */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('so-consignment.getTable'), 'label' => 'Titip Jual'], ['url' => '', 'label' => 'Konsinyasi Hari Ini']]" />

    <div class="content mt-4 lg:mt-0">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-xl font-extrabold text-on-surface">Konsinyasi Hari Ini</h2>
                <p class="text-sm text-on-surface-variant">{{ $today->translatedFormat('l, d F Y') }} — reseller dengan skema titip jual</p>
            </div>
            <a href="{{ route('so-consignment.getCreate') }}" wire:navigate class="btn btn-primary btn-sm">
                <span class="material-symbols-outlined text-base">add</span> Titip Barang
            </a>
        </div>

        @php
            $totalConsigned = $resellers->sum('qty_consigned');
            $totalSold = $resellers->sum('qty_sold');
            $totalCollected = $resellers->sum('amount_collected');
            $openTotal = $resellers->sum('open_count');
        @endphp

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="p-4 rounded-2xl bg-primary text-on-primary shadow-sm">
                <p class="text-xs text-on-primary/80">Reseller Konsinyasi</p>
                <p class="text-2xl font-extrabold mt-1">{{ $resellers->count() }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
                <p class="text-xs text-on-surface-variant">Barang Dititipkan Hari Ini</p>
                <p class="text-2xl font-extrabold text-on-surface mt-1">{{ number_format($totalConsigned, 0, ',', '.') }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
                <p class="text-xs text-on-surface-variant">Terjual</p>
                <p class="text-2xl font-extrabold text-on-surface mt-1">{{ number_format($totalSold, 0, ',', '.') }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-surface-container-lowest border border-outline-variant/50 shadow-sm">
                <p class="text-xs text-on-surface-variant">Nilai Ditarik</p>
                <p class="text-xl font-extrabold text-primary font-mono mt-1">{{ formatAngka($totalCollected, 'Rp') }}</p>
            </div>
        </div>

        {{-- Daftar reseller --}}
        @if($resellers->isEmpty())
            <div class="text-center py-16 bg-surface-container-lowest rounded-2xl border border-outline-variant/50">
                <span class="material-symbols-outlined text-5xl text-outline">storefront</span>
                <h3 class="font-bold text-on-surface mt-3">Belum Ada Reseller Konsinyasi</h3>
                <p class="text-sm text-on-surface-variant mt-1">Aktifkan "Ikut skema Titip Jual" di menu Reseller.</p>
                <a href="{{ route('so-reseller.getTable') }}" wire:navigate class="btn btn-soft btn-sm mt-4">Buka Menu Reseller</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($resellers as $r)
                    <div class="rounded-2xl border border-outline-variant/50 bg-surface-container-lowest shadow-sm overflow-hidden">
                        {{-- Header --}}
                        <div class="flex items-center justify-between p-4 border-b border-outline-variant/50 {{ $r['open_count'] > 0 ? 'bg-warning/10' : '' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-10 h-10 rounded-full bg-primary/10 grid place-items-center shrink-0">
                                    <span class="material-symbols-outlined text-primary">storefront</span>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-on-surface truncate">{{ $r['reseller']->name }}</p>
                                    <p class="text-xs text-on-surface-variant truncate">{{ $r['reseller']->phone ?? $r['reseller']->email }}</p>
                                </div>
                            </div>
                            @if($r['open_count'] > 0)
                                <span class="badge badge-soft text-warning shrink-0">{{ $r['open_count'] }} berjalan</span>
                            @elseif($r['settled_count'] > 0)
                                <span class="badge badge-soft text-success shrink-0">selesai</span>
                            @endif
                        </div>

                        {{-- Stats --}}
                        <div class="grid grid-cols-3 divide-x divide-outline-variant/40 text-center py-3">
                            <div>
                                <p class="font-bold text-on-surface">{{ number_format($r['qty_consigned'], 0, ',', '.') }}</p>
                                <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Dititip</p>
                            </div>
                            <div>
                                <p class="font-bold text-primary">{{ number_format($r['qty_sold'], 0, ',', '.') }}</p>
                                <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Terjual</p>
                            </div>
                            <div>
                                <p class="font-mono font-bold text-on-surface text-sm pt-0.5">{{ formatAngka($r['amount_collected'], 'Rp') }}</p>
                                <p class="text-[10px] uppercase tracking-wide text-on-surface-variant">Tarikan</p>
                            </div>
                        </div>

                        {{-- Rincian produk hari ini --}}
                        @if($r['details']->isNotEmpty())
                            <div class="px-4 pb-3 space-y-1.5 max-h-44 overflow-y-auto">
                                @foreach($r['details']->groupBy(fn ($d) => $d->has_product?->product_nama ?? '-') as $nama => $items)
                                    <div class="flex items-center justify-between text-xs px-2.5 py-1.5 rounded-lg bg-surface-container">
                                        <span class="truncate font-medium text-on-surface mr-2">{{ $nama }}</span>
                                        <span class="font-mono shrink-0 text-on-surface-variant">
                                            {{ number_format($items->sum('qty'), 0, ',', '.') }} titip
                                            • <span class="text-primary font-bold">{{ number_format($items->sum(fn ($i) => (float) ($i->qty_sold ?? 0)), 0, ',', '.') }} laku</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Footer action --}}
                        <div class="flex items-center justify-between gap-2 p-3 border-t border-outline-variant/50">
                            <a href="{{ route('so-consignment.getCreate') }}?user_id={{ $r['reseller']->id }}" wire:navigate
                                class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">
                                <span class="material-symbols-outlined text-sm">add_box</span> Titip barang
                            </a>
                            @if($r['open_count'] > 0)
                                @php
                                    $openId = $r['reseller']->has_consignments->firstWhere('status', \Modules\So\Enums\ConsignmentStatusEnum::OPEN)?->id;
                                @endphp
                                @if($openId)
                                    <a href="{{ route('so-consignment.getSettle', ['id' => $openId]) }}" wire:navigate
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary text-on-primary text-xs font-bold hover:bg-primary/90 transition-colors">
                                        <span class="material-symbols-outlined text-sm">payments</span> Tarik Uang
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts::app>
