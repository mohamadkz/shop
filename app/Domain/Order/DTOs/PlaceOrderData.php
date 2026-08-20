<?php

namespace App\Domain\Order\DTOs;

/**
 * Everything PlaceOrder needs, already computed by the orchestration layer
 * (controller) using Cart's own pricing calculator — Order never
 * recomputes or re-derives pricing itself, it just persists what it's given.
 *
 * @param OrderLineData[] $lines
 */
final class PlaceOrderData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $address,
        public readonly array $lines,
        public readonly ?string $discountCode,
        public readonly float $subtotal,
        public readonly float $discountAmount,
        public readonly float $tax,
        public readonly float $total,
        public readonly string $idempotencyKey,
    ) {
    }
}
