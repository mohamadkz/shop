<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BasketController;


// Public reads — anyone can browse the catalog
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{item}', [ItemController::class, 'show']);

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{item}', [ItemController::class, 'update']);
    Route::patch('/items/{item}', [ItemController::class, 'update']);
    Route::delete('/items/{item}', [ItemController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/basket', [BasketController::class, 'index']);
    Route::post('/basket/items', [BasketController::class, 'store']);
    Route::patch('/basket/items/{itemId}', [BasketController::class, 'update']);
    Route::delete('/basket/items/{itemId}', [BasketController::class, 'destroy']);
    Route::post('/basket/discount', [BasketController::class, 'applyDiscount']);
    Route::delete('/basket/discount', [BasketController::class, 'removeDiscount']);
    Route::post('/basket/checkout', [BasketController::class, 'checkout'])
        ->middleware('throttle:10,1');
});
