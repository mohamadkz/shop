<?php

namespace App\Domain\Order\Listeners;

use App\Domain\Order\Actions\MarkOrderPaid;
use App\Domain\Payment\Events\PaymentSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandlePaymentSucceeded implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'default';

    public function __construct(private readonly MarkOrderPaid $markPaid)
    {
    }

    public function handle(PaymentSucceeded $event): void
    {
        ($this->markPaid)($event->orderId);
    }
}
