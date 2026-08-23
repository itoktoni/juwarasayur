<?php

use Illuminate\Support\Facades\Route;
use Modules\Production\Http\Controllers\OrderProductionController;
use Modules\Production\Http\Controllers\RoutineProductionController;

// 1. Produksi rutin: gabung beberapa barang jadi 1 paket
Route::auto('/production/routine', RoutineProductionController::class, ['name' => 'production-routine']);

// 2. Produksi dari SO: pilih pesanan → WO otomatis per barang
Route::auto('/production/order', OrderProductionController::class, ['name' => 'production-order']);
Route::post('/production/order/group-orders', [OrderProductionController::class, 'getGroupOrders'])->name('production-order.getGroupOrders');
Route::post('/production/order/create-orders', [OrderProductionController::class, 'postCreateOrders'])->name('production-order.createOrders');
