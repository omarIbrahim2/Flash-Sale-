<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HoldController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\ProductController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/products/{product_id}' , [ProductController::class, 'index']);
Route::post('/holds/{product_id}/{qty}' , HoldController::class);
Route::post('orders/{hold_id}/{product_id}' , OrderController::class);
Route::post('payments/webhook' , [PaymentWebhookController::class , 'handle']);

