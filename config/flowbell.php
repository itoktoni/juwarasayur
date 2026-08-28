<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FlowBell Device Verification
    |--------------------------------------------------------------------------
    |
    | Device ID yang diizinkan mengirim webhook.
    | Kosongkan untuk nonaktifkan verifikasi device.
    |
    | Contoh: "motorola/fogos_gpn/fogos:15/V1UGS35H.75-14-9-3-1-3/..."
    |
    */

    'device_id' => env('FLOWBELL_DEVICE_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Supported Payment Apps
    |--------------------------------------------------------------------------
    |
    | Package name → payment method mapping.
    | Ditambahkan otomatis via PACKAGE_MAP di controller.
    |
    */

    'expiry_minutes' => (int) env('QRIS_EXPIRY_MINUTES', 5),

];
