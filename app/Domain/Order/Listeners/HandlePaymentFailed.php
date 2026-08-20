<?php

namespace App\Domain\Order\Listeners;

use App\Domain\Order\Actions\CancelOrder;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Events\PaymentFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Note: order line items were already decremented from stock by Catalog.
 * Cancelling here dispatches OrderCancelled, which Catalog listens to in
 * order to restock the reserved quantities (see
 * Catalog\Listeners\RestockOnOrderCancelled).
 */
class HandlePaymentFailed implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function __construct(private readonly CancelOrder $cancelOrder)
    {
    }

    public function handle(PaymentFailed $event): void
    {
        $order = Order::find($event->orderId);

        if ($order) {
            ($this->cancelOrder)($order, "payment_failed: {$event->reason}");
        }
    }
}
