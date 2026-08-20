<?php

namespace App\Domain\Payment\Services\Gateways;

use App\Domain\Payment\Services\Contracts\PaymentGatewayContract;
use App\Domain\Payment\DTOs\GatewayVerifyResultData;
use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGatewayContract
{
    /**
     * درخواست پرداخت تستی
     */
    public function requestPayment(
        float $amount,
        string $callbackUrl,
        string $description,
        string $orderNumber
    ): string {
        return 'TEST-' . Str::uuid();
    }

    /**
     * آدرس صفحه پرداخت تستی
     */
    public function redirectUrl(string $authority): string
    {
        return 'TEST_PAYMENT_' . $authority;
    }

    /**
     * تایید پرداخت
     */
    public function verify(
        string $authority,
        float $amount
    ): GatewayVerifyResultData {

        // در محیط تست، authority که با TEST شروع شده
        // را پرداخت موفق در نظر می‌گیریم.

        if (! str_starts_with($authority, 'TEST-')) {
            return new GatewayVerifyResultData(
                success: false,
                failureReason: 'invalid_test_authority',
            );
        }

        return new GatewayVerifyResultData(
            success: true,
            refId: 'TEST-REF-' . random_int(100000, 999999),
            maskedCardPan: '6037-****-****-1234',
        );
    }
}