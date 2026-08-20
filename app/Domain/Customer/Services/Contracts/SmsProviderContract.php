<?php

namespace App\Domain\Customer\Services\Contracts;

interface SmsProviderContract
{
    public function send(string $phone, string $message): void;
}
