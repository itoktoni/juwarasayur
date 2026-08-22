<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\GudangController;
use Modules\Inventory\Http\Controllers\LokasiController;
use Modules\Inventory\Http\Controllers\StockController;

Route::auto('/inventory/gudang', GudangController::class, ['name' => 'inventory-gudang']);
Route::auto('/inventory/lokasi', LokasiController::class, ['name' => 'inventory-lokasi']);
Route::auto('/inventory/stock', StockController::class, ['name' => 'inventory-stock', 'only' => ['index', 'getTable', 'getShow']]);
