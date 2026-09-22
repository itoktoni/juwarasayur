<?php

use Illuminate\Support\Facades\Route;
use Modules\Po\Http\Controllers\PoController;
use Modules\Po\Http\Controllers\PoDetailController;
use Modules\Po\Http\Controllers\SupplierController;

Route::auto('/po/supplier', SupplierController::class, ['name' => 'po-supplier']);
Route::auto('/po/po', PoController::class, [
    'name' => 'po-po',
    'except' => ['previewGenerateFromSo', 'doGenerateFromSo', 'getTemplate', 'getImport', 'postImport'],
]);
// Import PO via Excel/CSV — route manual agar tidak bentrok dengan Route::auto
Route::get('/po/template', [PoController::class, 'getTemplate'])->name('po-po.template');
Route::get('/po/import', [PoController::class, 'getImport'])->name('po-po.import');
Route::post('/po/import', [PoController::class, 'postImport'])->name('po-po.import.post');
// Generate dari SO — route manual + nama grup sendiri agar menu tidak bentrok dengan po-po.*
Route::get('/po/generate-from-so', [PoController::class, 'previewGenerateFromSo'])->name('po-generate.preview');
Route::post('/po/generate-from-so', [PoController::class, 'doGenerateFromSo'])->name('po-generate.generate');
Route::auto('/po/detail', PoDetailController::class, ['name' => 'po-detail']);
