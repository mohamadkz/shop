<?php

namespace App\Domain\Order\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:500'],
            // Client-generated idempotency key (e.g. a UUID created once per
            // checkout attempt and reused on retry) — prevents duplicate
            // orders from double-taps or network retries.
            'idempotency_key' => ['required', 'string', 'max:64'],
        ];
    }
}
