<?php /** @var App\Models\User $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Customer'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)
                <x-input col="6" name="name" label="Nama" />
                <x-input col="6" name="email" type="email" />
                <x-input col="6" name="phone" label="No. HP / WhatsApp" />
                <x-input col="6" name="password" type="password" :helper="$model->exists ? 'Kosongkan jika tidak ingin mengganti password' : null" />

                @if(!empty($resellerOptions))
                    <x-select col="6" name="reference_id" label="Reseller" :options="$resellerOptions" class="search" />
                @endif

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
