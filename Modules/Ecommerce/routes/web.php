<?php

use Illuminate\Support\Facades\Route;
use Modules\Ecommerce\Http\Controllers\AccountController;
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
Route::post('/cart/set-customer', [CartController::class, 'setCustomer'])->name('cart.setCustomer');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/quote-cod', [CheckoutController::class, 'quoteCod'])->name('checkout.quoteCod');
Route::post('/checkout/quote-cod-location', [CheckoutController::class, 'quoteCodLocation'])->name('checkout.quoteCodLocation');
Route::post('/checkout/quote-delivery', [CheckoutController::class, 'quoteDelivery'])->name('checkout.quoteDelivery');
Route::post('/checkout/discount/redeem', [CheckoutController::class, 'redeemDiscount'])->name('checkout.discount.redeem');
Route::post('/checkout/discount/remove', [CheckoutController::class, 'removeDiscount'])->name('checkout.discount.remove');
Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.placeOrder');

// Shareable link setelah reseller memesan untuk customer
Route::get('/order/share/{token}', [CheckoutController::class, 'share'])->name('checkout.share');

// Pembayaran QRIS (mockup) — URL memakai token uuid, bukan id
Route::get('/payment/{token}', [PaymentController::class, 'show'])->name('payment.show');
Route::get('/payment/{token}/invoice', [PaymentController::class, 'invoice'])->name('payment.invoice');
Route::get('/payment/{token}/status', [PaymentController::class, 'status'])->name('payment.status');

/*
|--------------------------------------------------------------------------
| Auth — riwayat pesanan user login
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/account/orders', [OrderController::class, 'index'])->name('ecommerce.orders.index');
    Route::get('/account/orders/{id}', [OrderController::class, 'show'])->name('ecommerce.orders.show');
});

/*
|--------------------------------------------------------------------------
| Auth — area akun (profile publik & customer reseller), tanpa 'admin'
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::post('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::post('/account/profile/password', [AccountController::class, 'updatePassword'])->name('account.password.update');

    Route::get('/account/customers', [AccountController::class, 'customers'])->name('account.customers');
    Route::get('/account/customers/create', [AccountController::class, 'customerCreate'])->name('account.customers.create');
    Route::post('/account/customers', [AccountController::class, 'customerStore'])->name('account.customers.store');
    Route::get('/account/customers/{id}/edit', [AccountController::class, 'customerEdit'])->name('account.customers.edit');
    Route::post('/account/customers/{id}', [AccountController::class, 'customerUpdate'])->name('account.customers.update');
    Route::post('/account/customers/{id}/delete', [AccountController::class, 'customerDelete'])->name('account.customers.delete');
});
