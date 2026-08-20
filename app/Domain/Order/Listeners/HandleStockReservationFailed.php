<?php

namespace App\Domain\Order\Listeners;

use App\Domain\Catalog\Events\StockReservationFailed;
use App\Domain\Order\Actions\CancelOrder;
use App\Domain\Order\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleStockReservationFailed implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function __construct(private readonly CancelOrder $cancelOrder)
    {
    }

    public function handle(StockReservationFailed $event): void
    {
        $order = Order::find($event->orderId);

        if ($order) {
            ($this->cancelOrder)($order, "stock_unavailable: {$event->reason}");
        }
    }
}
