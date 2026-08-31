<?php

return [
    'hero' => [
        'title' => env('FRONTEND_HERO_TITLE', 'Sayur & Sembako Segar, Langsung dari Gudang'),
        'subtitle' => env('FRONTEND_HERO_SUBTITLE', 'Harga grosir untuk semua. Pesan mudah, ambil di gudang atau kirim ke lokasi Anda.'),
        'cta_text' => env('FRONTEND_HERO_CTA_TEXT', 'Mulai Belanja'),
    ],
    'flash_sale' => [
        'title' => env('FRONTEND_FLASH_SALE_TITLE', 'Flash Sale'),
        'count' => (int) env('FRONTEND_FLASH_SALE_COUNT', 6),
        'hours' => (int) env('FRONTEND_FLASH_SALE_HOURS', 12),
    ],
    'latest' => [
        'show' => (bool) env('FRONTEND_SHOW_LATEST', true),
        'title' => env('FRONTEND_LATEST_TITLE', 'Produk Terbaru'),
    ],
    'footer' => [
        'tagline' => env('FRONTEND_FOOTER_TAGLINE', 'Sayur-mayur & bahan dapur segar — sayur, telur, ikan, ayam, daging. Pasar ke dapur Anda, setiap hari.'),
        'alamat' => env('FRONTEND_FOOTER_ALAMAT', ''),
        'telepon' => env('FRONTEND_FOOTER_TELEPON', ''),
        'email' => env('FRONTEND_FOOTER_EMAIL', ''),
    ],
    'shipping' => [
        'pickup' => (bool) env('FRONTEND_SHIPPING_PICKUP', true),
        'cod' => (bool) env('FRONTEND_SHIPPING_COD', true),
        'delivery' => (bool) env('FRONTEND_SHIPPING_DELIVERY', true),
    ],
    'delivery' => [
        'free_km' => (float) env('FRONTEND_DELIVERY_FREE_KM', 10),
        'price_per_km' => (float) env('FRONTEND_DELIVERY_PRICE_PER_KM', 2500),
        'min_fee' => (float) env('FRONTEND_DELIVERY_MIN_FEE', 10000),
    ],
];
