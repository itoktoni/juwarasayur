<?php

use Illuminate\Support\Facades\Route;
use Modules\So\Http\Controllers\PaymentWebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Pembayaran (SO)
|--------------------------------------------------------------------------
| Endpoint publik (tanpa auth/CSRF) untuk validasi pembayaran otomatis
| dari aplikasi Android / Postman.
|
|   POST/GET /api/payment/webhook
|   POST/GET /api/payment/webhook/{token}
|
| Body/query: token (so_payment_token) atau so_code, status = paid|cancel
| Header opsional: X-Webhook-Secret (wajib cocok bila dikonfigurasi)
*/
Route::match(['post', 'get'], '/payment/webhook/{token?}', [PaymentWebhookController::class, 'handle'])
    ->name('so.payment.webhook');
