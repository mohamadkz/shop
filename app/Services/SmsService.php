<?php


namespace App\Services;

use App\Sms\SmsProviderInterface;

class SmsService
{
    protected SmsProviderInterface $provider;

    public function __construct(
        SmsProviderInterface $provider
    )
    {
        $this->provider = $provider;
    }

    public function send(
        string $phone,
        string $message
    ): bool
    {
        return $this->provider->send(
            $phone,
            $message
        );
    }
}