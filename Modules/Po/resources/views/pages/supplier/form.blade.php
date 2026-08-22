<?php /** @var Modules\Po\Models\Supplier $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Supplier'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Informasi Supplier">
            @bind($model ?? null)
                <x-input col="6" name="supplier_nama" label="Nama Supplier" />
                <x-input col="6" name="supplier_kode" label="Kode" placeholder="Otomatis jika kosong" />
                <x-input col="6" name="supplier_telepon" label="Telepon" />
                <x-input col="6" name="supplier_email" label="Email" type="email" />
                <x-textarea col="12" name="supplier_alamat" label="Alamat" />
                <x-input col="6" name="supplier_kontak_person" label="Kontak Person" />
                <x-input col="6" name="supplier_npwp" label="NPWP" />
                <x-select col="6" name="is_active" label="Status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
                <x-input col="6" name="sort_order" label="Urutan" type="number" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
