<?php
use Modules\Catalog\Models\Product;

/** @var Product $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Produk'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model" enctype="multipart/form-data">
        <x-card label="Informasi Produk">
            @bind($model ?? null)
                <x-input col="6" name="product_nama" label="Nama Produk *" required />
                <x-input col="6" name="product_slug" label="Slug" placeholder="Auto generate jika kosong" />
                <x-input col="4" name="product_kode" label="Kode Produk" placeholder="Auto generate jika kosong" />
                <x-input col="4" name="product_sku" label="SKU" />
                <x-input col="4" name="product_barcode" label="Barcode" />

                <x-select col="4" name="product_id_product_master" label="Product Master" :options="$masterOptions" class="search" />
                <x-select col="4" name="product_id_category" label="Kategori" :options="$categoryOptions" />
                <x-select col="4" name="product_id_brand" label="Brand" :options="$brandOptions" />
                <x-select col="4" name="product_id_satuan" label="Satuan" :options="$satuanOptions" />

                <x-textarea col="12" name="product_deskripsi" label="Deskripsi Singkat" />
                <x-textarea col="12" name="product_deskripsi_lengkap" label="Deskripsi Lengkap" rows="8" class="cms-wysiwyg" data-wysiwyg="1" />
            @endbind
        </x-card>

        <x-card label="Harga & Stok" class="mt-5">
            @bind($model ?? null)
                <x-input col="4" name="product_harga" label="Harga Jual *" type="number" step="1" min="0" required />
                <x-input col="4" name="product_harga_modal" label="Harga Modal" type="number" step="1" />
                <x-input col="4" name="product_harga_grosir" label="Harga Grosir" type="number" step="1" />
                <x-input col="6" name="reseller_fee_percent" label="Diskon Reseller (%)" type="number" step="1" min="0" max="100" helper="Harga reseller = harga - diskon. 10% → Rp100.000 jadi Rp90.000. 0 = tanpa diskon." />
                <x-input col="6" name="affiliator_fee_percent" label="Komisi Affiliator (%)" type="number" step="1" min="0" max="100" helper="Komisi per baris order, cair via Withdraw. Kosong = fallback ke fee user/config global." />
                <x-input col="4" name="product_stok" label="Stok" type="number" />
                <x-input col="4" name="product_stok_minimum" label="Stok Minimum" type="number" />
                <x-select col="4" name="product_status" label="Status *" :options="['active' => 'Active', 'inactive' => 'Inactive', 'draft' => 'Draft', 'archived' => 'Archived']" :placeholder="false" required />
            @endbind
        </x-card>

        <x-card label="Dimensi & Berat" class="mt-5">
            @bind($model ?? null)
                <x-input col="3" name="product_berat" label="Berat (kg)" type="number" step="1" />
                <x-input col="3" name="product_panjang" label="Panjang (cm)" type="number" step="1" />
                <x-input col="3" name="product_lebar" label="Lebar (cm)" type="number" step="1" />
                <x-input col="3" name="product_tinggi" label="Tinggi (cm)" type="number" step="1" />
            @endbind
        </x-card>

        <x-card label="Media & Lainnya" class="mt-5">
            @bind($model ?? null)
                <x-file col="12" name="product_gambar" label="Gambar Utama" accept="image/*" :preview="true" :value="$model?->product_gambar_url" helper="Upload gambar utama produk" />
                <x-select col="4" name="is_featured" label="Featured" :options="['0' => 'Tidak', '1' => 'Ya']" />
                <x-select col="4" name="is_active" label="Aktif" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
                <x-input col="4" name="sort_order" label="Urutan" type="number" />
                <x-select col="12" name="tag_ids" label="Tags" :options="$tagOptions" :multiple="true" class="search"
                    :default="$model?->exists ? $model->has_tags->pluck('id')->toArray() : null" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
