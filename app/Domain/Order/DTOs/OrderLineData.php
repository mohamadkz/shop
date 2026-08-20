<?php

namespace App\Domain\Order\DTOs;

final class OrderLineData
{
    public function __construct(
        public readonly int $itemId,
        public readonly string $itemName,
        public readonly int $quantity,
        public readonly float $unitPrice,
    ) {
    }

    public function lineTotal(): float
    {
        return round($this->unitPrice * $this->quantity, 2);
    }
}
