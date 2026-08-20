<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Events\OrderStockConfirmed;
use App\Domain\Order\Models\Order;

/**
 * Moves an order from Pending to AwaitingPayment once Catalog has confirmed
 * stock was successfully reserved. Idempotent: re-running on an order
 * that's already past Pending is a no-op (guards against duplicate event
 * delivery).
 */
class ConfirmOrderStock
{
    public function __invoke(int $orderId): void
    {
        $order = Order::find($orderId);

        if (! $order || ! $order->status->isPending()) {
            return;
        }

        $order->update(['status' => OrderStatus::AwaitingPayment]);

        OrderStockConfirmed::dispatch($order->id, $order->uuid, $order->user_id, (float) $order->total_price);
    }
}
