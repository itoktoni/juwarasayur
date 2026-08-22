<?php /** @var Modules\Inventory\Models\Gudang $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Warehouse'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Informasi Gudang">
            @bind($model ?? null)
                <x-input col="6" name="gudang_nama" label="Nama Gudang" />
                <x-input col="6" name="gudang_kode" label="Kode" placeholder="Opsional" />
                <x-textarea col="12" name="gudang_alamat" label="Alamat" />
                <x-select col="6" name="is_active" label="Status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
                <x-input col="6" name="sort_order" label="Urutan" type="number" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
