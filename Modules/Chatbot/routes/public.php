<?php

use Illuminate\Support\Facades\Route;
use Modules\Chatbot\Http\Controllers\ChatbotWebhookController;

/*
|--------------------------------------------------------------------------
| Webhook publik (WhatsApp & Telegram)
|--------------------------------------------------------------------------
| Dipanggil oleh server messenger (POST JSON). CSRF dicexempt di
| bootstrap/app.php -> validateCsrfTokens(except).
*/

Route::post('/chatbot/webhook/telegram', [ChatbotWebhookController::class, 'telegram'])
    ->name('chatbot.webhook.telegram');

Route::post('/chatbot/webhook/whatsapp', [ChatbotWebhookController::class, 'whatsapp'])
    ->name('chatbot.webhook.whatsapp');
