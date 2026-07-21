<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\OtpController;

    Route::post('/send-otp', [OtpController::class, 'sendOtp']);
    Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);

    

