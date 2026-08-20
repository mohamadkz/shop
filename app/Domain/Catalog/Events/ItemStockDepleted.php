<?php

namespace App\Domain\Catalog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched whenever an item's stock reaches zero after a decrement.
 * Consumed only within Catalog today (e.g. to hide the item from listings /
 * notify a merchandiser), kept as a domain event for future extension.
 */
class ItemStockDepleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly int $itemId)
    {
    }
}
