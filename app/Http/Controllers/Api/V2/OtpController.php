<?php

namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\SendOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\Request;


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
            phone:$request->phone,
            ip:$request->ip(),
            userAgent:$request->userAgent()
        );

        if(!$result['success'])
        {
            return response()->json([
                'success'=>false,
                'message'=>$result['message'],
                'data'=>null
            ],422);
        }

        return response()->json([
            'success'=>true,
            'message'=>$result['message'],
            'data'=>null
        ],200);
    }
}