<?php

namespace App\Domain\Catalog\Listeners;

use App\Domain\Catalog\Actions\DecrementStockForOrder;
use App\Domain\Order\Events\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * The only place Catalog "knows about" Order — by listening to its public
 * event. Catalog never calls Order\Actions\* or Order\Models\* directly.
 */
class DecrementStockOnOrderPlaced implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'stock-reservations';
    public int $tries = 5;
    public array $backoff = [2, 5, 10, 30, 60];

    public function __construct(private readonly DecrementStockForOrder $decrementStock)
    {
    }

    public function handle(OrderPlaced $event): void
    {
        ($this->decrementStock)($event->orderId, $event->orderUuid, $event->lines);
    }
}
