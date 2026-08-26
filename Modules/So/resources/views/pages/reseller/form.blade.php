<?php /** @var App\Models\User $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Reseller'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)
                <x-input col="6" name="name" label="Nama" />
                <x-input col="6" name="email" type="email" />
                <x-input col="6" name="phone" label="No. HP / WhatsApp" />
                <x-input col="6" name="password" type="password" :helper="$model->exists ? 'Kosongkan jika tidak ingin mengganti password' : null" />
                <x-input col="6" name="fee" type="number" step="0.01" min="0" max="100"
                    label="Fee Komisi (%)"
                    placeholder="{{ rtrim(rtrim((string) config('commission.rate', 2), '0'), '.') }}"
                    helper="Khusus reseller ini. Kosongkan untuk pakai default komisi ({{ rtrim(rtrim((string) config('commission.rate', 2), '0'), '.') }}%)" />
                <div class="col-span-12 md:col-span-6">
                    <label class="flex items-center gap-3 h-12 px-4 bg-white border border-outline-variant rounded-lg cursor-pointer">
                        <input type="hidden" name="consignasi_check" value="1">
                        <input type="checkbox" name="consignasi" value="1" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary-container" @checked(old('consignasi') || $model?->consignasi)>
                        <span class="text-sm font-semibold text-on-surface">Ikut skema Titip Jual (konsinyasi)</span>
                    </label>
                    <p class="text-xs text-on-surface-variant mt-1">Reseller muncul di menu Konsinyasi Hari Ini untuk pencatatan titip barang & tarik uang.</p>
                </div>

                <x-file
                    name="avatar"
                    label="Foto Profil"
                    col="12"
                    accept="image/*"
                    capture="environment"
                    :preview="true"
                    :value="$model?->avatar_url"
                    helper="Ambil foto via kamera di HP atau pilih dari galeri" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
