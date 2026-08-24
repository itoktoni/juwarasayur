<?php

use Illuminate\Support\Facades\Route;
use Modules\Faq\Http\Controllers\FaqController;

// CRUD FAQ (knowledge base chatbot) — area admin.
Route::auto('/faq', FaqController::class, ['name' => 'faq']);
