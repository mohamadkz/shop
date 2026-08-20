<?php

namespace App\Domain\Order\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Order\Models\Order */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->uuid,
            'order_number'    => $this->order_number,
            'status'          => $this->status->value,
            'status_label'    => $this->status->label(),
            'address'         => $this->address,
            'subtotal'        => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'discount_code'   => $this->discount_code,
            'tax'             => (float) $this->tax,
            'total_price'     => (float) $this->total_price,
            'items'           => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
