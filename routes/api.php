<?php

use App\Modules\Catalog\Http\Controllers\Api\ProductController;
use App\Modules\Ordering\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::patch('/orders/{id}/pay', [OrderController::class, 'pay']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
