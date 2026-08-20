<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Services\Contracts\PaymentGatewayContract;
use App\Shared\Exceptions\DomainException;

class InitiatePayment
{
    public function __construct(private readonly PaymentGatewayContract $gateway)
    {
    }

    public function __invoke(Payment $payment, string $callbackUrl, string $orderNumber): string
    {
        if (! $payment->status->isPending()) {
            throw new DomainException('این پرداخت قابل شروع نیست.', 422, 'payment_not_pending');
        }

        $authority = $this->gateway->requestPayment(
            amount: (float) $payment->amount,
            callbackUrl: $callbackUrl,
            description: "پرداخت سفارش {$orderNumber}",
            orderNumber: $orderNumber,
        );

        $payment->update(['authority' => $authority]);

        return $this->gateway->redirectUrl($authority);
    }
}
