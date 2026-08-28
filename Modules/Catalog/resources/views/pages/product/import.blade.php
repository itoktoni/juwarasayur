<?php /** @var Modules\Catalog\Models\Product $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Produk'], ['url' => '', 'label' => 'Import CSV']]" />

    <x-card label="Import Produk dari CSV">
        <div class="p-6">
            <div class="mb-6">
                <p class="text-sm text-on-surface-variant mb-2">
                    Upload file CSV untuk menambah atau memperbarui data produk.
                    Produk baru akan ditambahkan, produk yang sudah ada (berdasarkan kode/nama) akan diupdate harganya.
                </p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h4 class="text-sm font-semibold text-blue-800 mb-2">Format CSV:</h4>
                    <code class="text-xs text-blue-700">Nama Produk,Kode Produk,Harga Jual,Harga Modal,Fee Reseller (%),Fee Affiliator (%)</code>
                    <p class="text-xs text-blue-600 mt-2">
                        Kolom wajib: <strong>Nama Produk</strong>, <strong>Harga Jual</strong>. Kolom opsional: Kode Produk, Harga Modal, Fee Reseller, Fee Affiliator.
                    </p>
                </div>

                <a href="{{ route('catalog-product.export') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download Template CSV
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            @if(session('import_errors'))
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <h4 class="text-sm font-semibold text-yellow-800 mb-2">Errors:</h4>
                    <ul class="list-disc list-inside text-xs text-yellow-700">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('catalog-product.import.post') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <x-file name="file" label="File CSV" accept=".csv,.txt" helper="Format: .csv atau .txt, maks 2MB" />
            </form>
        </div>
    </x-card>
</x-layouts::app>
