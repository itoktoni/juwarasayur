<?php /** @var Modules\Po\Models\Po $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('po-po.getTable'), 'label' => 'Purchase Order'], ['url' => '', 'label' => 'Import Excel']]" />

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
                Download template CSV (bisa dibuka di Excel), isi data PO, lalu upload kembali.
                Satu baris = satu produk. Baris dengan <b>PO Ref + Supplier + Tanggal sama</b> digabung jadi 1 PO.
            </p>
            <a href="{{ route('po-po.template') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold">
                <span class="material-symbols-outlined text-xl">download</span>
                Download Template
            </a>
        </x-card>

        <x-card label="Upload & Import" icon="upload" :noGrid="true" class="mt-5">
            <p class="text-sm text-on-surface-variant mb-3">
                Upload file CSV. Kolom: <code class="bg-surface-container px-1 py-0.5 rounded">PO Ref;Tanggal;Supplier Kode;Produk Kode;Qty;Harga;Keterangan PO</code>
                (delimiter <code>;</code>). Harga kosong = pakai harga modal produk.
            </p>
            <p class="text-xs text-on-surface-variant mb-4">
                Contoh: 2 baris <code>PO-1</code> dengan supplier sama = 1 PO isi 2 produk.
                Baris tanpa PO Ref = masing-masing jadi 1 PO sendiri.
            </p>
            <form action="{{ route('po-po.import.post') }}" method="POST" enctype="multipart/form-data">
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
