<?php /** @var Modules\So\Models\So $table */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('dashboard'), 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        {{-- Filters --}}
        <x-filter :per-page="25" :fields="$fields">
            <x-slot:advanced>
                <x-filter-item label="Affiliator" name="so_id_reseller" :options="$resellerOptions ?? []" />
                <x-filter-item label="Customer" name="so_id_customer" :options="$customerOptions ?? []" />
                <x-filter-item label="Tanggal Mulai" name="so_tanggal" type="date" operator="$gte" />
                <x-filter-item label="Tanggal Selesai" name="so_tanggal" type="date" operator="$lte" />
                <x-filter-item label="Pengiriman" name="so_shipping_method" :options="$shippingMethodOptions ?? []" />

                @foreach ($fields as $key => $advance)
                    @if(!in_array($key, ['so_id_reseller','so_id_customer','so_tanggal','so_shipping_method']))
                    <x-filter-item :label="$advance" :name="$key"/>
                    @endif
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

        {{-- Table dengan wrapper: di mobile stretch full-width tanpa padding/border --}}
        <div class="lg:bg-surface-container-lowest lg:border lg:border-outline-variant lg:rounded-xl lg:mt-5 -mx-4 md:mx-0 lg:form-card">
        <x-table :border="false">
            <x-slot:head>
                <x-table-checkbox :model="$model" onchange="toggleAll(this)" />
                <th>Actions</th>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Affiliator</th>
                <th>Customer</th>
                <th>Status</th>
                <th>Pengiriman</th>
                <th class="text-right">Grand Total</th>
            </x-slot:head>

            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                    <x-table-action :model="$model" :id="$table->field_primary">
                        <a href="{{ route('so-so.getPrintContinues', ['ids' => $table->field_primary]) }}" target="_blank" title="Print Struk 80mm"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-neutral-800/10 text-neutral-800 hover:bg-neutral-800/20 transition-colors">
                            <span class="material-symbols-outlined text-lg">print</span>
                        </a>
                        <a href="{{ route('so-so.getDeliveryOrder', ['ids' => $table->field_primary]) }}" target="_blank" title="Print Surat Jalan (Delivery Order A4)"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-sky-600/10 text-sky-700 hover:bg-sky-600/20 transition-colors">
                            <span class="material-symbols-outlined text-lg">local_shipping</span>
                        </a>
                        <a href="{{ route('so-so.getPayment', ['id' => $table->field_primary]) }}" title="Payment — QR & Link untuk customer"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-600/10 text-emerald-700 hover:bg-emerald-600/20 transition-colors">
                            <span class="material-symbols-outlined text-lg">qr_code_2</span>
                        </a>
                        @if(in_array($table->so_status, ['pending', 'paid', 'confirmed'], true))
                            <a href="{{ route('so-so.getPrepare', ['id' => $table->field_primary]) }}" title="Siapkan barang dari gudang"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                                <span class="material-symbols-outlined text-lg">inventory_2</span>
                            </a>
                        @endif
                    </x-table-action>
                    <td>{{ $table->so_code }}</td>
                    <td>{{ formatDate($table->so_tanggal) }}</td>
                    <td>{{ $table->has_reseller?->name ?? '-' }}</td>
                    <td>{{ $table->so_customer_name ?: ($table->has_customer?->name ?? '-') }}</td>
                    <td><span class="badge badge-soft">{{ \Modules\So\Enums\SoStatusEnum::getDescription($table->so_status) }}</span></td>
                    <td>{{ \Modules\So\Enums\ShippingMethodEnum::getDescription($table->so_shipping_method) }}{{ $table->so_cod_location ? ' ('.$table->so_cod_location.')' : '' }}</td>
                    <td class="text-right font-mono">{{ formatAngka((int) $table->so_grand_total, 'Rp') }}</td>
                </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->so_code }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Tanggal</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatDate($table->so_tanggal) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Customer</p>
                                <p class="text-xs font-medium text-primary truncate">{{ $table->so_customer_name ?: ($table->has_customer?->name ?? '-') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Status</p>
                                <p class="text-xs font-medium text-on-surface">{{ \Modules\So\Enums\SoStatusEnum::getDescription($table->so_status) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Total</p>
                                <p class="text-xs font-mono font-medium text-on-surface">{{ formatAngka((int) $table->so_grand_total, 'Rp') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="text-[9px] font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">{{ $table->field_primary }}</span>
                            <div class="flex gap-1" onclick="event.stopPropagation()">
                                <a href="{{ route('so-so.getPrintContinues', ['ids' => $table->field_primary]) }}" target="_blank" title="Print Struk 80mm"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-neutral-800/10 text-neutral-800 hover:bg-neutral-800/20 transition-colors">
                                    <span class="material-symbols-outlined text-lg">print</span>
                                </a>
                                <a href="{{ route('so-so.getDeliveryOrder', ['ids' => $table->field_primary]) }}" target="_blank" title="Print Surat Jalan (Delivery Order A4)"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-sky-600/10 text-sky-700 hover:bg-sky-600/20 transition-colors">
                                    <span class="material-symbols-outlined text-lg">local_shipping</span>
                                </a>
                                <a href="{{ route('so-so.getPayment', ['id' => $table->field_primary]) }}" title="Payment — QR & Link untuk customer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-600/10 text-emerald-700 hover:bg-emerald-600/20 transition-colors">
                                    <span class="material-symbols-outlined text-lg">qr_code_2</span>
                                </a>
                                @if(in_array($table->so_status, ['pending', 'paid', 'confirmed'], true))
                                    <a href="{{ route('so-so.getPrepare', ['id' => $table->field_primary]) }}" title="Siapkan barang dari gudang"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                                        <span class="material-symbols-outlined text-lg">inventory_2</span>
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
        <x-action :model="$model" :action="['create', 'delete']">
            <button type="button" onclick="prepareSelectedSo()" title="Prepare SO yang dicentang sekaligus"
                class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-all active:scale-95 shrink-0">
                <span class="material-symbols-outlined text-base md:text-xl">done_all</span>
                <span class="hidden sm:inline">Prepare All</span>
            </button>
        </x-action>
    <script>
        // ponytail: kumpulkan checkbox tercentang (desktop) atau set mobile,
        // lalu buka prepare.group dengan so_ids[] — pola mengikuti deleteSelected.
        function prepareSelectedSo(){
            var desktopIds = Array.from(document.querySelectorAll('tbody input[type="checkbox"]:checked')).map(function(c){ return c.value; });
            var ids = desktopIds.length ? desktopIds : Array.from(window.mSelected || []);
            if(!ids.length) return alert('Pilih minimal satu SO.');
            var url = '{{ route('prepare.group') }}?' + ids.map(function(id){ return 'so_ids[]=' + encodeURIComponent(id); }).join('&');
            window.location.href = url;
        }
    </script>

    </div>

    <input type="hidden" class="module" value="{{ Str::beforeLast(request()->route()->uri(), '/') }}">
    <script src="/js/table.js"></script>
    <script>initTable('{{ $sortField }}', '{{ $sortDir }}');</script>
</x-layouts::app>
