<?php

namespace App\Domain\Payment\Services\Gateways;

use App\Domain\Payment\DTOs\GatewayVerifyResultData;
use App\Domain\Payment\Services\Contracts\PaymentGatewayContract;
use App\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZarinpalGateway implements PaymentGatewayContract
{
    private const REQUEST_URL = 'https://api.zarinpal.com/pg/v4/payment/request.json';
    private const VERIFY_URL = 'https://api.zarinpal.com/pg/v4/payment/verify.json';
    private const STARTPAY_URL = 'https://www.zarinpal.com/pg/StartPay/';

    public function __construct(
        private readonly string $merchantId,
        private readonly bool $sandbox = false,
    ) {
    }

    public function requestPayment(float $amount, string $callbackUrl, string $description, string $orderNumber): string
    {
        $response = Http::timeout(15)->post(self::REQUEST_URL, [
            'merchant_id'  => $this->merchantId,
            // Zarinpal expects Rial as an integer; never trust/round on the client.
            'amount'       => (int) round($amount),
            'callback_url' => $callbackUrl,
            'description'  => $description,
            'metadata'     => ['order_id' => $orderNumber],
        ]);

        $data = $response->json('data');

        if (! $response->successful() || empty($data['authority']) || ($data['code'] ?? null) !== 100) {
            Log::error('Zarinpal payment request failed', ['response' => $response->json()]);

            throw new DomainException('خطا در اتصال به درگاه پرداخت.', 502, 'gateway_unavailable');
        }

        return $data['authority'];
    }

    public function redirectUrl(string $authority): string
    {
        return self::STARTPAY_URL . $authority;
    }

    public function verify(string $authority, float $amount): GatewayVerifyResultData
    {
        $response = Http::timeout(15)->post(self::VERIFY_URL, [
            'merchant_id' => $this->merchantId,
            'amount'      => (int) round($amount),
            'authority'   => $authority,
        ]);

        $data = $response->json('data');
        $code = $data['code'] ?? null;

        // 100 = fresh success, 101 = already verified (still a success case,
        // handled idempotently by the caller via Payment.status check).
        if (in_array($code, [100, 101], true)) {
            return new GatewayVerifyResultData(
                success: true,
                refId: (string) ($data['ref_id'] ?? ''),
                maskedCardPan: $data['card_pan'] ?? null,
            );
        }

        Log::warning('Zarinpal verification failed', ['authority' => $authority, 'response' => $response->json()]);

        return new GatewayVerifyResultData(
            success: false,
            failureReason: "zarinpal_code_{$code}",
        );
    }
}
