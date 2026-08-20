<?php

namespace App\Domain\Payment\Listeners;

use App\Domain\Order\Events\OrderStockConfirmed;
use App\Domain\Payment\Actions\CreatePendingPayment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreatePaymentOnOrderStockConfirmed implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function __construct(private readonly CreatePendingPayment $createPayment)
    {
    }

    public function handle(OrderStockConfirmed $event): void
    {
        ($this->createPayment)($event->orderId, $event->userId, $event->totalAmount);
    }
}
