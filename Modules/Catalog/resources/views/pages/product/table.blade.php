<?php /** @var Modules\Catalog\Models\Product $table */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Produk']]" />
    <div class="content mt-4 lg:mt-0">
        <x-filter :per-page="25" :fields="$fields">
            <x-slot:advanced>
                @foreach ($fields as $key => $advance)
                <x-filter-item :label="$advance" :name="$key"/>
                @endforeach
                <x-button variant="primary" class="btn-block" onclick="applyAdvanced()">Apply</x-button>
                <x-button variant="soft" class="btn-block" onclick="resetAdvanced()">Reset</x-button>
            </x-slot:advanced>
        </x-filter>

        @php
            $currentSort = request('sort.0', '');
            $sortField = str_replace(':desc','',str_replace(':asc','',$currentSort));
            $sortDir = str_contains($currentSort, ':desc') ? 'desc' : 'asc';
        @endphp

        <x-table>
            <x-slot:head>
                <x-table-checkbox :model="$model" onchange="toggleAll(this)" />
                <th>Actions</th>
                <th>Gambar</th>
                <th>Nama</th>
                <th>Kode</th>
                <th>Harga</th>
                <th>Diskon Reseller</th>
                <th>Komisi Affiliator</th>
                <th>Stok</th>
                <th>Status</th>
            </x-slot:head>
            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                    <x-table-action :model="$model" :id="$table->field_primary" />
                    <td>
                        @if($table->product_gambar)
                            <img src="{{ $table->product_gambar_url }}" alt="{{ $table->product_nama }}" class="w-10 h-10 rounded object-cover" />
                        @else
                            <span class="text-on-surface-variant text-xs">-</span>
                        @endif
                    </td>
                    <td>{{ $table->product_nama }}</td>
                    <td>{{ $table->product_kode ?? '-' }}</td>
                    <td>{{ formatAngka((int) $table->product_harga, 'Rp ') }}</td>
                    <td>{{ $table->reseller_fee_percent ? $table->reseller_fee_percent . '%' : '-' }}</td>
                    <td>{{ $table->affiliator_fee_percent ? $table->affiliator_fee_percent . '%' : '-' }}</td>
                    <td>{{ $table->product_stok }}</td>
                    <td><x-badge :label="ucfirst($table->product_status)" :variant="$table->product_status === 'active' ? 'success' : 'soft'" /></td>
                </tr>
                @endforeach
            </x-slot:body>
            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <x-table-mobile-list>
                    @foreach($data as $table)
                    <x-table-mobile-item :id="$table->field_primary">
                        <x-table-mobile-header :title="$table->product_nama" />
                        <div class="mt-2 space-y-1.5">
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Kode</span>
                                <span class="text-sm font-medium text-right">{{ $table->product_kode ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Harga</span>
                                <span class="text-sm font-medium text-right">{{ formatAngka((int) $table->product_harga, 'Rp ') }}</span>
                            </div>
                            @if($table->reseller_fee_percent)
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Diskon Reseller</span>
                                <span class="text-sm font-medium text-right">{{ $table->reseller_fee_percent }}%</span>
                            </div>
                            @endif
                            @if($table->affiliator_fee_percent)
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Komisi Affiliator</span>
                                <span class="text-sm font-medium text-right">{{ $table->affiliator_fee_percent }}%</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Stok</span>
                                <span class="text-sm font-medium text-right">{{ $table->product_stok }}</span>
                            </div>
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Status</span>
                                <span class="text-sm font-medium text-right">{{ ucfirst($table->product_status) }}</span>
                            </div>
                        </div>
                        <x-table-mobile-footer :label="'#' . $table->field_primary">
                            <x-table-action :model="$model" :id="$table->field_primary" />
                        </x-table-mobile-footer>
                    </x-table-mobile-item>
                    @endforeach
                </x-table-mobile-list>
            </x-slot:mobile>
        </x-table>

        <x-pagination :paginator="$data" />
        <x-action :model="$model" :action="['create', 'delete']">
            <a href="{{ route('catalog-product.export') }}" class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-green-600 text-white hover:bg-green-700 shadow-sm transition-all active:scale-95 shrink-0">
                <span class="material-symbols-outlined text-base md:text-xl">download</span>
                <span class="hidden sm:inline">Download CSV</span>
            </a>
            <a href="{{ route('catalog-product.import') }}" wire:navigate class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-all active:scale-95 shrink-0">
                <span class="material-symbols-outlined text-base md:text-xl">upload</span>
                <span class="hidden sm:inline">Import CSV</span>
            </a>
        </x-action>
    </div>

    <input type="hidden" class="module" value="{{ modules() }}">
    <script src="/js/table.js?v=4"></script>
    <script>initTable('{{ $sortField }}', '{{ $sortDir }}');</script>
</x-layouts::app>

