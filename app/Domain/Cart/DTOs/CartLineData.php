<?php

namespace App\Domain\Cart\DTOs;

final class CartLineData
{
    public function __construct(
        public readonly int $itemId,
        public readonly int $quantity,
        public readonly float $unitPrice,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            itemId: (int) $data['item_id'],
            quantity: (int) $data['quantity'],
            unitPrice: (float) $data['price'],
        );
    }

    public function toArray(): array
    {
        return [
            'item_id'  => $this->itemId,
            'quantity' => $this->quantity,
            'price'    => $this->unitPrice,
        ];
    }

    public function lineTotal(): float
    {
        return round($this->unitPrice * $this->quantity, 2);
    }
}
