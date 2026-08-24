<?php

use Illuminate\Support\Facades\Route;
use Modules\Chatbot\Http\Controllers\ChatbotController;

// Admin: daftar sesi chat + riwayat percakapan
Route::auto('/chatbot', ChatbotController::class, ['name' => 'chatbot']);
