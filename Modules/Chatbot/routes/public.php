<?php

use Illuminate\Support\Facades\Route;
use Modules\Chatbot\Http\Controllers\ChatbotWebhookController;
use Modules\Chatbot\Http\Controllers\WebChatController;

/*
|--------------------------------------------------------------------------
| Web chat WhatsApp-like (guest, template berdeda dari public/admin)
|--------------------------------------------------------------------------
*/

Route::get('/chat', [WebChatController::class, 'index'])->name('chat.web.index');
Route::post('/chat/send', [WebChatController::class, 'send'])->name('chat.web.send');

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
