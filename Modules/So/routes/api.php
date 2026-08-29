<?php

use Illuminate\Support\Facades\Route;
use Modules\So\Http\Controllers\PaymentWebhookController;

/*
|--------------------------------------------------------------------------
| Webhook Pembayaran (SO)
|--------------------------------------------------------------------------
| Endpoint publik (tanpa auth/CSRF) untuk validasi pembayaran otomatis
| dari NotifyHook (forwarder notifikasi Android).
|
|   POST /api/payment/webhook
|
| Body NotifyHook: {"ip":"...","payload":{"rule":"gopay","package":"...","text":"Rp 39 ...", ...}}
| Header wajib (bila NOTIFYHOOK_SECRET diisi):
|   X-NotifyHook-Signature: hash_hmac('sha256', raw body, NOTIFYHOOK_SECRET)
| Fallback standard format: {"amount": 39}
*/
Route::match(['post', 'get'], '/payment/webhook/{token?}', [PaymentWebhookController::class, 'handle'])
    ->name('so.payment.webhook');
