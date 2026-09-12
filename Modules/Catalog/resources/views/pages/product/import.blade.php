<?php /** @var Modules\Catalog\Models\Product $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('catalog-product.getTable'), 'label' => 'Produk'], ['url' => '', 'label' => 'Import CSV']]" />

    <div class="content mt-4 lg:mt-0">
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

        <x-card label="Download Template" icon="download" :noGrid="true">
            <p class="text-sm text-on-surface-variant mb-4">
                Download template CSV, isi data produk, lalu upload kembali untuk update massal.
            </p>
            <a href="{{ route('catalog-product.export') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                <span class="material-symbols-outlined text-xl">download</span>
                Download CSV
            </a>
        </x-card>

        <x-card label="Upload & Import" icon="upload" :noGrid="true" class="mt-5">
            <p class="text-sm text-on-surface-variant mb-3">
                Upload file CSV. <b>Jika Kode Produk sudah ada di DB maka di-update</b>, jika belum maka di-create. Tambahkan kolom <code class="bg-surface-container px-1 py-0.5 rounded">Flag</code> opsional isi <code>update</code>/<code>create</code>/<code>delete</code> untuk paksa aksi (delete = hapus soft-delete).
            </p>
            <p class="text-xs text-on-surface-variant mb-2">
                Format: <code class="bg-surface-container px-1 py-0.5 rounded">Nama Produk;Kode Produk;Harga Jual;Harga Modal;Stok;Fee Reseller (%);Fee Affilator (%);Sort Order;Flag</code> (delimiter <code>;</code> sesuai <code>config/website.php</code>)
            </p>
            <p class="text-xs text-on-surface-variant mb-4">
                Contoh Flag: <code>delete</code> di baris <code>PRD-0200</code> akan hapus produk tersebut. Kosong = upsert otomatis. File contoh: <code>docs/produk_2026-09-12_131315.csv</code>
            </p>
            <form action="{{ route('catalog-product.import.post') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" accept=".csv,.txt"
                       class="block w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-on-primary hover:file:bg-primary/90 file:cursor-pointer">
                @error('file') <p class="text-xs text-error mt-1">{{ $message }}</p> @enderror
                <button type="submit" class="mt-4 inline-flex items-center justify-center gap-1 h-10 px-5 text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95">
                    <span class="material-symbols-outlined text-xl">upload</span>
                    Import Sekarang
                </button>
            </form>
        </x-card>
    </div>
</x-layouts::app>
