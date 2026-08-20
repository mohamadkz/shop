<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Item;

class DeleteItem
{
    public function __invoke(Item $item): void
    {
        // Soft delete: preserves referential integrity for historical
        // OrderItem snapshots that reference this item_id.
        $item->delete();
    }
}
