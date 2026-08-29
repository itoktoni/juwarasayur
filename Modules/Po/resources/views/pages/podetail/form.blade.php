<?php /** @var Modules\Po\Models\PoDetail $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'PO Detail'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />
    <x-form :model="$model">
        <x-card label="Detail PO">
            @bind($model ?? null)
                <x-input col="6" name="po_detail_id_po" label="PO ID" type="number" />
                <x-input col="6" name="po_detail_id_product" label="Product ID" type="number" />
                <x-input col="4" name="po_detail_qty" label="Qty" type="number" />
                <x-input col="4" name="po_detail_harga" label="Harga" type="number" step="1" />
                <x-input col="4" name="po_detail_code" label="Kode Detail" placeholder="Auto jika kosong" />
                <x-textarea col="12" name="po_detail_keterangan" label="Keterangan" />
            @endbind
        </x-card>
        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
