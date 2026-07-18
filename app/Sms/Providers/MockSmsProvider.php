<?php

namespace App\Sms\Providers;

use App\Sms\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

class MockSmsProvider implements SmsProviderInterface
{
    public function send(
        string $phone,
        string $message
    ): bool
    {
        Log::info('SMS SENT', [
            'phone'=>$phone,
            'message'=>$message
        ]);
        return true;
    }
}