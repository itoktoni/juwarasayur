<?php /** @var Modules\Ecommerce\Models\CodLocation $model */ ?>
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => 'Pengiriman & COD'], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card label="Lokasi COD">
            @bind($model ?? null)
                <x-input col="6" name="location_name" label="Nama Lokasi" placeholder="cth: Pasuruan Kota" />
                <x-input col="6" name="address" label="Alamat" placeholder="Opsional" />
                <x-input col="3" name="lat" label="Latitude" type="number" step="any" />
                <x-input col="3" name="lng" label="Longitude" type="number" step="any" />
                <x-input col="3" name="fee" label="Ongkir (Rp)" type="number" min="0" step="0.01"
                    helper="Kosongkan agar ongkir dihitung dari jarak." />
                <x-select col="3" name="is_active" label="Status" :options="['1' => 'Aktif', '0' => 'Nonaktif']" />
            @endbind

            {{-- Open map: klik peta / geser pin untuk mengisi titik lokasi COD --}}
            <div class="col-span-12 mt-2">
                <button type="button" onclick="window.codUseMyLocation && window.codUseMyLocation()" class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                    <span class="material-symbols-outlined text-base">my_location</span> Gunakan Lokasi Saya
                </button>
                <p class="text-xs text-on-surface-variant mb-2">Klik peta atau geser pin untuk menentukan titik lokasi COD.</p>
                <div id="cod-map" class="w-full h-80 rounded-lg border border-outline-variant z-0"></div>
                <p id="cod-map-status" class="text-xs text-on-surface-variant mt-1"></p>
            </div>
        </x-card>

        <x-action :model="$model" :action="['save']"/>

        {{-- Push HARUS di dalam x-layouts::app: stack sudah tercetak saat komponen
             selesai dirender, push setelah tag penutup tidak akan tampil. --}}
        @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script>
            // Peta pin lokasi COD. Init dipisah dari form lain agar kegagalan CDN
            // tidak mematikan fungsi simpan.
            (() => {
                const DEF_LAT = {{ (float) ($warehouse['lat'] ?? -7.644872) }};
                const DEF_LNG = {{ (float) ($warehouse['lng'] ?? 112.904528) }};

                function loadLeaflet(cb) {
                    if (window.L) { cb(); return; }
                    const s = document.createElement('script');
                    s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    s.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
                    s.crossOrigin = '';
                    s.onload = cb;
                    s.onerror = () => console.error('Gagal memuat Leaflet.');
                    document.head.appendChild(s);
                }

                function initCodMap() {
                    const el = document.getElementById('cod-map');
                    const latInput = document.querySelector('input[name="lat"]');
                    const lngInput = document.querySelector('input[name="lng"]');
                    if (!el || !latInput || !lngInput || typeof L === 'undefined') return;

                    if (window.__codMap) {
                        try { window.__codMap.remove(); } catch (e) {}
                        window.__codMap = null;
                    }

                    const syncInputs = (lat, lng) => {
                        latInput.value = parseFloat(lat).toFixed(7);
                        lngInput.value = parseFloat(lng).toFixed(7);
                    };

                    let start = [parseFloat(latInput.value), parseFloat(lngInput.value)];
                    if (isNaN(start[0]) || isNaN(start[1]) || (start[0] === 0 && start[1] === 0)) start = [DEF_LAT, DEF_LNG];

                    const map = L.map(el).setView(start, 14);
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
                    const marker = L.marker(start, { draggable: true }).addTo(map);
                    window.__codMap = map;
                    window.__codMarker = marker;

                    marker.on('dragend', () => { const p = marker.getLatLng(); syncInputs(p.lat, p.lng); });
                    map.on('click', e => { marker.setLatLng(e.latlng); syncInputs(e.latlng.lat, e.latlng.lng); });

                    [latInput, lngInput].forEach(inputEl => inputEl.addEventListener('change', () => {
                        const lat = parseFloat(latInput.value), lng = parseFloat(lngInput.value);
                        if (!isNaN(lat) && !isNaN(lng)) { marker.setLatLng([lat, lng]); map.setView([lat, lng]); }
                    }));

                    // Tombol "Gunakan Lokasi Saya" — GPS browser
                    window.codUseMyLocation = function () {
                        const status = document.getElementById('cod-map-status');
                        if (!navigator.geolocation) {
                            if (status) status.textContent = 'Browser tidak mendukung geolokasi.';
                            return;
                        }
                        if (status) status.textContent = 'Mengambil lokasi…';
                        navigator.geolocation.getCurrentPosition(pos => {
                            syncInputs(pos.coords.latitude, pos.coords.longitude);
                            marker.setLatLng([pos.coords.latitude, pos.coords.longitude]);
                            map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                            if (status) status.textContent = '';
                        }, err => {
                            if (status) status.textContent = 'Izin lokasi ditolak. Tandai titik langsung di peta.';
                        }, { enableHighAccuracy: true, timeout: 10000 });
                    };
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => loadLeaflet(initCodMap));
                } else {
                    loadLeaflet(initCodMap);
                }
            })();
        </script>
        @endpush
    </x-form>
</x-layouts::app>
