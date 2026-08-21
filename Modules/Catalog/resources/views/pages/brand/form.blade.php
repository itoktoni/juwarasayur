<?php /** @var Modules\Catalog\Models\Brand $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Brands'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Brand">
            @bind($model ?? null)
                <x-input col="6" name="brand_nama" label="Nama Brand" />
                <x-input col="6" name="brand_slug" label="Slug" placeholder="Auto generate jika kosong" />
                <x-textarea col="6" name="brand_deskripsi" label="Deskripsi" />
                <x-input col="6" name="sort_order" label="Urutan" type="number" />
                <x-select col="6" name="is_active" label="Status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
