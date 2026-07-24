<?php

namespace App\Observers;

use App\Models\BasketItem;

class BasketItemObserver
{
    /**
     * Handle the Basket "created" event.
     */
    public function created(BasketItem $basketItem): void
    {
        $basketItem->basket->calculateTotals();
    }

    /**
     * Handle the Basket "updated" event.
     */
    public function updated(BasketItem $basketItem): void
    {
        $basketItem->basket->calculateTotals();
    }

    /**
     * Handle the Basket "deleted" event.
     */
    public function deleted(BasketItem $basketItem): void
    {
        $basketItem->basket->calculateTotals();
    }

    /**
     * Handle the Basket "restored" event.
     */
    public function restored(BasketItem $basketItem): void
    {
        //
    }

    /**
     * Handle the Basket "force deleted" event.
     */
    public function forceDeleted(BasketItem $basketItem): void
    {
        //
    }
}
