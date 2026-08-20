<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Shared\Exceptions\DomainException;

/**
 * Used both as a compensating action in the saga (stock reservation failed)
 * and as a direct user-initiated cancellation. Idempotent: cancelling an
 * already-terminal order is a no-op.
 */
class CancelOrder
{
    public function __invoke(Order $order, string $reason, bool $userInitiated = false): void
    {
        if (! $order->status->isCancellable()) {
            if ($userInitiated) {
                throw new DomainException('این سفارش قابل لغو نیست.', 422, 'order_not_cancellable');
            }

            return; // already terminal — idempotent no-op for saga replays
        }

        $order->update(['status' => OrderStatus::Cancelled]);

        OrderCancelled::dispatch($order->id, $order->uuid, $order->user_id, $reason);
    }
}
