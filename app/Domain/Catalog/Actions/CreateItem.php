<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\DTOs\ItemData;
use App\Domain\Catalog\Models\Item;

class CreateItem
{
    public function __invoke(ItemData $data): Item
    {
        return Item::create($data->toArray());
    }
}
