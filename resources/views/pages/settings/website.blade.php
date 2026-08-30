@php
    $title = 'Website Settings';

    $whName = old('warehouse_name', $warehouse['name'] ?? '');
    $whAddr = old('warehouse_address', $warehouse['address'] ?? '');
    $whLat = old('warehouse_lat', $warehouse['lat'] ?? -7.644872);
    $whLng = old('warehouse_lng', $warehouse['lng'] ?? 112.904528);

    $qrisExpiry = old('qris_expiry', $payment['qris_expiry'] ?? 5);
    $uniqueDigits = old('unique_digits', $payment['unique_digits'] ?? 2);
    $notifyhookSecret = old('notifyhook_secret', $payment['notifyhook_secret'] ?? '');
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
                    <textarea name="description" rows="6" class="cms-wysiwyg" data-wysiwyg="1">{{ old('description', $settings['description'] ?? '') }}</textarea>
                    <p class="text-xs text-on-surface-variant mt-1">Bisa format rich text (bold, list, link, gambar) — tampil apa adanya di halaman frontend.</p>
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

            {{-- Gudang Utama + Pin Lokasi (dipakai perhitungan ongkir SO) --}}
            <h3 class="text-lg font-bold text-on-surface mb-4 pt-4 border-t border-outline-variant">Gudang Utama</h3>
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
                    <button type="button" onclick="window.useMyLocationWarehouse && window.useMyLocationWarehouse()" class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                        <span class="material-symbols-outlined text-base">my_location</span> Gunakan Lokasi Saya
                    </button>
                    <p class="text-xs text-on-surface-variant mb-2">Klik peta atau geser pin untuk mengatur titik gudang utama. Jarak pengiriman dihitung dari titik ini.</p>
                    <div id="warehouse-map" class="w-full h-80 rounded-lg border border-outline-variant z-0"></div>
                </div>
            </div>

            {{-- Printer Struk (print continues 58mm / 80mm) --}}
            <h3 class="text-lg font-bold text-on-surface mb-4 pt-4 border-t border-outline-variant">Printer Struk</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Ukuran Kertas Struk</label>
                    <select name="struk_paper_width"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                        <option value="80" @selected((string) old('struk_paper_width', config('printer.web.paper_width', 80)) === '80')>80 mm</option>
                        <option value="58" @selected((string) old('struk_paper_width', config('printer.web.paper_width', 80)) === '58')>58 mm</option>
                    </select>
                    <p class="text-xs text-on-surface-variant mt-1">Dipakai untuk print continues struk SO &amp; PO. Default dari .env: STRUK_PAPER_WIDTH.</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Komisi Reseller (%)</label>
                    <input type="number" step="1" min="0" max="100" name="commission_rate"
                        value="{{ old('commission_rate', rtrim(rtrim((string) config('commission.rate', 2), '0'), '.')) }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                    <p class="text-xs text-on-surface-variant mt-1">Persen komisi reseller dari omzet order. Default dari .env: RESELLER_COMMISSION_RATE.</p>
                    @error('commission_rate')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Minimal Pencairan Komisi (Rp)</label>
                    <input type="number" step="1000" min="0" name="min_withdraw"
                        value="{{ old('min_withdraw', config('commission.min_withdraw', 50000)) }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                    <p class="text-xs text-on-surface-variant mt-1">Jumlah minimum withdraw komisi reseller. Default dari .env: RESELLER_MIN_WITHDRAW.</p>
                    @error('min_withdraw')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- CSV Import --}}
            <h3 class="text-lg font-bold text-on-surface mb-4 pt-4 border-t border-outline-variant">CSV Import</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">CSV Delimiter</label>
                    <select name="csv_delimiter"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                        <option value=";" @selected(old('csv_delimiter', config('website.csv_delimiter', ';')) === ';')>Titik koma ( ; )</option>
                        <option value="," @selected(old('csv_delimiter', config('website.csv_delimiter', ';')) === ',')>Koma ( , )</option>
                    </select>
                    <p class="text-xs text-on-surface-variant mt-1">Pemisah kolom saat import CSV produk. Default: titik koma ( ; ).</p>
                </div>
            </div>

            {{-- Homepage / Frontend --}}
            <h3 class="text-lg font-bold text-on-surface mb-4 pt-4 border-t border-outline-variant">Homepage</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Hero Title</label>
                    <input type="text" name="hero_title" value="{{ old('hero_title', $frontend['hero']['title'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">Hero Subtitle</label>
                    <textarea name="hero_subtitle" rows="2"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">{{ old('hero_subtitle', $frontend['hero']['subtitle'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Hero CTA Text</label>
                    <input type="text" name="hero_cta_text" value="{{ old('hero_cta_text', $frontend['hero']['cta_text'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Flash Sale Title</label>
                    <input type="text" name="flash_sale_title" value="{{ old('flash_sale_title', $frontend['flash_sale']['title'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Flash Sale — Jumlah Produk</label>
                    <input type="number" min="1" max="20" name="flash_sale_count" value="{{ old('flash_sale_count', $frontend['flash_sale']['count'] ?? 6) }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Flash Sale — Durasi (jam)</label>
                    <input type="number" min="1" max="48" name="flash_sale_hours" value="{{ old('flash_sale_hours', $frontend['flash_sale']['hours'] ?? 12) }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Tampilkan Produk Terbaru</label>
                    <select name="show_latest"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                        <option value="1" @selected(old('show_latest', $frontend['latest']['show'] ?? true))>Ya</option>
                        <option value="0" @selected(!old('show_latest', $frontend['latest']['show'] ?? true))>Tidak</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Judul Produk Terbaru</label>
                    <input type="text" name="latest_title" value="{{ old('latest_title', $frontend['latest']['title'] ?? '') }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm">
                </div>
            </div>

            {{-- Payment Settings --}}
            <h3 class="text-lg font-bold text-on-surface mb-4 pt-4 border-t border-outline-variant">Payment & Webhook</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">QRIS Expiry (menit)</label>
                    <input type="number" min="1" max="60" name="qris_expiry"
                        value="{{ $qrisExpiry }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm" required>
                    <p class="text-xs text-on-surface-variant mt-1">Batas waktu pembayaran QRIS sebelum expired. Default: 5 menit.</p>
                    @error('qris_expiry')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Kode Unik (digit)</label>
                    <input type="number" min="1" max="6" name="unique_digits"
                        value="{{ $uniqueDigits }}"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm" required>
                    <p class="text-xs text-on-surface-variant mt-1">Jumlah digit kode unik pada nominal pembayaran. 2 = Rp xx039 (acak 00–99), 3 = acak 000–999, dst. Maks 6 digit.</p>
                    @error('unique_digits')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-on-surface mb-1">NotifyHook Secret</label>
                    <div class="relative" x-data="{ show: false }">
                        <input :type="show ? 'text' : 'password'" type="password" name="notifyhook_secret"
                            value="{{ $notifyhookSecret }}"
                            placeholder="Kosongkan untuk nonaktifkan verifikasi signature"
                            class="w-full border border-outline-variant rounded-lg px-3 py-2 pr-11 bg-surface text-on-surface focus:border-primary focus:ring-1 focus:ring-primary text-sm font-mono">
                        <button type="button" @click="show = !show"
                            :aria-label="show ? 'Sembunyikan secret' : 'Tampilkan secret'"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-on-surface transition-colors p-1">
                            <span class="material-symbols-outlined text-xl" x-show="!show">visibility</span>
                            <span class="material-symbols-outlined text-xl" x-show="show" style="display: none;">visibility_off</span>
                        </button>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-1">Secret untuk verifikasi webhook NotifyHook (header X-NotifyHook-Signature = HMAC-SHA256 raw body). Kosongkan = tanpa verifikasi signature.</p>
                    @error('notifyhook_secret')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
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
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
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

        // Peta gudang utama — init dipisah dari logika lain agar kegagalan
        // CDN tidak mematikan fungsi form lain.
        (() => {
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

            function initWarehouseMap() {
                const el = document.getElementById('warehouse-map');
                if (!el || typeof L === 'undefined') return;

                if (window.__warehouseMap) {
                    try { window.__warehouseMap.remove(); } catch (e) {}
                    window.__warehouseMap = null;
                }

                const latInput = document.getElementById('warehouse-lat');
                const lngInput = document.getElementById('warehouse-lng');

                const syncInputs = (lat, lng) => {
                    latInput.value = parseFloat(lat).toFixed(7);
                    lngInput.value = parseFloat(lng).toFixed(7);
                };

                const start = [parseFloat(latInput.value) || -7.644872, parseFloat(lngInput.value) || 112.904528];
                const map = L.map(el).setView(start, 13);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
                const marker = L.marker(start, { draggable: true }).addTo(map);
                window.__warehouseMap = map;

                marker.on('dragend', () => { const p = marker.getLatLng(); syncInputs(p.lat, p.lng); });
                map.on('click', e => { marker.setLatLng(e.latlng); syncInputs(e.latlng.lat, e.latlng.lng); });
                [latInput, lngInput].forEach(inputEl => inputEl.addEventListener('change', () => {
                    const lat = parseFloat(latInput.value), lng = parseFloat(lngInput.value);
                    if (!isNaN(lat) && !isNaN(lng)) { marker.setLatLng([lat, lng]); map.setView([lat, lng]); }
                }));

                window.useMyLocationWarehouse = function () {
                    if (!navigator.geolocation) { alert('Geolocation tidak didukung browser ini.'); return; }
                    navigator.geolocation.getCurrentPosition(pos => {
                        syncInputs(pos.coords.latitude, pos.coords.longitude);
                        marker.setLatLng([pos.coords.latitude, pos.coords.longitude]);
                        map.setView([pos.coords.latitude, pos.coords.longitude], 15);
                    }, err => alert('Tidak bisa mengambil lokasi: ' + err.message));
                };
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => loadLeaflet(initWarehouseMap));
            } else {
                loadLeaflet(initWarehouseMap);
            }
        })();
    </script>
    @endpush
</x-layouts::app>
