<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FlowBellWebhookController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Modules\So\Http\Controllers\PaymentWebhookController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);

    Route::auto('/users', UsersController::class, ['name' => 'users']);
});

Route::post('/payment/webhook', [PaymentWebhookController::class, 'handle'])
    ->name('so.payment.webhook');

Route::post('/flowbell/webhook', [FlowBellWebhookController::class, 'handle'])
    ->name('flowbell.webhook');
