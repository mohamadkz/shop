<?php

namespace App\Domain\Cart\Listeners;

use App\Domain\Cart\Services\CartRedisRepository;
use App\Domain\Order\Events\OrderPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Cart's only coupling to Order: reacting to its public OrderPlaced event.
 * Clearing Redis is naturally idempotent (deleting an already-deleted key
 * is a no-op), so no extra dedup bookkeeping is needed here.
 */
class ClearCartOnOrderPlaced implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function __construct(private readonly CartRedisRepository $cart)
    {
    }

    public function handle(OrderPlaced $event): void
    {
        $this->cart->clear($event->userId);
    }
}
