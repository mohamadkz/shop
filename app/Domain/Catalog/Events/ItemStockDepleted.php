<?php

namespace App\Domain\Catalog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemStockDepleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly int $itemId)
    {
    }
}
