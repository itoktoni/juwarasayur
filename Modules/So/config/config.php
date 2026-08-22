<?php

return [
    'name' => 'So',

    /*
    |--------------------------------------------------------------------------
    | Shipping / Pengiriman
    |--------------------------------------------------------------------------
    | Metode: pickup (diambil di gudang), cod (lokasi COD terbatas),
    | delivery (dikirim — jarak dihitung via OpenMap/OSRM per km).
    |
    */
    'shipping' => [
        // Lokasi gudang (titik awal hitung jarak)
        'warehouse' => [
            'name' => env('SO_WAREHOUSE_NAME', 'Gudang Pusat'),
            'address' => env('SO_WAREHOUSE_ADDRESS', ''),
            'lat' => (float) env('SO_WAREHOUSE_LAT', -7.644872),
            'lng' => (float) env('SO_WAREHOUSE_LNG', 112.904528),
        ],

        // Harga ongkir per km + minimum fee
        'price_per_km' => (float) env('SO_SHIPPING_PRICE_PER_KM', 2500),
        'min_fee' => (float) env('SO_SHIPPING_MIN_FEE', 10000),

        // Radius maksimal layanan kirim (km), 0 = tanpa batas
        'max_radius_km' => (float) env('SO_SHIPPING_MAX_RADIUS_KM', 50),

        // Lokasi COD: JSON array [{name, lat, lng, fee}]
        'cod_locations' => json_decode(
            env('SO_COD_LOCATIONS', '[{"name":"Pasuruan Kota","lat":-7.6453,"lng":112.9077,"fee":5000},{"name":"Rejoso","lat":-7.6842,"lng":112.9286,"fee":8000}]'),
            true
        ) ?? [],
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenMap (routing/distance provider)
    |--------------------------------------------------------------------------
    | provider osrm → base_url OSRM (self-host atau demo server).
    | Set SO_MAP_API_KEY untuk provider yang butuh auth.
    |
    */
    'map' => [
        'provider' => env('SO_MAP_PROVIDER', 'osrm'),
        'base_url' => env('SO_MAP_BASE_URL', 'https://router.project-osrm.org'),
        'api_key' => env('SO_MAP_API_KEY'),
        'timeout' => (int) env('SO_MAP_TIMEOUT', 10),
    ],
];
