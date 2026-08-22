<?php /** @var Modules\Inventory\Models\Lokasi $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Lokasi'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Informasi Lokasi">
            @bind($model ?? null)
                <x-input col="6" name="lokasi_nama" label="Nama Lokasi" />
                <x-input col="6" name="lokasi_kode" label="Kode" placeholder="Opsional" />
                <x-select col="6" name="lokasi_id_gudang" label="Gudang" :options="$gudangOptions" class="search" />
                <x-select col="6" name="is_active" label="Status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
                <x-input col="6" name="sort_order" label="Urutan" type="number" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
