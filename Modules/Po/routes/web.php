<?php

use Illuminate\Support\Facades\Route;
use Modules\Po\Http\Controllers\PoController;
use Modules\Po\Http\Controllers\PoDetailController;
use Modules\Po\Http\Controllers\SupplierController;

Route::auto('/po/supplier', SupplierController::class, ['name' => 'po-supplier']);
Route::auto('/po/po', PoController::class, [
    'name' => 'po-po',
    'except' => ['previewGenerateFromSo', 'doGenerateFromSo'],
]);
// Generate dari SO — route manual + nama grup sendiri agar menu tidak bentrok dengan po-po.*
Route::get('/po/generate-from-so', [PoController::class, 'previewGenerateFromSo'])->name('po-generate.preview');
Route::post('/po/generate-from-so', [PoController::class, 'doGenerateFromSo'])->name('po-generate.generate');
Route::auto('/po/detail', PoDetailController::class, ['name' => 'po-detail']);
