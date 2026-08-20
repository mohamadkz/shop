<?php

namespace App\Domain\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCancelled
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly string $orderUuid,
        public readonly int $userId,
        public readonly string $reason,
    ) {
    }
}
