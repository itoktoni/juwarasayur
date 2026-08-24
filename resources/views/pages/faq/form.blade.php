<?php /** @var Modules\Faq\Models\Faq $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'FAQ'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)
                <x-textarea col="12" name="question" label="Pertanyaan" rows="2"
                    helper="Pertanyaan yang sering diajukan customer, misal: Jam berapa toko buka?" />
                <x-textarea col="12" name="answer" label="Jawaban" rows="6"
                    helper="Jawaban ini akan dipakai AI chatbot sebagai bahan menjawab customer." />
                <x-toggle col="12" name="is_active" label="Aktif (dipakai oleh chatbot)" />
            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
