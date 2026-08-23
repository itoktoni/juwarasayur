<?php /** @var Modules\Production\Models\Production $table */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Produksi Rutin']]" />
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
                <th>Kode WO</th>
                <th>Produk Hasil</th>
                <th>Qty Output</th>
                <th>Bahan</th>
                <th>Status</th>
            </x-slot:head>
            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                    <x-table-action :model="$model" :id="$table->field_primary" />
                    <td>{{ $table->production_code }}</td>
                    <td>{{ $table->has_product?->product_nama ?? '-' }}</td>
                    <td>{{ formatQty($table->production_qty_output) }}</td>
                    <td>{{ $table->has_items->count() }} item</td>
                    <td>
                        @php
                            $badgeType = match ($table->production_status) {
                                \Modules\Production\Enums\ProductionStatusEnum::COMPLETED => 'success',
                                \Modules\Production\Enums\ProductionStatusEnum::CANCELLED => 'error',
                                default => 'default',
                            };
                        @endphp
                        <x-badge :type="$badgeType">{{ \Modules\Production\Enums\ProductionStatusEnum::getDescription($table->production_status) }}</x-badge>
                    </td>
                </tr>
                @endforeach
            </x-slot:body>
            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <x-table-mobile-list>
                    @foreach($data as $table)
                    <x-table-mobile-item :id="$table->field_primary">
                        <x-table-mobile-header :title="$table->production_code" />
                        <div class="mt-2 space-y-1.5">
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Produk</span>
                                <span class="text-sm font-medium text-right">{{ $table->has_product?->product_nama ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Status</span>
                                <span class="text-sm font-medium text-right">{{ \Modules\Production\Enums\ProductionStatusEnum::getDescription($table->production_status) }}</span>
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
        <x-action :model="$model" :action="['create', 'delete']"/>
    </div>

    <input type="hidden" class="module" value="{{ modules() }}">
    <script src="/js/table.js?v=3"></script>
    <script>initTable('{{ $sortField }}', '{{ $sortDir }}');</script>
</x-layouts::app>
