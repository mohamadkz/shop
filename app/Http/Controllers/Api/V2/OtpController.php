<?php

namespace App\Http\Controllers\Api\V2;

use App\Domain\Customer\Actions\SendOtp;
use App\Domain\Customer\Actions\VerifyOtp;
use App\Domain\Customer\Resources\UserResource;
use App\Shared\Http\ApiResponse;


use App\Http\Controllers\Controller;
use App\Domain\Customer\Requests\SendOtpRequest;
use App\Domain\Customer\Requests\VerifyOtpRequest;
// use App\Services\OtpService;
use App\App\Domain\Customer\Services\OtpService;
use App\Domain\Customer\Services\OtpService as ServicesOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OtpController extends Controller
{
    protected ServicesOtpService $otpService;

    public function __construct(ServicesOtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendOtp(SendOtpRequest $request, SendOtp $action)
    {
        $data = $request->validated();

        $action($data['phone'], $request->ip(), $request->userAgent());

        return ApiResponse::success(null, 'کد تایید ارسال شد');
    }

    public function verifyOtp(VerifyOtpRequest $request, VerifyOtp $action) 
    {

        $data = $request->validated();

        $result = $action($data['phone'], $data['code']);

        return ApiResponse::success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'شماره موبایل با موفقیت تایید شد');
    }
}
