<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Models\Order;

/**
 * Reacts (via a Listener) to Payment\Events\PaymentSucceeded. Idempotent:
 * an order that's already Paid or beyond ignores a duplicate/replayed event.
 */
class MarkOrderPaid
{
    public function __invoke(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order || $order->status !== OrderStatus::AwaitingPayment) {
            return;
        }

        $order->update(['status' => OrderStatus::Paid]);

        OrderPaid::dispatch($order->id, $order->uuid, $order->user_id);
    }
}
