@php
    $title = 'Website Settings';
@endphp

<x-layouts::app :title="$title">
    <div>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-on-surface">Website Settings</h2>
        </div>

        <form method="POST" action="{{ route('settings.website.save') }}" enctype="multipart/form-data" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Website Name</label>
                    <input type="text" name="name" value="{{ old('name', $settings['name'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $settings['tagline'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('description', $settings['description'] ?? '') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Address</label>
                    <textarea name="alamat" rows="2"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('alamat', $settings['alamat'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Phone</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $settings['telepon'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $settings['email'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>

                {{-- Logo Upload --}}
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Logo</label>
                    @php
                        $logoUrl = \App\Models\WebsiteSetting::fileUrl($settings['logo'] ?? null);
                    @endphp
                    <div class="flex items-center gap-4 mb-2">
                        <div id="logo-preview" class="shrink-0 w-16 h-16 rounded-lg border border-outline-variant bg-surface flex items-center justify-center overflow-hidden">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="Logo" class="max-w-full max-h-full object-contain">
                            @else
                                <span class="material-symbols-outlined text-on-surface-variant/40">image</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="logo" id="logo-input" accept="image/*"
                                class="w-full text-sm text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer"
                                onchange="previewImage(this, 'logo-preview')">
                            @if($logoUrl)
                                <label class="inline-flex items-center gap-1.5 mt-2 text-xs text-on-surface-variant cursor-pointer">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-outline-variant text-error focus:ring-error">
                                    Remove current logo
                                </label>
                            @endif
                        </div>
                    </div>
                    @error('logo')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Favicon Upload --}}
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Favicon</label>
                    @php
                        $faviconUrl = \App\Models\WebsiteSetting::fileUrl($settings['favicon'] ?? null);
                    @endphp
                    <div class="flex items-center gap-4 mb-2">
                        <div id="favicon-preview" class="shrink-0 w-10 h-10 rounded-lg border border-outline-variant bg-surface flex items-center justify-center overflow-hidden">
                            @if($faviconUrl)
                                <img src="{{ $faviconUrl }}" alt="Favicon" class="max-w-full max-h-full object-contain">
                            @else
                                <span class="material-symbols-outlined text-on-surface-variant/40 text-base">image</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="favicon" id="favicon-input" accept="image/*"
                                class="w-full text-sm text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer"
                                onchange="previewImage(this, 'favicon-preview')">
                            @if($faviconUrl)
                                <label class="inline-flex items-center gap-1.5 mt-2 text-xs text-on-surface-variant cursor-pointer">
                                    <input type="checkbox" name="remove_favicon" value="1" class="rounded border-outline-variant text-error focus:ring-error">
                                    Remove current favicon
                                </label>
                            @endif
                        </div>
                    </div>
                    @error('favicon')
                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Primary Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" value="{{ old('primary_color', $settings['colors']['primary'] ?? '#00288e') }}"
                            class="h-10 w-16 border border-outline-variant rounded-lg cursor-pointer">
                        <input type="text" id="primary_color_hex"
                            value="{{ old('primary_color', $settings['colors']['primary'] ?? '#00288e') }}"
                            class="w-28 border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono"
                            pattern="#[0-9a-fA-F]{6}" placeholder="#00288e"
                            oninput="document.querySelector('input[name=primary_color]').value = this.value">
                    </div>
                    <p class="text-xs text-on-surface-variant mt-1">Updates all buttons, links, and accent colors.</p>
                </div>
                <div class="flex items-end">
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-outline-variant bg-surface">
                        <span class="text-sm text-on-surface-variant">Preview:</span>
                        <span class="inline-block w-8 h-8 rounded-lg" style="background-color: {{ $settings['colors']['primary'] ?? '#00288e' }}"></span>
                        <span class="px-3 py-1 rounded-lg text-xs font-semibold text-on-primary" style="background-color: {{ $settings['colors']['primary'] ?? '#00288e' }}">Primary</span>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Footer Text</label>
                    <textarea name="footer_text" rows="2"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('footer_text', $settings['footer_text'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-outline-variant">
                <button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 transition-opacity">
                    Save Settings
                </button>
                <a href="{{ route('dashboard') }}" wire:navigate class="text-sm text-on-surface-variant hover:text-on-surface">
                    Cancel
                </a>
            </div>
        </form>

        {{-- ==================== PENGIRIMAN SO ==================== --}}
        @php
            $whName = old('warehouse_name', $shipping['warehouse_name'] ?? '');
            $whAddr = old('warehouse_address', $shipping['warehouse_address'] ?? '');
            $whLat = old('warehouse_lat', $shipping['warehouse_lat'] ?? -7.644872);
            $whLng = old('warehouse_lng', $shipping['warehouse_lng'] ?? 112.904528);
            $codRows = old('cod_name') !== null
                ? collect(old('cod_name'))->map(fn ($n, $i) => [
                    'name' => $n,
                    'lat' => old('cod_lat')[$i] ?? '',
                    'lng' => old('cod_lng')[$i] ?? '',
                    'fee' => old('cod_fee')[$i] ?? '',
                ])->all()
                : ($shipping['cod_locations'] ?? []);
        @endphp

        <form method="POST" action="{{ route('settings.website.shipping') }}" id="form-shipping" class="mt-8 bg-surface-container-lowest border border-outline-variant rounded-xl p-6 form-card">
            @csrf

            <h3 class="text-lg font-bold text-on-surface mb-4">Pengiriman (Sales Order)</h3>

            {{-- Gudang + Pin Lokasi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Nama Gudang</label>
                    <input type="text" name="warehouse_name" value="{{ $whName }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Alamat Gudang</label>
                    <input type="text" name="warehouse_address" value="{{ $whAddr }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Latitude</label>
                    <input type="number" step="any" name="warehouse_lat" id="warehouse-lat" value="{{ $whLat }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Longitude</label>
                    <input type="number" step="any" name="warehouse_lng" id="warehouse-lng" value="{{ $whLng }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Pin Lokasi Gudang</label>
                    <p class="text-xs text-on-surface-variant mb-2">Klik peta atau geser pin untuk mengatur titik gudang. Jarak pengiriman dihitung dari titik ini.</p>
                    <button type="button" onclick="useMyLocationWarehouse()" class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                        <span class="material-symbols-outlined text-base">my_location</span> Gunakan Lokasi Saya
                    </button>
                    <div id="warehouse-map" class="w-full h-80 rounded-lg border border-outline-variant z-0"></div>
                </div>
            </div>

            {{-- Ongkir --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Harga per Km (Rp)</label>
                    <input type="number" step="0.01" min="0" name="price_per_km" value="{{ old('price_per_km', $shipping['price_per_km'] ?? 2500) }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Minimal Ongkir (Rp)</label>
                    <input type="number" step="0.01" min="0" name="min_fee" value="{{ old('min_fee', $shipping['min_fee'] ?? 10000) }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Radius Maksimal (km)</label>
                    <input type="number" step="0.01" min="0" name="max_radius_km" value="{{ old('max_radius_km', $shipping['max_radius_km'] ?? 50) }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm" required>
                    <p class="text-xs text-on-surface-variant mt-1">0 = tanpa batas radius.</p>
                </div>
            </div>

            {{-- Lokasi COD --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-on-surface mb-1">Lokasi COD Terdaftar</label>
                <p class="text-xs text-on-surface-variant mb-2">Area COD terbatas dengan ongkir tetap per lokasi.</p>
                <div id="cod-rows" class="space-y-3"></div>
                <button type="button" onclick="addCodRow()" class="mt-3 btn btn-soft btn-sm">
                    <span class="material-symbols-outlined text-base">add</span> Tambah Lokasi COD
                </button>
            </div>

            {{-- Map provider --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Map Provider</label>
                    <select name="map_provider" class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                        @foreach(['osrm' => 'OSRM', 'openrouteservice' => 'OpenRouteService', 'custom' => 'Custom'] as $pk => $pl)
                            <option value="{{ $pk }}" @selected(old('map_provider', $shipping['map_provider'] ?? 'osrm') === $pk)>{{ $pl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Base URL Routing</label>
                    <input type="url" name="map_base_url" value="{{ old('map_base_url', $shipping['map_base_url'] ?? 'https://router.project-osrm.org') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">API Key</label>
                    <input type="text" name="map_api_key" value="{{ old('map_api_key', $shipping['map_api_key'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono"
                        placeholder="kosongkan jika tidak perlu">
                </div>
            </div>

            @error('warehouse_lat')<p class="text-xs text-error mb-2">{{ $message }}</p>@enderror
            @error('so_map_base_url')<p class="text-xs text-error mb-2">{{ $message }}</p>@enderror

            <div class="flex items-center gap-3 pt-4 border-t border-outline-variant">
                <button type="submit" class="bg-primary text-on-primary px-6 py-2.5 rounded-lg font-semibold text-sm hover:opacity-90 transition-opacity">
                    Save Shipping Settings
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // IIFE: cegah re-declaration saat Livewire navigate menukar HTML halaman
        (() => {
        if (!document.getElementById('warehouse-map')) return;

        // ================= Warehouse pin map (Leaflet / OpenStreetMap) =================
        const whLatInput = document.getElementById('warehouse-lat');
        const whLngInput = document.getElementById('warehouse-lng');
        const map = L.map('warehouse-map').setView([parseFloat(whLatInput.value) || -7.644872, parseFloat(whLngInput.value) || 112.904528], 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);

        const marker = L.marker([parseFloat(whLatInput.value) || -7.644872, parseFloat(whLngInput.value) || 112.904528], { draggable: true }).addTo(map);

        function syncWarehouseInputs(lat, lng) {
            whLatInput.value = parseFloat(lat).toFixed(7);
            whLngInput.value = parseFloat(lng).toFixed(7);
        }

        marker.on('dragend', () => {
            const pos = marker.getLatLng();
            syncWarehouseInputs(pos.lat, pos.lng);
        });
        map.on('click', e => {
            marker.setLatLng(e.latlng);
            syncWarehouseInputs(e.latlng.lat, e.latlng.lng);
        });
        [whLatInput, whLngInput].forEach(el => el.addEventListener('change', () => {
            const lat = parseFloat(whLatInput.value), lng = parseFloat(whLngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) { marker.setLatLng([lat, lng]); map.setView([lat, lng]); }
        }));

        window.useMyLocationWarehouse = function () {
            if (!navigator.geolocation) { alert('Geolocation tidak didukung browser ini.'); return; }
            navigator.geolocation.getCurrentPosition(pos => {
                syncWarehouseInputs(pos.coords.latitude, pos.coords.longitude);
                marker.setLatLng([pos.coords.latitude, pos.coords.longitude]);
                map.setView([pos.coords.latitude, pos.coords.longitude], 15);
            }, err => alert('Tidak bisa mengambil lokasi: ' + err.message));
        };

        // ================= COD location rows =================
        let codRowIndex = 0;

        function codRowHtml(idx, data = {}) {
            return `
            <div class="cod-row grid grid-cols-12 gap-2 items-end p-3 rounded-lg border border-outline-variant bg-surface-container-low/50" data-cod-index="${idx}">
                <div class="col-span-12 md:col-span-4">
                    <label class="text-xs font-bold text-on-surface-variant block mb-1">Nama Lokasi</label>
                    <input type="text" name="cod_name[]" value="${data.name ?? ''}" placeholder="cth: Pasuruan Kota"
                        class="w-full h-11 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <div class="col-span-6 md:col-span-3">
                    <label class="text-xs font-bold text-on-surface-variant block mb-1">Latitude</label>
                    <input type="number" step="any" name="cod_lat[]" value="${data.lat ?? ''}" placeholder="-7.6453"
                        class="w-full h-11 px-3 bg-white border border-outline-variant rounded-lg text-sm font-mono outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <div class="col-span-6 md:col-span-3">
                    <label class="text-xs font-bold text-on-surface-variant block mb-1">Longitude</label>
                    <input type="number" step="any" name="cod_lng[]" value="${data.lng ?? ''}" placeholder="112.9077"
                        class="w-full h-11 px-3 bg-white border border-outline-variant rounded-lg text-sm font-mono outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <div class="col-span-10 md:col-span-2">
                    <label class="text-xs font-bold text-on-surface-variant block mb-1">Ongkir (Rp)</label>
                    <input type="number" min="0" step="0.01" name="cod_fee[]" value="${data.fee ?? ''}" placeholder="5000"
                        class="w-full h-11 px-3 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
                <div class="col-span-2 flex justify-end">
                    <button type="button" onclick="this.closest('.cod-row').remove()" class="btn btn-soft w-full h-11 text-error" title="Hapus lokasi">
                        <span class="material-symbols-outlined text-lg">delete</span>
                    </button>
                </div>
                <div class="col-span-12 flex items-center gap-2">
                    <button type="button" onclick="pinCodFromMap(this)" class="text-xs font-semibold text-primary hover:underline inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">pin_drop</span> Ambil titik dari pin gudang/peta
                    </button>
                    <span class="cod-pos text-[11px] font-mono text-on-surface-variant"></span>
                </div>
            </div>`;
        }

        function addCodRow(data = {}) {
            document.getElementById('cod-rows').insertAdjacentHTML('beforeend', codRowHtml(codRowIndex++, data));
        }

        window.pinCodFromMap = function (btn) {
            const row = btn.closest('.cod-row');
            const center = map.getCenter();
            row.querySelector('[name="cod_lat[]"]').value = center.lat.toFixed(7);
            row.querySelector('[name="cod_lng[]"]').value = center.lng.toFixed(7);
            const label = row.querySelector('.cod-pos');
            if (label) label.textContent = 'titik pusat peta';
        };

        @foreach ($codRows as $row)
            addCodRow(@js($row));
        @endforeach
        })();
    </script>

    <script>
        window.previewImage = function(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="max-w-full max-h-full object-contain">';
                };
                reader.readAsDataURL(input.files[0]);
            }
        };
    </script>
    @endpush
</x-layouts::app>