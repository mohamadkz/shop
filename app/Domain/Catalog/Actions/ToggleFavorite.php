<?php

namespace App\Domain\Catalog\Actions;

use App\Domain\Catalog\Models\Favorite;
use App\Domain\Catalog\Models\Item;

class ToggleFavorite
{
    /**
     * @return bool true if the item is now favorited, false if it was removed
     */
    public function __invoke(Item $item, int $userId): bool
    {
        $favorite = Favorite::where('item_id', $item->id)
            ->where('user_id', $userId)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return false;
        }

        Favorite::create([
            'item_id' => $item->id,
            'user_id' => $userId,
        ]);

        return true;
    }
}
