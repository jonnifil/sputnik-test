<?php

use Illuminate\Support\Facades\Route;

Route::apiResource('product', App\Http\Controllers\Api\ProductController::class);
Route::get('prices', [App\Http\Controllers\Api\ProductController::class, 'index']);
