<?php /** @var Modules\Chatbot\Models\ChatbotSession $table */ ?>

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
            $channelColors = ['whatsapp' => 'badge-success', 'telegram' => 'badge-primary', 'web' => 'badge-info'];
        @endphp

        <x-table>
            <x-slot:head>
                <th>Actions</th>
                <th>Channel</th>
                <th>Kontak</th>
                <th>No. HP / ID</th>
                <th>State</th>
                <th class="text-center">Item Keranjang</th>
                <th>Terakhir Aktif</th>
            </x-slot:head>

            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <td>
                        <a href="{{ route('chatbot.getShow', ['id' => $table->field_primary]) }}"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors"
                            title="Lihat riwayat percakapan">
                            <span class="material-symbols-outlined text-lg">forum</span>
                        </a>
                    </td>
                    <td>
                        <span class="badge {{ $channelColors[$table->channel] ?? 'badge-secondary' }}">{{ ucfirst($table->channel) }}</span>
                    </td>
                    <td>{{ $table->contact_name ?? '-' }}</td>
                    <td>{{ $table->contact_phone ?? \Illuminate\Support\Str::limit($table->messenger_user, 24) }}</td>
                    <td>{{ $table->state ?? '-' }}</td>
                    <td class="text-center font-mono">{{ is_array($table->cart) ? count($table->cart) : 0 }}</td>
                    <td>{{ $table->last_active_at?->diffForHumans() }}</td>
                </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <a href="{{ route('chatbot.getShow', ['id' => $table->field_primary]) }}" class="block border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-bold text-on-surface truncate">{{ $table->contact_name ?? \Illuminate\Support\Str::limit($table->messenger_user, 20) }}</p>
                            <span class="badge {{ $channelColors[$table->channel] ?? 'badge-secondary' }}">{{ ucfirst($table->channel) }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div><p class="text-[10px] text-on-surface-variant uppercase">State</p><p class="text-xs">{{ $table->state ?? '-' }}</p></div>
                            <div><p class="text-[10px] text-on-surface-variant uppercase">Keranjang</p><p class="text-xs font-mono">{{ is_array($table->cart) ? count($table->cart) : 0 }}</p></div>
                            <div><p class="text-[10px] text-on-surface-variant uppercase">Aktif</p><p class="text-xs">{{ $table->last_active_at?->diffForHumans() }}</p></div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </x-slot:mobile>

        </x-table>

        <x-pagination :paginator="$data" />

    </div>

    <input type="hidden" class="module" value="{{ Str::beforeLast(request()->route()->uri(), '/') }}">
    <script src="/js/table.js"></script>
    <script>initTable('{{ $sortField }}', '{{ $sortDir }}');</script>
</x-layouts::app>
