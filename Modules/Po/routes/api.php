<?php

use Illuminate\Support\Facades\Route;
use Modules\Po\Http\Controllers\PoController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('pos', PoController::class)->names('po');
});
