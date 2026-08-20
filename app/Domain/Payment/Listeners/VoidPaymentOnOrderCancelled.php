<?php

namespace App\Domain\Payment\Listeners;

use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * If an order is cancelled while its Payment is still Pending (e.g. the
 * order was manually cancelled by the user before paying), void the
 * payment so it can't be initiated/paid afterward.
 */
class VoidPaymentOnOrderCancelled implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function handle(OrderCancelled $event): void
    {
        Payment::where('order_id', $event->orderId)
            ->pending()
            ->update(['status' => PaymentStatus::Cancelled]);
    }
}
