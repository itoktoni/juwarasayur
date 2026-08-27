<?php /** @var Modules\Faq\Models\Faq $table */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('dashboard'), 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
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

        {{-- Table --}}
        @php
            $currentSort = request('sort.0', '');
            $sortField = str_replace(':desc','',str_replace(':asc','',$currentSort));
            $sortDir = str_contains($currentSort, ':desc') ? 'desc' : 'asc';
        @endphp

        <x-table>
            <x-slot:head>
                <x-table-checkbox :model="$model" onchange="toggleAll(this)" />
                <th>Actions</th>
                <th>Pertanyaan</th>
                <th>Jawaban</th>
                <th class="text-center">Status</th>
            </x-slot:head>

            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                    <x-table-action :model="$model" :id="$table->field_primary" />
                    <td>{{ \Illuminate\Support\Str::limit($table->question, 80) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit(strip_tags($table->answer), 100) }}</td>
                    <td class="text-center">
                        <span class="badge {{ $table->is_active ? 'badge-success' : 'badge-secondary' }}">
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
                        <p class="text-sm font-bold text-on-surface mb-1">{{ \Illuminate\Support\Str::limit($table->question, 90) }}</p>
                        <p class="text-xs text-on-surface-variant mb-2">{{ \Illuminate\Support\Str::limit(strip_tags($table->answer), 120) }}</p>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="badge {{ $table->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $table->is_active ? 'Aktif' : 'Nonaktif' }}</span>
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
    <script>initTable('{{ $sortField }}', '{{ $sortDir }}');</script>
</x-layouts::app>
