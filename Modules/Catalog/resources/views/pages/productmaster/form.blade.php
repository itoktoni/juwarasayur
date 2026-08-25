<?php /** @var Modules\Catalog\Models\ProductMaster $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Product Master'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Product Master">
            @bind($model ?? null)
                <x-input col="6" name="product_master_nama" label="Nama Master" placeholder="cth: Wortel" />
                <x-input col="6" name="product_master_slug" label="Slug" placeholder="Auto generate jika kosong" />
                <x-textarea col="6" name="product_master_deskripsi" label="Deskripsi" />
                <x-input col="3" name="sort_order" label="Urutan" type="number" />
                <x-select col="3" name="is_active" label="Status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
                <x-select col="6" name="supplier_ids" label="Suppliers" :options="$supplierOptions" :multiple="true" class="search"
                    :default="$model?->exists ? $model->supplier_ids : null"
                    helper="Supplier yang menyediakan master ini" />
                <x-select col="6" name="recommended_supplier_id" label="Supplier Rekomendasi" :options="$supplierOptions" class="search"
                    :default="$model?->exists ? $model->recommended_supplier_id : null"
                    helper="Dipakai otomatis saat generate PO dari SO" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
