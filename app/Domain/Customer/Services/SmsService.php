<?php

namespace App\Domain\Customer\Services;

use App\Domain\Customer\Services\Contracts\SmsProviderContract;

class SmsService
{
    public function __construct(private readonly SmsProviderContract $provider)
    {
    }

    public function send(string $phone, string $message): void
    {
        $this->provider->send($phone, $message);
    }
}
