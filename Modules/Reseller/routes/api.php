<?php

use Illuminate\Support\Facades\Route;
use Modules\Reseller\Http\Controllers\ResellerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('resellers', ResellerController::class)->names('reseller');
});
