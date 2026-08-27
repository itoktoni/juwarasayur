<?php /** @var App\Models\Withdrawal $table */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('dashboard'), 'label' => 'Home'], ['url' => '', 'label' => 'Pencairan Komisi']]" />
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
                <th>Actions</th>
                <th>Kode</th>
                <th>Reseller</th>
                <th>Jumlah</th>
                <th>Rekening</th>
                <th>Tgl Ajukan</th>
                <th>Status</th>
            </x-slot:head>
            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-action :model="$model" :id="$table->field_primary" />
                    <td class="font-mono text-xs font-bold">#{{ $table->id }}</td>
                    <td>{{ $table->has_user?->name ?? '-' }}</td>
                    <td class="font-mono">{{ formatAngka((float) $table->amount, 'Rp') }}</td>
                    <td class="text-xs">{{ $table->bank_name }} • {{ $table->bank_account_no }} a.n. {{ $table->bank_account_name }}</td>
                    <td>{{ $table->created_at->format('d/m/Y') }}</td>
                    <td><span class="badge badge-soft {{ $table->status === 'paid' ? 'text-success' : ($table->status === 'rejected' ? 'text-error' : 'text-warning') }}">{{ ucfirst($table->status) }}</span></td>
                </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-2">#{{ $table->id }} — {{ $table->has_user?->name ?? '-' }}</p>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono font-bold text-primary">{{ formatAngka((float) $table->amount, 'Rp') }}</span>
                            <span class="badge badge-soft text-xs">{{ ucfirst($table->status) }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="text-[10px] text-on-surface-variant">{{ $table->created_at->format('d/m/Y') }}</span>
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
    </div>

    <input type="hidden" class="module" value="withdrawal">
    <script src="/js/table.js"></script>
    <script>initTable('{{ $sortField }}', '{{ $sortDir }}');</script>
</x-layouts::app>
