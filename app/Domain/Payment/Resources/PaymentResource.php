<?php

namespace App\Domain\Payment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Domain\Payment\Models\Payment */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->uuid,
            'amount'         => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'status'         => $this->status->value,
            'ref_id'         => $this->ref_id,
            'paid_at'        => $this->paid_at?->toIso8601String(),
        ];
    }
}
