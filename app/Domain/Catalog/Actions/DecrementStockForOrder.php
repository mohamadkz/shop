<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Events\ItemStockDepleted;
use App\Domain\Catalog\Events\StockDecremented;
use App\Domain\Catalog\Events\StockReservationFailed;
use App\Domain\Catalog\Models\Item;
use App\Shared\Services\DistributedLock;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Reacts to Order\Events\OrderPlaced (via a Listener) to reserve stock for
 * an order's line items. This is Catalog's own write, guarded independently
 * by a DB transaction + row locks — Catalog never reads Order's tables.
 *
 * Idempotent: a `catalog_stock_reservations` row (unique on order_id) is
 * inserted first; a duplicate-key failure means this order was already
 * reserved (e.g. a redelivered queue job) and we simply re-confirm success
 * without double-decrementing.
 *
 * @param  array<int, array{item_id:int, quantity:int}>  $lines
 */
class DecrementStockForOrder
{
    public function __construct(private readonly DistributedLock $lock)
    {
    }

    public function __invoke(int $orderId, string $orderUuid, array $lines): void
    {
        if (empty($lines)) {
            return;
        }

        // Order-level lock: prevents two concurrent deliveries of the same
        // event (e.g. queue redelivery) from racing each other.
        $this->lock->run("stock-reservation:{$orderId}", function () use ($orderId, $orderUuid, $lines) {
            $alreadyReserved = ! $this->markReservationStarted($orderId, $lines);

            if ($alreadyReserved) {
                // Idempotent replay: assume prior attempt already resolved
                // the reservation and re-announce success so listeners that
                // missed the first event still progress the saga.
                StockDecremented::dispatch($orderId, $orderUuid);

                return;
            }

            try {
                DB::transaction(function () use ($orderId, $orderUuid, $lines) {
                    $itemIds = array_column($lines, 'item_id');

                    // Lock rows for the duration of the transaction —
                    // concurrent checkouts for the same item serialize here.
                    $items = Item::whereIn('id', $itemIds)
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('id');

                    foreach ($lines as $line) {
                        $item = $items->get($line['item_id']);

                        if (! $item || ! $item->hasSufficientStock($line['quantity'])) {
                            throw new \RuntimeException(
                                "Insufficient stock for item #{$line['item_id']}"
                            );
                        }
                    }

                    foreach ($lines as $line) {
                        $item = $items->get($line['item_id']);
                        $item->decrement('stock', $line['quantity']);

                        if ($item->fresh()->stock <= 0) {
                            ItemStockDepleted::dispatch($item->id);
                        }
                    }
                });
            } catch (\RuntimeException $e) {
                Log::warning('Stock reservation failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);

                DB::table('catalog_stock_reservations')->where('order_id', $orderId)->delete();

                StockReservationFailed::dispatch($orderId, $orderUuid, $e->getMessage());

                return;
            }

            StockDecremented::dispatch($orderId, $orderUuid);
        }, ttlSeconds: 15, waitSeconds: 10);
    }

    /**
     * Attempts to claim the reservation slot for this order. Returns false
     * if another delivery of the same event already claimed it.
     */
    private function markReservationStarted(int $orderId, array $lines): bool
    {
        try {
            // reserved_lines is stored here (not re-derived from Order's
            // tables) so RestockItems can undo the reservation later
            // without Catalog ever reading Order's models.
            DB::table('catalog_stock_reservations')->insert([
                'order_id'      => $orderId,
                'reserved_lines' => json_encode($lines),
                'created_at'    => now(),
            ]);

            return true;
        } catch (QueryException $e) {
            // Unique constraint violation on order_id => already reserved.
            return false;
        }
    }
}
