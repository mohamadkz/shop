<?php

namespace App\Domain\Catalog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched by Catalog after it successfully reserves (decrements) stock
 * for an order's line items. Carries only primitive/scalar data — never
 * Eloquent model references — so other domains cannot accidentally couple
 * to Catalog's internal model shape.
 */
class StockDecremented
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly string $orderUuid,
    ) {
    }
}
