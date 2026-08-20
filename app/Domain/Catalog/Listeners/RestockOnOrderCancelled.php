<?php

namespace App\Domain\Catalog\Listeners;

use App\Domain\Catalog\Actions\RestockItems;
use App\Domain\Order\Events\OrderCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class RestockOnOrderCancelled implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'stock-reservations';

    public function __construct(private readonly RestockItems $restockItems)
    {
    }

    public function handle(OrderCancelled $event): void
    {
        // Catalog never reads Order's tables. The line items it decremented
        // were snapshotted into its own reservation ledger at reservation
        // time (see DecrementStockForOrder), so it can undo them here using
        // only data it owns.
        $lines = DB::table('catalog_stock_reservations')
            ->where('order_id', $event->orderId)
            ->value('reserved_lines');

        if ($lines) {
            ($this->restockItems)($event->orderId, json_decode($lines, true));
        }
    }
}
