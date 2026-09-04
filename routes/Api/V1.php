<?php

use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V2\OtpController;
use App\Http\Controllers\Payment\PaymentCallbackController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BasketController;



Route::get('/items', [ItemController::class, 'index'])->name('index');
Route::get('/items/{item}', [ItemController::class, 'show'])->name('show');
Route::get('/items/{item}/comments', [CommentController::class, 'index'])->name('comments.index');

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user');

    // Route::post('/items', [ItemController::class, 'store']);
    // Route::put('/items/{item}', [ItemController::class, 'update']);
    // Route::patch('/items/{item}', [ItemController::class, 'update']);
    // Route::delete('/items/{item}', [ItemController::class, 'destroy']);

    Route::post('/items/{item}/comments', [CommentController::class, 'store'])->middleware('throttle:20,1')->name('comments.store');

    Route::post('/items/{item}/favorite', [FavoriteController::class, 'toggle'])->name('favorite.toggle');
});

Route::middleware(['auth:sanctum', 'ability:items:write'])->group(function () {
    Route::post('/items', [ItemController::class, 'store'])->name('store');
    Route::match(['put', 'patch'], '/items/{item}', [ItemController::class, 'update'])->name('update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('destroy');
});

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

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

Route::middleware('auth:sanctum')->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/items', [CartController::class, 'store'])->name('items.store');
    Route::patch('/items/{itemId}', [CartController::class, 'update'])->name('items.update');
    Route::delete('/items/{itemId}', [CartController::class, 'destroy'])->name('items.destroy');
    Route::post('/discount', [CartController::class, 'applyDiscount'])->name('discount.apply');
    Route::delete('/discount', [CartController::class, 'removeDiscount'])->name('discount.remove');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:10,1')->name('checkout.store');

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order:uuid}', [OrderController::class, 'show'])->name('show');
    });
});

Route::middleware(['auth:sanctum', 'throttle:10,1'])
    ->post('/orders/{order:uuid}/payment/initiate', [PaymentController::class, 'initiate'])
    ->name('orders.payment.initiate');

Route::get('/payment/callback', PaymentCallbackController::class)
    ->name('payment.callback')
    ->middleware('throttle:30,1');
