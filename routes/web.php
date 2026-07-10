<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\Order;
use App\Models\Payment;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

// Route::get('/test-order', function () {

//     DB::beginTransaction();

//     try {

//         $order = Order::create([
//             'user_id' => 1,
//             'order_number' => 'ORD-' . rand(100000, 999999),
//             'total_price' => 850000,
//             'discount' => 50000,
//             'final_price' => 800000,
//             'status' => OrderStatus::Pending,
//         ]);

//         $payment = Payment::create([
//             'order_id' => $order->id,
//             'gateway' => 'zarinpal',
//             'amount' => $order->final_price,
//             'status' => PaymentStatus::Pending,
//         ]);

//         DB::commit();

//         return [
//             'message' => 'success',
//             'order' => $order,
//             'payment' => $payment,
//         ];
//     } catch (\Exception $e) {

//         DB::rollBack();

//         return $e->getMessage();
//     }
// });
