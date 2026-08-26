<?php /** @var Modules\So\Models\Consignment $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Titip Jual'], ['url' => '', 'label' => 'Invoice — '.$model->code]]" />

    <div class="content mt-4 lg:mt-0">
        {{-- Toolbar --}}
        <div class="flex gap-2 mb-5">
            <a href="{{ route('so-consignment.getTable') }}" class="btn btn-soft btn-sm">
                <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <span class="material-symbols-outlined text-base">print</span> Print Invoice
            </button>
        </div>

        <div class="max-w-xl bg-white rounded-2xl border border-outline-variant shadow-sm p-8 print:shadow-none print:border-0">
            {{-- Header --}}
            <div class="flex items-start justify-between border-b-2 border-neutral-900 pb-4">
                <div>
                    @php $site = \App\Models\WebsiteSetting::merged(); @endphp
                    <h1 class="text-xl font-extrabold">{{ $site['name'] ?? config('app.name') }}</h1>
                    <p class="text-xs text-on-surface-variant mt-1">{{ $site['alamat'] ?? '' }}{{ !empty($site['telepon']) ? ' — '.$site['telepon'] : '' }}</p>
                </div>
                <div class="text-right">
                    <h2 class="font-extrabold tracking-widest">INVOICE</h2>
                    <p class="text-xs font-mono text-on-surface-variant">{{ $model->code }}</p>
                    <p class="text-xs text-on-surface-variant">Ditarik: {{ optional($model->settled_at)->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            {{-- Info reseller --}}
            <div class="grid grid-cols-2 gap-3 py-4 text-sm">
                <div>
                    <p class="text-xs text-on-surface-variant uppercase tracking-wide">Dititipkan kepada</p>
                    <p class="font-bold">{{ $model->has_reseller?->name ?? '-' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-on-surface-variant uppercase tracking-wide">Tanggal titip</p>
                    <p class="font-bold">{{ $model->consignment_date->format('d/m/Y') }}</p>
                </div>
            </div>

            {{-- Items --}}
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-y border-outline-variant text-left text-xs uppercase tracking-wide text-on-surface-variant">
                        <th class="py-2 pr-3">Produk</th>
                        <th class="py-2 px-2 text-right">Titip</th>
                        <th class="py-2 px-2 text-right">Terjual</th>
                        <th class="py-2 px-2 text-right">Sisa</th>
                        <th class="py-2 pl-2 text-right">Harga</th>
                        <th class="py-2 pl-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    @foreach($model->has_details as $d)
                        <tr>
                            <td class="py-2 pr-3 font-medium">{{ $d->has_product?->product_nama ?? '-' }}</td>
                            <td class="py-2 px-2 text-right font-mono">{{ number_format((float) $d->qty, 0, ',', '.') }}</td>
                            <td class="py-2 px-2 text-right font-mono font-bold text-primary">{{ number_format((float) ($d->qty_sold ?? 0), 0, ',', '.') }}</td>
                            <td class="py-2 px-2 text-right font-mono text-on-surface-variant">{{ number_format((float) ($d->qty_returned ?? 0), 0, ',', '.') }}</td>
                            <td class="py-2 pl-2 text-right font-mono">{{ number_format((float) $d->price, 0, ',', '.') }}</td>
                            <td class="py-2 pl-2 text-right font-mono font-bold">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-neutral-900">
                        <td colspan="4"></td>
                        <td class="py-3 pl-2 text-right font-bold">TOTAL TAGIHAN</td>
                        <td class="py-3 pl-2 text-right font-extrabold text-lg font-mono">{{ formatAngka((float) $model->total_amount, 'Rp') }}</td>
                    </tr>
                </tfoot>
            </table>

            <p class="mt-6 text-center text-xs text-on-surface-variant">Terima kasih — invoice ini dibuat otomatis oleh sistem.</p>
        </div>
    </div>

    <style>@media print { .btn, header nav, aside, footer { display: none !important; } }</style>
</x-layouts::app>
