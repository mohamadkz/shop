<?php

namespace App\Domain\Cart\Resources;

use App\Domain\Cart\DTOs\CartSnapshotData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CartSnapshotData */
class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var CartSnapshotData $cart */
        $cart = $this->resource;

        return [
            'lines' => array_map(fn($line) => [
                'item_id'    => $line->itemId,
                'quantity'   => $line->quantity,
                'unit_price' => $line->unitPrice,
                'line_total' => $line->lineTotal(),
            ], $cart->lines),
            'discount_code'   => $cart->discountCode,
            'subtotal'        => $cart->subtotal,
            'discount_amount' => $cart->discountAmount,
            'tax'             => $cart->tax,
            'total'           => $cart->total,
            'is_empty'        => $cart->isEmpty(),
        ];
    }
}
