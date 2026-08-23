<?php

use Illuminate\Support\Facades\Route;
use Modules\Production\Http\Controllers\ProductionController;

Route::auto('/production', ProductionController::class, ['name' => 'production']);

// AJAX: kelompokkan kebutuhan produk dari beberapa SO (custom, di luar CRUD)
Route::post('/production/group-orders', [ProductionController::class, 'getGroupOrders'])->name('production.getGroupOrders');
