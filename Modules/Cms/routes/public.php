<?php

/*
|--------------------------------------------------------------------------
| Public (tanpa auth) — halaman frontend CMS
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\PublicController;

Route::get('/blog', [PublicController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [PublicController::class, 'post'])->name('blog.post');
Route::get('/blog/category/{slug}', [PublicController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{slug}', [PublicController::class, 'tag'])->name('blog.tag');

Route::get('/page/{slug}', [PublicController::class, 'page'])->name('page.show');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'postContact'])->name('contact.post');
Route::get('/contact/captcha', [PublicController::class, 'captchaImage'])->name('captcha.contact');
Route::get('/search', [PublicController::class, 'search'])->name('search');
