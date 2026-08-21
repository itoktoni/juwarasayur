<?php

use Illuminate\Support\Facades\Route;
use Modules\Po\Http\Controllers\PoController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('pos', PoController::class)->names('po');
});
