<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Actions\InitiatePayment;
use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Requests\InitiatePaymentRequest;
use App\Http\Controllers\Controller;
use App\Shared\Http\ApiResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function initiate(InitiatePaymentRequest $request, Order $order, InitiatePayment $action)
    {
        if ($order->user_id !== $request->user()->id) {
            return ApiResponse::error('این سفارش متعلق به شما نیست.', 403, [], 'forbidden');
        }

        // Controller reads Order (for its order_number/uuid) and Payment
        // (to initiate it) — orchestration across domains at the HTTP
        // layer only; Payment's own Action never reads Order's model.
        $payment = Payment::where('order_id', $order->id)->pending()->firstOrFail();

        $redirectUrl = $action(
            $payment,
            callbackUrl: route('payment.callback'),
            orderNumber: $order->order_number,
        );

        return ApiResponse::success(['redirect_url' => $redirectUrl]);
    }
}
