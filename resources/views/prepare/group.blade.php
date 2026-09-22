<x-layouts::app>
    <x-breadcrumb :items="[
        ['url' => route('prepare.index'), 'label' => 'Prepare dari SO'],
        ['url' => '', 'label' => 'Group by Product'],
    ]" />

    @php $totalSisa = $groups->sum('sisa'); @endphp
    <x-card label="Ringkasan Group" class="mt-5" icon="category" :noGrid="true">
        <p class="text-sm text-on-surface-variant">
            Dari <strong>{{ count($soIds) }}</strong> SO yang dipilih, sistem mengelompokkan berdasarkan produk
            (total sisa <strong>{{ $totalSisa }}</strong> unit).
            Klik <strong>Siapkan</strong> per produk, atau isi 1 lokasi + <strong>Siapkan Semua</strong> di bawah.
        </p>
    </x-card>

    @forelse($groups as $g)
        @php
            $progress = $g['total_diminta'] > 0 ? min(100, round($g['total_disiapkan'] / $g['total_diminta'] * 100)) : 0;
        @endphp
        <x-card :label="$g['product']?->product_nama ?? 'Produk #' . $loop->iteration" class="mt-5" :noGrid="true">
            <div class="col-span-12 mb-4 rounded-lg bg-primary-container/30 px-4 py-3 flex flex-wrap justify-between items-center gap-2">
                <span class="font-semibold text-on-surface">
                    {{ $g['product']?->product_nama }}
                    <span class="text-xs text-on-surface-variant ml-2">({{ $g['product']?->has_satuan?->satuan_nama ?? 'pcs' }})</span>
                </span>
                <span class="text-sm font-bold text-on-surface">
                    {{ $g['total_disiapkan'] }} / {{ $g['total_diminta'] }} unit ({{ $progress }}%)
                </span>
            </div>

            {{-- Progress bar --}}
            <div class="col-span-12 mb-4">
                <div class="h-1.5 rounded-full bg-surface-container overflow-hidden">
                    <div class="h-full rounded-full {{ $progress >= 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <x-table :border="false">
                <x-slot:head>
                    <th>SO</th>
                    <th>Customer</th>
                    <th class="text-center">Qty Diminta</th>
                    <th class="text-center">Sudah Disiapkan</th>
                    <th class="text-center">Sisa</th>
                </x-slot:head>
                <x-slot:body>
                    @foreach($g['sos'] as $row)
                        @php
                            $rowSisa = max(0, $row['qty_diminta'] - $row['qty_disiapkan']);
                        @endphp
                        <tr>
                            <td class="font-data-mono text-data-mono text-primary">{{ $row['so_code'] }}</td>
                            <td>{{ $row['customer'] }}</td>
                            <td class="text-center font-medium">{{ $row['qty_diminta'] }}</td>
                            <td class="text-center">
                                <x-badge :type="$row['qty_disiapkan'] > 0 ? 'info' : 'default'">{{ $row['qty_disiapkan'] }}</x-badge>
                            </td>
                            <td class="text-center">
                                <span class="{{ $rowSisa > 0 ? 'font-bold text-warning' : 'text-success' }}">{{ $rowSisa }}</span>
                            </td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>

            <div class="col-span-12 mt-4 pt-4 border-t border-outline-variant/30 flex justify-end">
                @if($g['sisa'] > 0)
                    <a href="{{ route('prepare.prepareForm', ['product' => $g['product']?->id, 'so_detail_ids' => $g['so_detail_ids']]) }}"
                       class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg text-sm font-semibold bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all">
                        <span class="material-symbols-outlined text-xl">inventory_2</span>
                        Siapkan ({{ $g['sisa'] }} unit)
                    </a>
                @else
                    <span class="inline-flex items-center gap-2 text-success font-semibold">
                        <span class="material-symbols-outlined">check_circle</span> Sudah Lengkap
                    </span>
                @endif
            </div>
        </x-card>
    @empty
        <x-card label="Tidak Ada Data" class="mt-5" :noGrid="true">
            <p class="text-sm text-on-surface-variant text-center py-8">SO yang dipilih tidak memiliki detail produk.</p>
        </x-card>
    @endforelse

    @if($totalSisa > 0)
    <form action="{{ route('prepare.prepareAll') }}" method="POST" onsubmit="return confirm('Siapkan SELURUH sisa ({{ $totalSisa }} unit) dari 1 lokasi?');">
        @csrf
        @foreach($soIds as $sid)
            <input type="hidden" name="so_ids[]" value="{{ $sid }}">
        @endforeach
        <x-card label="Siapkan Semua Qty" class="mt-5" icon="done_all">
            <div class="col-span-12 md:col-span-8">
                <label class="text-xs font-bold text-on-surface-variant block mb-1">Lokasi Gudang <span class="text-error">*</span></label>
                <div class="flex flex-col md:flex-row gap-2">
                    <select name="lokasi_id" class="flex-1 h-12 px-3 bg-white border border-outline-variant rounded-lg text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none" required>
                        <option value="">-- Pilih Lokasi --</option>
                        @foreach($lokasiOptions as $lok)
                            <option value="{{ $lok->id }}">{{ $lok->lokasi_nama }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-12 px-5 text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 transition-all active:scale-95 shrink-0">
                        <span class="material-symbols-outlined text-xl">done_all</span> Siapkan Semua ({{ $totalSisa }} unit)
                    </button>
                </div>
                <label class="mt-2 flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" name="force" value="1" class="mt-0.5 w-4 h-4 shrink-0 accent-red-600">
                    <span class="text-xs text-on-surface-variant"><b class="text-error">Force prepare</b> — lewati cek stock (bypass). Alokasi + movement tetap dicatat, stock boleh minus. Pakai bila barang fisik ada tapi stock sistem belum diinput.</span>
                </label>
                @error('lokasi_id')<p class="text-error text-xs mt-2">{{ $message }}</p>@enderror
                <p class="text-xs text-on-surface-variant mt-2">Seluruh sisa tiap produk dialokasikan FIFO per SO dari lokasi ini (expired kosong). Tanpa force: produk yang stok lokasinya kurang dilewati & dilaporkan, yang lain tetap tersimpan.</p>
            </div>
        </x-card>
    </form>
    @endif

    <div class="mt-5">
        <a href="{{ route('prepare.index') }}" class="inline-flex items-center justify-center gap-2 h-10 px-5 rounded-lg text-sm font-semibold bg-surface-container-highest text-on-surface hover:bg-surface-container-low transition-all">
            <span class="material-symbols-outlined text-xl">arrow_back</span> Kembali
        </a>
    </div>
</x-layouts::app>
