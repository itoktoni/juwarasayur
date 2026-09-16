<?php /** @var App\Models\User $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Affiliator'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)
                <x-input col="6" name="name" label="Nama" />
                <x-input col="6" name="email" type="email" />
                <x-input col="6" name="phone" label="No. HP / WhatsApp" />
                <x-input col="6" name="password" type="password" :helper="$model->exists ? 'Kosongkan jika tidak ingin mengganti password' : null" />
                <x-textarea col="12" name="address" label="Alamat" rows="2" />
                <x-input col="6" name="fee" type="number" step="1" min="0" max="100"
                    label="Fee Komisi (%)"
                    placeholder="{{ rtrim(rtrim((string) config('commission.rate', 2), '0'), '.') }}"
                    helper="Khusus affiliator ini. Kosongkan untuk pakai default komisi ({{ rtrim(rtrim((string) config('commission.rate', 2), '0'), '.') }}%)" />
                <div class="col-span-12 md:col-span-6">
                    <label class="flex items-center gap-3 h-12 px-4 bg-white border border-outline-variant rounded-lg cursor-pointer">
                        <input type="hidden" name="consignasi_check" value="1">
                        <input type="checkbox" name="consignasi" value="1" class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary-container" @checked(old('consignasi') || $model?->consignasi)>
                        <span class="text-sm font-semibold text-on-surface">Ikut skema Titip Jual (konsinyasi)</span>
                    </label>
                    <p class="text-xs text-on-surface-variant mt-1">Affiliator muncul di menu Konsinyasi Hari Ini untuk pencatatan titip barang & tarik uang.</p>
                </div>

                @if(isset($model) && $model->exists && $model->referral_code)
                <div class="col-span-12">
                    <label class="text-xs font-bold text-on-surface-variant block mb-1">Link Referral</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ url('/r/'.$model->referral_code) }}" class="flex-1 h-12 px-3 bg-surface-container border border-outline-variant rounded-lg text-sm font-mono" />
                        <button type="button" onclick="navigator.clipboard.writeText('{{ url('/r/'.$model->referral_code) }}'); this.textContent='Tersalin'; setTimeout(()=>this.textContent='Salin',1500)" class="h-12 px-4 rounded-lg bg-amber-500 text-white font-semibold text-sm">Salin</button>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-1">Bagikan link ini — customer yang daftar via link otomatis jadi milik affiliator ini.</p>
                </div>
                @endif

                <x-file
                    name="avatar"
                    label="Foto Profil"
                    col="12"
                    accept="image/*"
                    capture="environment"
                    :preview="true"
                    :value="$model?->avatar_url"
                    helper="Ambil foto via kamera di HP atau pilih dari galeri" />
            @endbind
        </x-card>

        {{-- Pilih multiple customer untuk affiliator ini --}}
        <x-card label="Customer milik Affiliator" class="mt-5">
            <div class="col-span-12">
                @php
                    $selIds = old('customer_ids', $selectedCustomerIds ?? []);
                    if (! is_array($selIds)) $selIds = $selIds ? [(int)$selIds] : [];
                    $selIds = array_map('strval', $selIds);
                @endphp
                <div class="col-span-12">
                    <input type="hidden" name="customer_ids_submitted" value="1">
                    <label class="font-body-sm text-body-sm font-bold text-on-surface-variant block mb-1">Pilih Customer (multiple)</label>
                    <select name="customer_ids[]" multiple id="select-customer_ids" class="search w-full h-12 bg-transparent font-body-sm">
                        @foreach(($customerOptions ?? []) as $cid => $clabel)
                            <option value="{{ $cid }}" @selected(in_array((string)$cid, $selIds, true))>{{ $clabel }}</option>
                        @endforeach
                    </select>
                    <span class="font-label-caps text-label-caps text-on-surface-variant mt-1 block">Pilih beberapa customer yang menjadi milik affiliator ini. Customer yang tidak dipilih akan dilepas. Yang dipilih dari pemilik lain akan dipindahkan kesini.</span>
                </div>
                <p class="text-xs text-on-surface-variant mt-2">Simpan form untuk menerapkan perubahan. Lihat juga di <a href="{{ isset($model) && $model->exists ? url('/admin/so/customer/table').'?filter[reference_id]='.$model->id : url('/admin/so/customer/table') }}" class="text-primary hover:underline">tabel Customer</a>.</p>
            </div>
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>

    {{-- TomSelect untuk multiple customer — style khusus karena tidak pakai x-select (agar search dropdown ter-style) --}}
    <style>
        .ts-wrapper { display:block !important; border:none !important; box-shadow:none !important; outline:none !important; }
        .ts-wrapper .ts-control { background:white !important; border:1px solid #c4c5d5 !important; border-radius:0.5rem !important; min-height:3rem !important; height:auto !important; padding:0.5rem 2.5rem 0.5rem 1rem !important; font-size:0.875rem !important; line-height:1.25rem !important; color:#191c1e !important; box-shadow:none !important; opacity:1 !important; }
        .ts-wrapper.multi .ts-control { display:flex !important; flex-wrap:wrap !important; align-items:center !important; gap:4px !important; height:auto !important; }
        .ts-wrapper.multi .item { background:#00288e !important; color:#fff !important; border:none !important; border-radius:0.25rem !important; padding:2px 6px !important; line-height:1.4 !important; }
        .ts-wrapper .ts-control::after { content:'' !important; display:block !important; position:absolute !important; right:0.75rem !important; top:50% !important; transform:translateY(-50%) !important; width:0 !important; height:0 !important; border-left:5px solid transparent !important; border-right:5px solid transparent !important; border-top:5px solid #444653 !important; border-bottom:none !important; margin:0 !important; pointer-events:none !important; }
        .ts-wrapper.focus .ts-control, .ts-wrapper .ts-control:focus { border-color:#1e40af !important; box-shadow:0 0 0 1px #1e40af !important; }
        .ts-wrapper .ts-control > * { color:#191c1e !important; }
        .ts-wrapper .ts-dropdown { background:white !important; border:1px solid #c4c5d5 !important; border-radius:0.5rem !important; margin-top:4px !important; box-shadow:0 4px 12px rgba(0,0,0,0.1) !important; z-index:50 !important; }
        .ts-wrapper .ts-dropdown .ts-dropdown-content { max-height:200px !important; }
        .ts-wrapper .ts-dropdown .option { padding:0.625rem 1rem !important; font-size:0.875rem !important; color:#191c1e !important; }
        .ts-wrapper .ts-dropdown .option.active { background:#dde1ff !important; color:#00288e !important; }
        .ts-wrapper .ts-dropdown .option:hover { background:#eceef0 !important; }
        .ts-wrapper .ts-control input { font-size:0.875rem !important; }
        .ts-wrapper .ts-placeholder { color:#757684 !important; font-size:0.875rem !important; }
        .ts-wrapper.has-error .ts-control { border-color:#ba1a1a !important; }
        .ts-wrapper.multi .remove { color:#fff !important; border:none !important; opacity:0.7 !important; } .ts-wrapper.multi .remove:hover { opacity:1 !important; }
    </style>
    <script>
    (function(){
        function initCust(){
            var el=document.getElementById('select-customer_ids');
            if(!el || el.tomselect || !window.TomSelect) return;
            new TomSelect(el,{plugins:['remove_button'], placeholder:'-- Pilih customer --', allowEmptyOption:true, maxOptions: 500});
        }
        initCust();
        document.addEventListener('DOMContentLoaded', initCust);
        document.addEventListener('livewire:navigated', initCust);
        // retry jika TomSelect load belakangan (vite)
        var t=0, iv=setInterval(function(){ if(window.TomSelect){ initCust(); clearInterval(iv);} if(++t>50) clearInterval(iv); },100);
    })();
    </script>

    @if(isset($model) && $model->exists)
    <div class="mt-6">
        <x-card label="Daftar Customer — milik affiliator ini (read-only)">
            <div class="col-span-12">
                @php
                    $customers = \App\Models\User::where('type', \App\Enums\UserTypeEnum::CUSTOMER)->where('reference_id', $model->id)->orderBy('name')->get();
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <p class="text-sm font-semibold text-on-surface">Total {{ $customers->count() }} customer</p>
                    <a href="{{ url('/admin/so/customer/table') }}?filter[reference_id]={{ $model->id }}" class="btn btn-soft btn-sm">Lihat di tabel Customer</a>
                </div>
                @if($customers->isEmpty())
                    <p class="text-sm text-on-surface-variant py-4 text-center border border-dashed border-outline-variant rounded-lg">Belum ada customer — pilih di field <b>Pilih Customer</b> di atas lalu Simpan.</p>
                @else
                    <div class="overflow-auto rounded-lg border border-outline-variant">
                        <table class="w-full text-sm">
                            <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
                                <tr><th class="px-3 py-2 text-left">Nama</th><th class="px-3 py-2 text-left">Email</th><th class="px-3 py-2 text-left">Phone</th><th class="px-3 py-2 text-right">Aksi</th></tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/50" id="customerTableBody">
                                @foreach($customers as $c)
                                <tr>
                                    <td class="px-3 py-2 font-medium">{{ $c->name }}</td>
                                    <td class="px-3 py-2 text-on-surface-variant">{{ $c->email }}</td>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $c->phone ?? '-' }}</td>
                                    <td class="px-3 py-2 text-right flex gap-2 justify-end">
                                        <a href="{{ url('/admin/so/customer/update/'.$c->id) }}" class="text-primary text-xs font-semibold hover:underline">Edit</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </x-card>
    </div>
    @endif
</x-layouts::app>
