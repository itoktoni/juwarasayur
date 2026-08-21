<?php /** @var Modules\Catalog\Models\Product $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Produk'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model" enctype="multipart/form-data">
        <x-card label="Informasi Produk">
            @bind($model ?? null)
                <x-input col="6" name="product_nama" label="Nama Produk" />
                <x-input col="6" name="product_slug" label="Slug" placeholder="Auto generate jika kosong" />
                <x-input col="4" name="product_kode" label="Kode Produk" placeholder="Auto generate jika kosong" />
                <x-input col="4" name="product_sku" label="SKU" />
                <x-input col="4" name="product_barcode" label="Barcode" />

                <x-select col="4" name="product_id_category" label="Kategori" :options="$categoryOptions" />
                <x-select col="4" name="product_id_brand" label="Brand" :options="$brandOptions" />
                <x-select col="4" name="product_id_satuan" label="Satuan" :options="$satuanOptions" />

                <x-textarea col="12" name="product_deskripsi" label="Deskripsi Singkat" />
                <x-textarea col="12" name="product_deskripsi_lengkap" label="Deskripsi Lengkap" />
            @endbind
        </x-card>

        <x-card label="Harga & Stok" class="mt-5">
            @bind($model ?? null)
                <x-input col="4" name="product_harga" label="Harga Jual" type="number" step="0.01" />
                <x-input col="4" name="product_harga_modal" label="Harga Modal" type="number" step="0.01" />
                <x-input col="4" name="product_harga_grosir" label="Harga Grosir" type="number" step="0.01" />
                <x-input col="4" name="product_stok" label="Stok" type="number" />
                <x-input col="4" name="product_stok_minimum" label="Stok Minimum" type="number" />
                <x-select col="4" name="product_status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'draft' => 'Draft', 'archived' => 'Archived']" />
            @endbind
        </x-card>

        <x-card label="Dimensi & Berat" class="mt-5">
            @bind($model ?? null)
                <x-input col="3" name="product_berat" label="Berat (kg)" type="number" step="0.01" />
                <x-input col="3" name="product_panjang" label="Panjang (cm)" type="number" step="0.01" />
                <x-input col="3" name="product_lebar" label="Lebar (cm)" type="number" step="0.01" />
                <x-input col="3" name="product_tinggi" label="Tinggi (cm)" type="number" step="0.01" />
            @endbind
        </x-card>

        <x-card label="Media & Lainnya" class="mt-5">
            @bind($model ?? null)
                <x-file col="12" name="product_gambar" label="Gambar Utama" accept="image/*" :preview="true" :value="$model?->product_gambar_url" helper="Upload gambar utama produk" />
                <x-select col="4" name="is_featured" label="Featured" :options="['0' => 'Tidak', '1' => 'Ya']" />
                <x-select col="4" name="is_active" label="Aktif" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
                <x-input col="4" name="sort_order" label="Urutan" type="number" />
                <x-select col="12" name="tag_ids" label="Tags" :options="$tagOptions" :multiple="true" class="search" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
