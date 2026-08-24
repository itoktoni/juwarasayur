<?php

use Illuminate\Support\Facades\Route;

// Area reseller kini lewat controller publik Ecommerce (/account/*).
// Rute lama diarahkan agar bookmark tidak mati.
Route::get('/reseller/customer/{any?}', fn () => redirect()->route('account.customers'))->where('any', '.*');
Route::get('/reseller/order/{any?}', fn () => redirect()->route('ecommerce.orders.index'))->where('any', '.*');
