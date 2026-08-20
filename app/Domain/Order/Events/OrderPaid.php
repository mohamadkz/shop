<?php

namespace App\Domain\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly string $orderUuid,
        public readonly int $userId,
    ) {
    }
}
