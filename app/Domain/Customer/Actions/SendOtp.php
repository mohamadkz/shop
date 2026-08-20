<?php

namespace App\Domain\Customer\Actions;

use App\Domain\Customer\Models\User;
use App\Domain\Customer\Services\OtpService;
use App\Shared\Exceptions\DomainException;

class SendOtp
{
    public function __construct(private readonly OtpService $otp)
    {
    }

    public function __invoke(string $phone, ?string $ip, ?string $userAgent): void
    {
        $user = User::where('phone', $phone)->first();

        if (! $user) {
            throw new DomainException('کاربری با این شماره موبایل یافت نشد.', 404, 'user_not_found');
        }

        $this->otp->send($user, $ip, $userAgent);
    }
}
