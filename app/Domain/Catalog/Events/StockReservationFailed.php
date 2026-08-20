<?php

namespace App\Domain\Catalog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when Catalog cannot reserve stock for one or more lines of an
 * order (compensating trigger for Order to cancel itself — saga pattern).
 */
class StockReservationFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly string $orderUuid,
        public readonly string $reason,
    ) {
    }
}
