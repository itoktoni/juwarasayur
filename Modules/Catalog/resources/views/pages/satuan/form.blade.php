<?php /** @var Modules\Catalog\Models\Satuan $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Satuan'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Satuan / Unit">
            @bind($model ?? null)
                <x-input col="6" name="satuan_nama" label="Nama Satuan" />
                <x-input col="6" name="satuan_kode" label="Kode" placeholder="cth: PCS, KG, LTR" />
                <x-input col="6" name="satuan_simbol" label="Simbol" placeholder="cth: pcs, kg" />
                <x-input col="6" name="sort_order" label="Urutan" type="number" />
                <x-textarea col="6" name="satuan_deskripsi" label="Deskripsi" />
                <x-select col="6" name="is_active" label="Status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
