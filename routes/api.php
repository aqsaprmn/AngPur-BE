<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Open Routes
Route::post("register", [AuthController::class, "register"]);
Route::post("login", [AuthController::class, "login"]);

Route::group([
    'middleware' => 'api',
    // 'prefix' => 'auth'
], function ($router) {
    // AUTH
    Route::post('logout', [AuthController::class, "logout"]);
    Route::post('refresh', [AuthController::class, "refresh"]);
    Route::post('me', [AuthController::class, "me"]);
    // END AUTH

    // USER
    Route::resource('user', UserController::class);
    // END USER

    // USER
    Route::resource('product', ProductController::class);
    // END USER

    // USER
    Route::resource('shipping', ShippingController::class);
    // END USER

    // USER
    Route::resource('payment', PaymentController::class);
    // END USER

    // ORDER
    Route::get('order', [OrderController::class, "index"]);
    Route::post('order', [OrderController::class, "store"]);
    Route::patch('order/{uuid}', [OrderController::class, "update"]);
    Route::patch('order/cancel/{uuid}', [OrderController::class, "cancel"]);
    // END ORDER
});
