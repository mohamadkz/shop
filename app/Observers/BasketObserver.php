<?php

namespace App\Observers;

use App\Models\Basket;

class BasketObserver
{
    /**
     * Handle the Basket "created" event.
     */
    public function created(Basket $basket): void
    {
        //
    }

    /**
     * Handle the Basket "updated" event.
     */
    public function updated(Basket $basket): void
    {
        $basket->basket->calculateTotals();
    }

    /**
     * Handle the Basket "deleted" event.
     */
    public function deleted(Basket $basket): void
    {
        $basket->basket->calculateTotals();
    }

    /**
     * Handle the Basket "restored" event.
     */
    public function restored(Basket $basket): void
    {
        //
    }

    /**
     * Handle the Basket "force deleted" event.
     */
    public function forceDeleted(Basket $basket): void
    {
        //
    }
}
