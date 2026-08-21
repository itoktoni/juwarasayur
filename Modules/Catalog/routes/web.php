<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\BrandController;
use Modules\Catalog\Http\Controllers\CategoryController;
use Modules\Catalog\Http\Controllers\ProductController;
use Modules\Catalog\Http\Controllers\SatuanController;
use Modules\Catalog\Http\Controllers\TagController;

Route::auto('/catalog/brand', BrandController::class, ['name' => 'catalog-brand']);
Route::auto('/catalog/satuan', SatuanController::class, ['name' => 'catalog-satuan']);
Route::auto('/catalog/category', CategoryController::class, ['name' => 'catalog-category']);
Route::auto('/catalog/tag', TagController::class, ['name' => 'catalog-tag']);
Route::auto('/catalog/product', ProductController::class, ['name' => 'catalog-product']);
