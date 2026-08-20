<?php

namespace App\Domain\Customer\Services\Providers;

use App\Domain\Customer\Services\Contracts\SmsProviderContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KavenegarProvider implements SmsProviderContract
{
    public function __construct(private readonly string $apiKey, private readonly string $sender)
    {
    }

    public function send(string $phone, string $message): void
    {
        $response = Http::timeout(10)->asForm()->post("https://api.kavenegar.com/v1/{$this->apiKey}/sms/send.json", [
            'receptor' => $phone,
            'sender'   => $this->sender,
            'message'  => $message,
        ]);

        if (! $response->successful()) {
            Log::error('SMS delivery failed', ['phone' => $phone, 'response' => $response->body()]);
        }
    }
}
