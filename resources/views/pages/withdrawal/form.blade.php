<?php /** @var App\Models\Withdrawal $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Pencairan Komisi'], ['url' => '', 'label' => 'Proses #'.$model->id]]" />

    <x-form :model="$model" :method="'POST'">
        <x-card label="Detail Pengajuan">
            <div class="col-span-12 md:col-span-6 space-y-3">
                <div>
                    <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Reseller</label>
                    <div class="w-full h-12 px-4 bg-surface-container border border-outline-variant rounded-lg flex items-center text-sm">{{ $model->has_user?->name ?? '-' }} ({{ $model->has_user?->email ?? '-' }})</div>
                </div>
                <div>
                    <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Jumlah Pencairan</label>
                    <div class="w-full h-12 px-4 bg-surface-container border border-outline-variant rounded-lg flex items-center font-mono font-bold text-sm">{{ formatAngka((float) $model->amount, 'Rp') }}</div>
                </div>
                <div>
                    <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Rekening Tujuan</label>
                    <div class="w-full h-12 px-4 bg-surface-container border border-outline-variant rounded-lg flex items-center text-sm">{{ $model->bank_name }} • {{ $model->bank_account_no }} a.n. {{ $model->bank_account_name }}</div>
                </div>
                <div>
                    <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Diajukan</label>
                    <div class="w-full h-12 px-4 bg-surface-container border border-outline-variant rounded-lg flex items-center text-sm">{{ $model->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </x-card>

        <x-card label="Proses" class="mt-5">
            @bind($model ?? null)
                <x-select col="6" name="status" label="Status" :options="$statusOptions" />
                <x-input col="6" name="note" label="Catatan (opsional)" placeholder="cth: transfer via BCA 26/08" />
            @endbind
            @if($model->status === \App\Models\Withdrawal::STATUS_PAID)
                <p class="col-span-12 text-xs text-success font-semibold">Withdrawal ini sudah DIBAYAR dan tidak bisa diubah lagi.</p>
            @endif
        </x-card>

        <x-action :model="$model" :action="['save', 'delete']"/>
    </x-form>
</x-layouts::app>
