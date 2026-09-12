<?php /** @var App\Models\User $table */ ?>

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
                <th>Nama</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Referral</th>
                <th class="text-center">Jumlah Customer</th>
            </x-slot:head>

            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                    <x-table-action :model="$model" :id="$table->field_primary">
                        <a href="{{ url('/admin/so/customer/table') }}?filter[reference_id]={{ $table->id }}"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors"
                            title="Lihat customer milik affiliator ini">
                            <span class="material-symbols-outlined text-lg">group</span>
                        </a>
                        @if($table->referral_code)
                        <button type="button" onclick="navigator.clipboard.writeText('{{ url('/r/'.$table->referral_code) }}'); alert('Link referral disalin: {{ url('/r/'.$table->referral_code) }}')"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500/10 text-amber-700 hover:bg-amber-500/20 transition-colors"
                            title="Salin link referral">
                            <span class="material-symbols-outlined text-lg">link</span>
                        </button>
                        @endif
                    </x-table-action>
                    <td>{{ $table->name }}</td>
                    <td>{{ $table->email }}</td>
                    <td>{{ $table->phone ?? '-' }}</td>
                    <td class="font-mono text-xs">
                        @if($table->referral_code)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-amber-50 border border-amber-200 text-amber-800">{{ $table->referral_code }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center font-mono">{{ $table->hasCustomers()->count() }}</td>
                </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->name }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Email</p>
                                <p class="text-xs font-medium text-primary truncate">{{ $table->email }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Phone</p>
                                <p class="text-xs font-medium text-on-surface">{{ $table->phone ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Referral</p>
                                <p class="text-xs font-mono font-medium text-amber-700">{{ $table->referral_code ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Jumlah Customer</p>
                                <p class="text-xs font-mono font-medium text-on-surface">{{ $table->hasCustomers()->count() }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="text-[9px] font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">{{ $table->field_primary }}</span>
                            <div class="flex gap-1" onclick="event.stopPropagation()">
                                <a href="{{ url('/admin/so/customer/table') }}?filter[reference_id]={{ $table->id }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20" title="Lihat customer">
                                    <span class="material-symbols-outlined text-base">group</span>
                                </a>
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
