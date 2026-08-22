<?php

use Illuminate\Support\Facades\Route;
use Modules\So\Http\Controllers\CustomerController;
use Modules\So\Http\Controllers\SoController;

Route::auto('/so/so', SoController::class, ['name' => 'so-so']);
Route::auto('/so/customer', CustomerController::class, ['name' => 'so-customer']);

// AJAX endpoints (getShippingCost / getCodFee) sudah ter-cover oleh Route::auto:
//   GET so/so/shipping-cost?lat=&lng=   → hitung ongkir delivery per km
//   GET so/so/cod-fee?location=         → ongkir COD lokasi terdaftar
