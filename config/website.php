<?php

// Konfigurasi website — SEMUA nilai dibaca dari .env, bukan hardcode di sini.
// Nilai bisa diubah via menu Settings → Website (disimpan ke .env oleh WebsiteSetting::persist()).
return [
    'name' => env('WEBSITE_NAME', 'JuwaraSayur.id'),
    'tagline' => env('WEBSITE_TAGLINE', 'Web Application'),
    'description' => env('WEBSITE_DESCRIPTION', 'JuwaraSayur.id hadir sebagai mitra terpercaya Anda dalam memenuhi kebutuhan pangan sehat dan bernutrisi dengan menyediakan pilihan sayuran segar berkualitas tinggi untuk meja makan Anda.'),
    'alamat' => env('WEBSITE_ALAMAT', ''),
    'telepon' => env('WEBSITE_TELEPON', ''),
    'email' => env('WEBSITE_EMAIL', ''),
    'logo' => env('WEBSITE_LOGO'),
    'favicon' => env('WEBSITE_FAVICON', '/favicon.ico'),
    'colors' => [
        'primary' => env('WEBSITE_PRIMARY_COLOR', '#388e3c'),
    ],
    'social' => [
        'facebook' => env('WEBSITE_FACEBOOK'),
        'instagram' => env('WEBSITE_INSTAGRAM'),
        'twitter' => env('WEBSITE_TWITTER'),
        'youtube' => env('WEBSITE_YOUTUBE'),
        'tiktok' => env('WEBSITE_TIKTOK'),
    ],
    'footer_text' => env('WEBSITE_FOOTER_TEXT', ''),
    // Gudang utama (juga dibaca Modules/So via config so.shipping.warehouse.*)
    'warehouse_name' => env('SO_WAREHOUSE_NAME', 'Gudang Pusat'),
    'warehouse_address' => env('SO_WAREHOUSE_ADDRESS', ''),
    'warehouse_lat' => env('SO_WAREHOUSE_LAT', '-7.644872'),
    'warehouse_lng' => env('SO_WAREHOUSE_LNG', '112.904528'),
    // Ukuran kertas struk print continues (58mm / 80mm) — juga dibaca config/printer.php
    'struk_paper_width' => env('STRUK_PAPER_WIDTH', '80'),
    // Komisi reseller (%) — juga dibaca config/commission.php
    'commission_rate' => env('RESELLER_COMMISSION_RATE', '2'),
    // Minimal pencairan komisi (Rp)
    'min_withdraw' => env('RESELLER_MIN_WITHDRAW', '25000'),
];
