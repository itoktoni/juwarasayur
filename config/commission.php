<?php

/*
|--------------------------------------------------------------------------
| Komisi Reseller
|--------------------------------------------------------------------------
|
| rate: persen komisi reseller dari omzet order (bisa di-override dari
|       Settings → Website, tersimpan ke .env RESELLER_COMMISSION_RATE).
|
*/

return [
    'rate' => (float) env('RESELLER_COMMISSION_RATE', 2),

    // Minimal jumlah pencairan komisi (Rp). Bisa di-override dari Settings → Website.
    'min_withdraw' => (float) env('RESELLER_MIN_WITHDRAW', 50000),
];
