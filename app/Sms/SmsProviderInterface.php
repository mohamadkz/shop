<?php

namespace App\Sms;


interface SmsProviderInterface
{
    public function send(
        string $phone,
        string $message
    ): bool;
}