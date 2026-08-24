<?php

use Illuminate\Support\Facades\Route;
use Modules\Chatbot\Http\Controllers\WebChatController;

/*
|--------------------------------------------------------------------------
| AJAX chat — tanpa session (group 'chat'), identitas via cookie
|--------------------------------------------------------------------------
*/

Route::post('/chat/send', [WebChatController::class, 'send'])->name('chat.web.send');

// Wizard checkout (deterministik, bukan lewat AI)
Route::get('/chat/products', [WebChatController::class, 'products'])->name('chat.web.products');
Route::get('/chat/cart', [WebChatController::class, 'cart'])->name('chat.web.cart');
Route::post('/chat/cart/add', [WebChatController::class, 'addItems'])->name('chat.web.addItems');
Route::post('/chat/cart/remove', [WebChatController::class, 'removeItem'])->name('chat.web.removeItem');
Route::post('/chat/checkout/start', [WebChatController::class, 'start'])->name('chat.web.checkoutStart');
Route::post('/chat/checkout/details', [WebChatController::class, 'shippingDetails'])->name('chat.web.details');
Route::get('/chat/checkout/contact', [WebChatController::class, 'contact'])->name('chat.web.contact');
Route::get('/chat/checkout/cod-locations', [WebChatController::class, 'codLocations'])->name('chat.web.codLocations');
Route::post('/chat/checkout/shipping', [WebChatController::class, 'setShipping'])->name('chat.web.shipping');
Route::post('/chat/checkout/pay', [WebChatController::class, 'pay'])->name('chat.web.pay');
