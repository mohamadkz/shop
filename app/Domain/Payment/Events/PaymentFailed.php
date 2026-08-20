<?php

namespace App\Domain\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $paymentId,
        public readonly int $orderId,
        public readonly int $userId,
        public readonly string $reason,
    ) {
    }
}
