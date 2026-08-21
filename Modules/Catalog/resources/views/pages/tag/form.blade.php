<?php /** @var Modules\Catalog\Models\Tag $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Tags'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Tag Produk">
            @bind($model ?? null)
                <x-input col="6" name="tag_nama" label="Nama Tag" />
                <x-input col="6" name="tag_slug" label="Slug" placeholder="Auto generate jika kosong" />
                <x-input col="6" name="tag_warna" label="Warna" placeholder="#3b82f6" type="color" />
                <x-input col="6" name="sort_order" label="Urutan" type="number" />
                <x-select col="6" name="is_active" label="Status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
