<?php

namespace App\Domain\Order\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * The order-placement saga's starting event. Carries only primitive data
 * (IDs, scalars) — Cart and Catalog react to this without ever touching
 * Order's Eloquent models.
 *
 * @param array<int, array{item_id:int, quantity:int}> $lines
 */
class OrderPlaced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly string $orderUuid,
        public readonly int $userId,
        public readonly array $lines,
    ) {
    }
}
