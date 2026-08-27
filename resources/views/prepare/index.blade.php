<x-layouts::app>
    <x-breadcrumb :items="[['url' => route('prepare.index'), 'label' => 'Prepare dari SO'], ['url' => '', 'label' => 'Pilih SO']]" />

    {{-- Filter tanggal --}}
    <x-card label="Filter Tanggal">
        <form method="GET" action="{{ route('prepare.index') }}" class="contents">
            <x-input col="4" type="date" name="tanggal" label="Tanggal SO" :value="$tanggal" />
            <div class="col-span-12 md:col-span-2 flex items-end">
                <x-button type="submit" variant="primary" icon="filter_list">Tampilkan</x-button>
            </div>
            <div class="col-span-12 md:col-span-6 flex items-end justify-end">
                <a href="{{ route('prepare.progress') }}" class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg text-sm font-semibold bg-surface-container-highest text-on-surface hover:bg-surface-container-low transition-all">
                    <span class="material-symbols-outlined text-xl">analytics</span> Lihat Progress per SO
                </a>
            </div>
        </form>
    </x-card>

    @if($sos->isEmpty())
        <x-card label="Tidak Ada SO" class="mt-5" icon="info" :noGrid="true">
            <div class="text-center py-10">
                <span class="material-symbols-outlined text-6xl text-outline">inbox</span>
                <p class="text-sm text-on-surface-variant mt-3">
                    Tidak ada SO berstatus Paid/Confirmed pada tanggal {{ formatDate($tanggal) }}
                    yang masih ada item belum disiapkan.
                </p>
            </div>
        </x-card>
    @else
            <form method="POST" action="{{ route('prepare.group') }}" class="contents">
            @csrf
            <input type="hidden" name="so_ids" />
            <x-card label="Pilih SO untuk Diprepare" class="mt-5" :noGrid="true">
                <x-table :border="false">
                    <x-slot:head>
                        <th class="w-10">
                            <input type="checkbox" id="check-all" class="accent-primary">
                        </th>
                        <th>Kode SO</th>
                        <th>Customer</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-center">Sisa Item</th>
                        <th>Status</th>
                    </x-slot:head>
                    <x-slot:body>
                        @foreach($sos as $so)
                            @php
                                $totalItems = $so->has_details->count();
                                $sisaItems = $so->has_details->filter(fn ($d) => (int) $d->so_detail_qty - (int) $d->has_prepare_allocations->sum('qty') > 0)->count();
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" name="so_ids[]" value="{{ $so->id }}" class="so-check accent-primary" {{ $sisaItems === 0 ? 'disabled' : '' }}>
                                </td>
                                <td class="font-data-mono text-data-mono text-primary">{{ $so->so_code }}</td>
                                <td>{{ $so->has_customer?->name ?? $so->so_customer_name ?? '-' }}</td>
                                <td class="text-center">{{ $totalItems }}</td>
                                <td class="text-center">
                                    <x-badge :type="$sisaItems > 0 ? 'warning' : 'success'">{{ $sisaItems }}</x-badge>
                                </td>
                                <td>
                                    <x-badge :type="match($so->so_status) {
                                        'paid' => 'info',
                                        'confirmed' => 'info',
                                        'delivered' => 'success',
                                        default => 'default'
                                    }">{{ ucfirst($so->so_status) }}</x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </x-slot:body>
                    <x-slot:mobile>
                        <div class="divide-y divide-outline-variant/40">
                            @foreach($sos as $so)
                                @php
                                    $totalItems = $so->has_details->count();
                                    $sisaItems = $so->has_details->filter(fn ($d) => (int) $d->so_detail_qty - (int) $d->has_prepare_allocations->sum('qty') > 0)->count();
                                @endphp
                                <label class="flex items-start gap-3 px-3 py-3 cursor-pointer {{ $sisaItems === 0 ? 'opacity-50' : '' }}">
                                    <input type="checkbox" name="so_ids[]" value="{{ $so->id }}" class="so-check accent-primary mt-1" {{ $sisaItems === 0 ? 'disabled' : '' }}>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="font-data-mono text-xs font-bold text-primary truncate">{{ $so->so_code }}</span>
                                            <x-badge :type="match($so->so_status) {
                                                'paid' => 'info',
                                                'confirmed' => 'info',
                                                'delivered' => 'success',
                                                default => 'default'
                                            }">{{ ucfirst($so->so_status) }}</x-badge>
                                        </div>
                                        <p class="text-sm font-medium text-on-surface truncate">{{ $so->has_customer?->name ?? $so->so_customer_name ?? '-' }}</p>
                                        <div class="flex items-center justify-between text-[11px] mt-1">
                                            <span class="text-on-surface-variant">Total {{ $totalItems }} item</span>
                                            <span class="font-semibold {{ $sisaItems > 0 ? 'text-warning' : 'text-success' }}">Sisa {{ $sisaItems }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </x-slot:mobile>
                </x-table>

                <div class="mt-5 flex justify-end gap-2">
                    <x-button type="submit" variant="primary" icon="arrow_forward">Proses Group by Product</x-button>
                </div>
            </x-card>
        </form>
    @endif

    <script>
        document.getElementById('check-all')?.addEventListener('change', function (e) {
            document.querySelectorAll('.so-check:not(:disabled)').forEach(function (cb) {
                cb.checked = e.target.checked;
            });
        });
    </script>
</x-layouts::app>
