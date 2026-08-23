<?php /** @var Modules\So\Models\SoDiscount $table */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />

    <div class="content mt-4 lg:mt-0">
        {{-- Filters --}}
        <x-filter :per-page="25" :fields="$fields">
            <x-slot:advanced>
                @foreach ($fields as $key => $advance)
                <x-filter-item :label="$advance" :name="$key"/>
                @endforeach

                <x-button variant="primary" class="btn-block" onclick="applyAdvanced()">Apply</x-button>
                <x-button variant="soft" class="btn-block" onclick="resetAdvanced()">Reset</x-button>
            </x-slot:advanced>
        </x-filter>

        <x-table>
            <x-slot:head>
                <x-table-checkbox :model="$model" onchange="toggleAll(this)" />
                <th>Actions</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tipe</th>
                <th>Nilai</th>
                <th>Min. Transaksi</th>
                <th>Status</th>
            </x-slot:head>

            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                    <x-table-action :model="$model" :id="$table->field_primary" />
                    <td class="font-mono font-semibold">{{ $table->discount_code }}</td>
                    <td>{{ $table->discount_nama }}</td>
                    <td>{{ $table->discount_type === 'percent' ? 'Persen' : 'Nominal' }}</td>
                    <td>{{ $table->discount_type === 'percent' ? rtrim(rtrim((string) $table->discount_value, '0'), '.'). '%' : formatAngka((float) $table->discount_value, 'Rp') }}</td>
                    <td>{{ (float) $table->discount_min_purchase > 0 ? formatAngka((float) $table->discount_min_purchase, 'Rp') : '-' }}</td>
                    <td>
                        <span class="badge {{ $table->is_active ? 'badge-soft text-success' : 'badge-soft text-error' }}">
                            {{ $table->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold font-mono text-on-surface truncate mb-1">{{ $table->discount_code }}</p>
                        <p class="text-xs text-on-surface-variant mb-3">{{ $table->discount_nama }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Nilai</p>
                                <p class="text-xs font-medium text-primary">{{ $table->discount_type === 'percent' ? rtrim(rtrim((string) $table->discount_value, '0'), '.').'%' : formatAngka((float) $table->discount_value, 'Rp') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Min. Transaksi</p>
                                <p class="text-xs font-medium text-on-surface">{{ (float) $table->discount_min_purchase > 0 ? formatAngka((float) $table->discount_min_purchase, 'Rp') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Status</p>
                                <p class="text-xs font-medium {{ $table->is_active ? 'text-success' : 'text-error' }}">{{ $table->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="text-[9px] font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">{{ $table->field_primary }}</span>
                            <div class="flex gap-1" onclick="event.stopPropagation()">
                                <x-table-action :model="$model" :id="$table->field_primary" />
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-slot:mobile>

        </x-table>

        <x-pagination :paginator="$data" />
        <x-action :model="$model" :action="['create', 'delete']"/>

    </div>

    <input type="hidden" class="module" value="{{ Str::beforeLast(request()->route()->uri(), '/') }}">
    <script src="/js/table.js"></script>
    <script>initTable('', '');</script>
</x-layouts::app>
