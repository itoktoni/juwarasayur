<?php

use Illuminate\Support\Facades\Route;
use Modules\Prepare\Http\Controllers\PrepareController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('prepares', PrepareController::class)->names('prepare');
});
