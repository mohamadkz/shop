<?php

namespace App\Domain\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Payment\Models\Payment;

class PaymentSucceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $paymentId,
        public readonly int $orderId,
        public readonly int $userId,
        public readonly float $amount,
    ) {}

    public static function fromPayment(Payment $payment): self
    {
        return new self(
            paymentId: $payment->id,
            orderId: $payment->order_id,
            userId: $payment->order->user_id,
            amount: (float) $payment->amount,
        );
    }
}
