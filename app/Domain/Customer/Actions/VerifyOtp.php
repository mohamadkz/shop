<?php

namespace App\Domain\Customer\Actions;

use App\Domain\Customer\Models\User;
use App\Domain\Customer\Services\OtpService;
use App\Shared\Exceptions\DomainException;

class VerifyOtp
{
    public function __construct(private readonly OtpService $otp)
    {
    }

    /**
     * @return array{user: User, token: string}
     */
    public function __invoke(string $phone, string $code): array
    {
        $user = User::where('phone', $phone)->first();

        if (! $user) {
            throw new DomainException('کاربری با این شماره موبایل یافت نشد.', 404, 'user_not_found');
        }

        $this->otp->verify($user, $code);

        $token = $user->createToken('otp-login')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }
}
