<?php

use Illuminate\Support\Facades\Route;
use Modules\Reseller\Http\Controllers\CustomerController;
use Modules\Reseller\Http\Controllers\OrderController;

Route::auto('/reseller/customer', CustomerController::class, ['name' => 'reseller-customer']);
Route::auto('/reseller/order', OrderController::class, ['name' => 'reseller-order']);
