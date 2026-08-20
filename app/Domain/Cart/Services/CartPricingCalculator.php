<?php

namespace App\Domain\Cart\Services;

use App\Domain\Cart\DTOs\CartLineData;
use App\Domain\Cart\DTOs\CartSnapshotData;
use App\Domain\Cart\Models\DiscountCode;

/**
 * The single source of truth for cart pricing math. Every place that needs
 * a price (cart view, checkout, order placement) must go through here so
 * subtotal/discount/tax/total are computed identically everywhere.
 */
class CartPricingCalculator
{
    private const TAX_RATE = 0.09; // 9% VAT — externalize to config in real deployments

    /**
     * @param  CartLineData[]  $lines
     */
    public function calculate(array $lines, ?DiscountCode $discount): CartSnapshotData
    {
        $subtotal = round(array_sum(array_map(fn (CartLineData $l) => $l->lineTotal(), $lines)), 2);

        $discountAmount = $discount ? $discount->calculateDiscount($subtotal) : 0.0;

        $taxableAmount = max(0, $subtotal - $discountAmount);
        $tax = round($taxableAmount * self::TAX_RATE, 2);

        $total = round($taxableAmount + $tax, 2);

        return new CartSnapshotData(
            lines: array_values($lines),
            discountCode: $discount?->code,
            subtotal: $subtotal,
            discountAmount: $discountAmount,
            tax: $tax,
            total: $total,
        );
    }
}
