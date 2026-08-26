<?php /** @var Modules\So\Models\Consignment $table */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Titip Jual']]" />
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
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Reseller</th>
                <th class="text-right">Qty Titip</th>
                <th class="text-right">Terjual</th>
                <th>Status</th>
                <th class="text-right">Nilai Tarikan</th>
            </x-slot:head>

            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                    <x-table-action :model="$model" :id="$table->field_primary">
                        @if($table->status === \Modules\So\Enums\ConsignmentStatusEnum::OPEN)
                            <a href="{{ route('so-consignment.getSettle', ['id' => $table->field_primary]) }}" wire:navigate title="Tarik Uang"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                                <span class="material-symbols-outlined text-lg">payments</span>
                            </a>
                        @endif
                    </x-table-action>
                    <td class="font-mono text-xs font-bold">{{ $table->code }}</td>
                    <td>{{ formatDate($table->consignment_date) }}</td>
                    <td>{{ $table->has_reseller?->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format((float) $table->total_qty, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format((float) $table->total_sold, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge badge-soft {{ $table->status === \Modules\So\Enums\ConsignmentStatusEnum::SETTLED ? 'text-success' : 'text-warning' }}">
                            {{ \Modules\So\Enums\ConsignmentStatusEnum::getDescription($table->status) }}
                        </span>
                    </td>
                    <td class="text-right font-mono font-bold">{{ formatAngka((float) $table->total_amount, 'Rp') }}</td>
                </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-2">{{ $table->code }} — {{ $table->has_reseller?->name ?? '-' }}</p>
                        <div class="grid grid-cols-2 gap-2 mb-2 text-xs">
                            <div><span class="text-on-surface-variant">Tgl:</span> {{ formatDate($table->consignment_date) }}</div>
                            <div><span class="text-on-surface-variant">Titip:</span> {{ number_format((float) $table->total_qty, 0, ',', '.') }}</div>
                            <div><span class="text-on-surface-variant">Terjual:</span> {{ number_format((float) $table->total_sold, 0, ',', '.') }}</div>
                            <div class="font-bold text-primary">{{ formatAngka((float) $table->total_amount, 'Rp') }}</div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="badge badge-soft {{ $table->status === \Modules\So\Enums\ConsignmentStatusEnum::SETTLED ? 'text-success' : 'text-warning' }}">{{ ucfirst($table->status) }}</span>
                            <div class="flex gap-1" onclick="event.stopPropagation()">
                                @if($table->status === \Modules\So\Enums\ConsignmentStatusEnum::OPEN)
                                    <a href="{{ route('so-consignment.getSettle', ['id' => $table->field_primary]) }}" wire:navigate
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors" title="Tarik Uang">
                                        <span class="material-symbols-outlined text-lg">payments</span>
                                    </a>
                                @endif
                                <x-table-action :model="$model" :id="$table->field_primary" />
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-slot:mobile>

        </x-table>

        <x-pagination :paginator="$data" />
    </div>

    <input type="hidden" class="module" value="so/consignment">
    <script src="/js/table.js"></script>
    <script>initTable('{{ $sortField }}', '{{ $sortDir }}');</script>
</x-layouts::app>
