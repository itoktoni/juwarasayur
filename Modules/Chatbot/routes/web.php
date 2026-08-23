<?php

use Illuminate\Support\Facades\Route;
use Modules\Chatbot\Http\Controllers\ChatbotController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('chatbots', ChatbotController::class)->names('chatbot');
});
