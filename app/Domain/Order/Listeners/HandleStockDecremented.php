<?php

namespace App\Domain\Order\Listeners;

use App\Domain\Catalog\Events\StockDecremented;
use App\Domain\Order\Actions\ConfirmOrderStock;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleStockDecremented implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function __construct(private readonly ConfirmOrderStock $confirmStock)
    {
    }

    public function handle(StockDecremented $event): void
    {
        ($this->confirmStock)($event->orderId);
    }
}
