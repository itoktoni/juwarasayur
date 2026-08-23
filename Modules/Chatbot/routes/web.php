<?php

use Illuminate\Support\Facades\Route;
use Modules\Chatbot\Http\Controllers\ChatbotController;

// Halaman admin chatbot (prefix /admin ditambahkan oleh RouteServiceProvider)
Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');

// Backoffice CRUD (skeleton default)
Route::resource('chatbots', ChatbotController::class)->names('chatbot');
