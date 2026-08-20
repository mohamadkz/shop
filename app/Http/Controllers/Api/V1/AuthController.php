<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Customer\Actions\LoginUser;
use App\Domain\Customer\Actions\Logout;
use App\Domain\Customer\Actions\RegisterUser;
use App\Shared\Http\ApiResponse;

use App\Http\Controllers\Controller;
use App\Domain\Customer\Requests\LoginRequest;
use App\Domain\Customer\Requests\RegisterRequest;
use App\Domain\Customer\Resources\UserResource;
use App\Domain\Customer\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUser $action)
    {
        $data = $request->validated();

        $user = $action($data['name'], $data['email'], $data['phone'], $data['password']);

        return ApiResponse::success(new UserResource($user), 'ثبت نام با موفقیت انجام شد', 201);
    }

    public function login(LoginRequest $request, LoginUser $action)
    {
        $data = $request->validated();

        $result = $action($data['email'], $data['password'], $request->userAgent());

        return ApiResponse::success([
            'user'  => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'ورود موفقیت آمیز بود');
    }

    public function logout(Request $request, Logout $action)
    {
        $action($request->user());

        return ApiResponse::success(null, 'خروج با موفقیت انجام شد');
    }

    public function user(Request $request)
    {
        return ApiResponse::success(new UserResource($request->user()));
    }
}
