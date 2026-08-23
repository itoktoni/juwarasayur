<?php

use Illuminate\Support\Facades\Route;
use Modules\Production\Http\Controllers\RoutineProductionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('productions', [RoutineProductionController::class, 'index'])->name('api.production.index');
    Route::get('productions/{id}', [RoutineProductionController::class, 'getShow'])->name('api.production.show');
});
