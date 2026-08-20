<?php

namespace App\Domain\Cart\DTOs;

/**
 * Fully-priced, read-only view of a user's cart. This is what's returned
 * to the client and what the checkout orchestration hands to Order\PlaceOrder.
 *
 * @param CartLineData[] $lines
 */
final class CartSnapshotData
{
    public function __construct(
        public readonly array $lines,
        public readonly ?string $discountCode,
        public readonly float $subtotal,
        public readonly float $discountAmount,
        public readonly float $tax,
        public readonly float $total,
    ) {
    }

    public function isEmpty(): bool
    {
        return empty($this->lines);
    }

    public function toArray(): array
    {
        return [
            'lines'           => array_map(fn (CartLineData $l) => $l->toArray(), $this->lines),
            'discount_code'   => $this->discountCode,
            'subtotal'        => $this->subtotal,
            'discount_amount' => $this->discountAmount,
            'tax'             => $this->tax,
            'total'           => $this->total,
        ];
    }
}
