<?php

use Illuminate\Support\Facades\Route;
use Modules\Po\Http\Controllers\PoController;
use Modules\Po\Http\Controllers\PoDetailController;
use Modules\Po\Http\Controllers\SupplierController;

Route::auto('/po/supplier', SupplierController::class, ['name' => 'po-supplier']);
Route::auto('/po/po', PoController::class, ['name' => 'po-po']);
Route::auto('/po/detail', PoDetailController::class, ['name' => 'po-detail']);
