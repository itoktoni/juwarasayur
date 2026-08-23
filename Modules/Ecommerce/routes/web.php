<?php

use Illuminate\Support\Facades\Route;
use Modules\Ecommerce\Http\Controllers\CartController;
use Modules\Ecommerce\Http\Controllers\CheckoutController;
use Modules\Ecommerce\Http\Controllers\OrderController;
use Modules\Ecommerce\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Public — tanpa login
|--------------------------------------------------------------------------
| Cart berbasis session browser (guest) / DB (login). Checkout & pembayaran
| QRIS bisa dilakukan tanpa akun.
*/

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/quote-cod', [CheckoutController::class, 'quoteCod'])->name('checkout.quoteCod');
Route::post('/checkout/quote-cod-location', [CheckoutController::class, 'quoteCodLocation'])->name('checkout.quoteCodLocation');
Route::post('/checkout/quote-delivery', [CheckoutController::class, 'quoteDelivery'])->name('checkout.quoteDelivery');
Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');

// Pembayaran QRIS (mockup)
Route::get('/payment/{id}', [PaymentController::class, 'show'])->name('payment.show');
Route::get('/payment/{id}/invoice', [PaymentController::class, 'invoice'])->name('payment.invoice');
Route::post('/payment/{id}/simulate', [PaymentController::class, 'simulate'])->name('payment.simulate');

/*
|--------------------------------------------------------------------------
| Auth — riwayat pesanan user login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/account/orders', [OrderController::class, 'index'])->name('ecommerce.orders.index');
    Route::get('/account/orders/{id}', [OrderController::class, 'show'])->name('ecommerce.orders.show');
});
