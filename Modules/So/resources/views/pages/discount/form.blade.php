<?php /** @var Modules\So\Models\SoDiscount $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Diskon'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)
                <x-input col="4" name="discount_code" label="Kode Diskon" placeholder="HEMAT50K" />
                <x-input col="8" name="discount_nama" label="Nama Promo" placeholder="Promo Hemat Akhir Bulan" />

                <x-select col="4" name="discount_type" label="Tipe Diskon"
                    :options="['percent' => 'Persen (%)', 'nominal' => 'Nominal (Rp)']" />
                <x-input col="4" name="discount_value" type="number" step="1" label="Nilai"
                    helper="Persen: isi 5 = potongan 5%. Nominal: isi 5000 = potongan Rp5.000" />
                <x-input col="4" name="discount_min_purchase" type="number" step="1" label="Min. Transaksi (Rp)"
                    helper="Kosongkan / 0 jika tanpa syarat minimal" />

                <x-checkbox col="12" name="is_active" label="Aktif" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
