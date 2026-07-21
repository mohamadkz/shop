<?php

namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\SendOtpRequest;
use App\Http\Requests\Api\V2\VerifyOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OtpController extends Controller
{
    protected OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendOtp(SendOtpRequest $request)
    {
        $result = $this->otpService->send(
            phone: $request->phone,
            ip: $request->ip(),
            userAgent: $request->userAgent()
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => null
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => null
        ], 200);
    }

    public function verifyOtp(VerifyOtpRequest $request) 
    {

        $result = $this->otpService->verify(
            phone: $request->phone,
            code: $request->code
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 422);
        }

        $user = $result['data'];

        Auth::login($user);

        $token = $user->createToken(
            'auth-token'
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'ورود موفق بود.',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }
}
