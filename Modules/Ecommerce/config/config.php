<?php

return [
    'name' => 'Ecommerce',

    // QRIS static payload dari .env — nominal dinamis disisipkan via helper nominalQRIS()
    'qris_payload' => env('QRIS'),
];
