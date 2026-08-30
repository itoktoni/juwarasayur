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
];
