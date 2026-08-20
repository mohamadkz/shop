<?php

namespace App\Domain\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched by Order after it moves an order from Pending to
 * AwaitingPayment (i.e. once Catalog has confirmed stock reservation).
 * Payment listens to this to create its own Pending Payment record.
 */
class OrderStockConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly string $orderUuid,
        public readonly int $userId,
        public readonly float $totalAmount,
    ) {
    }
}
