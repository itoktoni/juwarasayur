<?php /** @var Modules\Catalog\Models\Category $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Kategori Produk'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model" enctype="multipart/form-data">
        <x-card label="Kategori Produk">
            @bind($model ?? null)
                <x-input col="6" name="category_nama" label="Nama Kategori" />
                <x-input col="6" name="category_slug" label="Slug" placeholder="Auto generate jika kosong" />
                <x-textarea col="6" name="category_deskripsi" label="Deskripsi" />
                <x-input col="6" name="category_icon" label="Icon" placeholder="cth: category" />
                <x-input col="6" name="parent_id" label="Parent ID" type="number" placeholder="Kosong untuk top-level" />
                <x-input col="6" name="sort_order" label="Urutan" type="number" />
                <x-select col="6" name="is_active" label="Status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
                <x-file col="12" name="category_image" label="Gambar Kategori" accept="image/*" :preview="true" :value="$model?->category_image_url" helper="Upload gambar kategori" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
