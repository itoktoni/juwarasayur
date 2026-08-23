<?php

use Illuminate\Support\Facades\Route;
use Modules\Production\Http\Controllers\ProductionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // CRUD standar via ControllerTrait (index/getTable/getShow/...)
    Route::get('productions', [ProductionController::class, 'index'])->name('api.production.index');
    Route::get('productions/{id}', [ProductionController::class, 'getShow'])->name('api.production.show');
});
