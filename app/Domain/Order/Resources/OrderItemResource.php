<?php

namespace App\Domain\Order\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Order\Models\OrderItem */
class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'item_id'    => $this->item_id,
            'item_name'  => $this->item_name,
            'quantity'   => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'line_total' => (float) $this->line_total,
        ];
    }
}
