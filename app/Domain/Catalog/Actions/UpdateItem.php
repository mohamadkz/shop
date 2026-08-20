<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\DTOs\ItemData;
use App\Domain\Catalog\Models\Item;

class UpdateItem
{
    public function __invoke(Item $item, ItemData $data): Item
    {
        $item->update($data->toArray());

        return $item->refresh();
    }
}
