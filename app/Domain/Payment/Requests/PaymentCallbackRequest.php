<?php

namespace App\Domain\Payment\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates only the SHAPE of the gateway's callback payload. Values are
 * never trusted for business decisions — HandlePaymentCallback always
 * re-verifies server-to-server with the gateway.
 */
class PaymentCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public callback endpoint — no user session, protected by signed verification instead
    }

    public function rules(): array
    {
        return [
            'Authority' => ['required', 'string', 'max:64'],
            'Status'    => ['required', 'string', 'in:OK,NOK'],
        ];
    }
}
