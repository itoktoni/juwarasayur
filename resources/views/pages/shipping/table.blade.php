<?php /** @var Modules\Ecommerce\Models\CodLocation $table */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('dashboard'), 'label' => 'Home'], ['url' => '', 'label' => 'Pengiriman & COD']]" />
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
                <th>Nama Lokasi</th>
                <th>Alamat</th>
                <th>Ongkir</th>
                <th>Status</th>
            </x-slot:head>
            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                    <x-table-action :model="$model" :id="$table->field_primary" />
                    <td>{{ $table->location_name }}</td>
                    <td>{{ $table->address ?? '-' }}</td>
                    <td>{{ $table->fee !== null ? formatAngka((float) $table->fee, 'Rp') : 'Hitung dari jarak' }}</td>
                    <td><x-badge :type="$table->is_active ? 'success' : 'default'">{{ $table->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge></td>
                </tr>
                @endforeach
            </x-slot:body>
            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <x-table-mobile-list>
                    @foreach($data as $table)
                    <x-table-mobile-item :id="$table->field_primary">
                        <x-table-mobile-header :title="$table->location_name" />
                        <div class="mt-2 space-y-1.5">
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Alamat</span>
                                <span class="text-sm font-medium text-right">{{ $table->address ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center gap-2">
                                <span class="text-xs text-on-surface-variant">Ongkir</span>
                                <span class="text-sm font-medium text-right">{{ $table->fee !== null ? formatAngka((float) $table->fee, 'Rp') : 'Hitung dari jarak' }}</span>
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
    <script src="/js/table.js?v=4"></script>
    <script>initTable('{{ $sortField }}', '{{ $sortDir }}');</script>
</x-layouts::app>

