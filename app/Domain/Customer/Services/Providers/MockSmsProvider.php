<?php

namespace App\Domain\Customer\Services\Providers;

use App\Domain\Customer\Services\Contracts\SmsProviderContract;
use Illuminate\Support\Facades\Log;

class MockSmsProvider implements SmsProviderContract
{
    public function send(string $phone, string $message): void {
        Log::info('SMS SENT', [
            'phone' => $phone,
            'message' => $message
        ]);
    }
}
