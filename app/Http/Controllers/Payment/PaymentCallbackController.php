<?php

namespace App\Http\Controllers\Payment;

use App\Domain\Payment\Actions\HandlePaymentCallback;
use App\Domain\Payment\Requests\PaymentCallbackRequest;
use App\Http\Controllers\Controller;

/**
 * Public endpoint the payment gateway redirects the browser back to.
 * No auth — the gateway isn't an authenticated API client — but every
 * business decision inside HandlePaymentCallback is re-verified
 * server-to-server with the gateway, never trusting these query params.
 */
class PaymentCallbackController extends Controller
{
    public function __invoke(PaymentCallbackRequest $request, HandlePaymentCallback $action)
    {
        $data = $request->validated();

        $payment = $action($data['Authority'], $data['Status'] === 'OK');

        $frontendBase = config('app.frontend_url', config('app.url'));

        $statusQuery = $payment->status->value === 'success' ? 'success' : 'failed';
        $redirectUrl = "{$frontendBase}/orders/{$payment->order_id}?payment={$statusQuery}";

        // return $payment->status->value === 'success'
        //     ? redirect()->away("{$frontendBase}/orders/{$payment->order_id}?payment=success")
        //     : redirect()->away("{$frontendBase}/orders/{$payment->order_id}?payment=failed");

        return response()->json([
            'status' => $payment->status->value,
            'redirect_url' => $redirectUrl,
            'message' => 'Callback processed successfully.'
        ], 200);
    }
}
