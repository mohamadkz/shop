<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\OtpController;

    Route::post('/send-otp', [OtpController::class, 'sendOtp'])->middleware('throttle:3,1')->name('send');
    Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('verify');

    

