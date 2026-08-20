<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Item;
use App\Shared\Services\DistributedLock;
use Illuminate\Support\Facades\DB;

/**
 * Compensating action: restores stock previously reserved for an order
 * that was subsequently cancelled (e.g. payment failed). Only restocks
 * orders that actually had a completed reservation, and is idempotent —
 * running twice for the same order does not double-restock.
 *
 * @param  array<int, array{item_id:int, quantity:int}>  $lines
 */
class RestockItems
{
    public function __construct(private readonly DistributedLock $lock)
    {
    }

    public function __invoke(int $orderId, array $lines): void
    {
        if (empty($lines)) {
            return;
        }

        $this->lock->run("stock-reservation:{$orderId}", function () use ($orderId, $lines) {
            $reservation = DB::table('catalog_stock_reservations')->where('order_id', $orderId)->first();

            // No reservation row => stock was never decremented for this
            // order (e.g. reservation itself had failed) — nothing to undo.
            if (! $reservation || $reservation->restocked_at !== null) {
                return;
            }

            DB::transaction(function () use ($lines) {
                foreach ($lines as $line) {
                    Item::whereKey($line['item_id'])->lockForUpdate()->increment('stock', $line['quantity']);
                }
            });

            DB::table('catalog_stock_reservations')->where('order_id', $orderId)->update(['restocked_at' => now()]);
        }, ttlSeconds: 15, waitSeconds: 10);
    }
}
