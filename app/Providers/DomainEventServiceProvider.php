<?php

namespace App\Providers;

use App\Domain\Catalog\Listeners\DecrementStockOnOrderPlaced;
use App\Domain\Catalog\Listeners\RestockOnOrderCancelled;
use App\Domain\Cart\Listeners\ClearCartOnOrderPlaced;
use App\Domain\Order\Events\OrderCancelled;
use App\Domain\Order\Events\OrderPaid;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Events\OrderStockConfirmed;
use App\Domain\Order\Listeners\HandlePaymentFailed;
use App\Domain\Order\Listeners\HandlePaymentSucceeded;
use App\Domain\Order\Listeners\HandleStockDecremented;
use App\Domain\Order\Listeners\HandleStockReservationFailed;
use App\Domain\Payment\Events\PaymentFailed;
use App\Domain\Payment\Events\PaymentSucceeded;
use App\Domain\Payment\Listeners\CreatePaymentOnOrderStockConfirmed;
use App\Domain\Payment\Listeners\VoidPaymentOnOrderCancelled;
use App\Domain\Catalog\Events\StockDecremented;
use App\Domain\Catalog\Events\StockReservationFailed;
use App\Domain\Payment\Listeners\SendPaymentSuccessEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * The single map of the whole cross-domain saga. Reading this file top to
 * bottom tells the full story of checkout -> stock -> payment without
 * having to trace method calls through the domains themselves.
 *
 *   Order::PlaceOrder            -> OrderPlaced
 *     -> Cart::ClearCartOnOrderPlaced        (clears the Redis cart)
 *     -> Catalog::DecrementStockOnOrderPlaced (locks + decrements stock)
 *
 *   Catalog::DecrementStockForOrder -> StockDecremented | StockReservationFailed
 *     -> Order::HandleStockDecremented        -> order becomes AwaitingPayment, dispatches OrderStockConfirmed
 *     -> Order::HandleStockReservationFailed   -> order becomes Cancelled, dispatches OrderCancelled
 *
 *   Order::ConfirmOrderStock -> OrderStockConfirmed
 *     -> Payment::CreatePaymentOnOrderStockConfirmed (creates the pending Payment row)
 *
 *   Payment::HandlePaymentCallback -> PaymentSucceeded | PaymentFailed
 *     -> Order::HandlePaymentSucceeded -> order becomes Paid, dispatches OrderPaid
 *     -> Order::HandlePaymentFailed    -> order becomes Cancelled, dispatches OrderCancelled
 *
 *   Order::CancelOrder -> OrderCancelled
 *     -> Catalog::RestockOnOrderCancelled     (undoes a completed stock reservation, if any)
 *     -> Payment::VoidPaymentOnOrderCancelled (voids a still-pending payment, if any)
 */
class DomainEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderPlaced::class => [
            ClearCartOnOrderPlaced::class,
            DecrementStockOnOrderPlaced::class,
        ],

        StockDecremented::class => [
            HandleStockDecremented::class,
        ],

        StockReservationFailed::class => [
            HandleStockReservationFailed::class,
        ],

        OrderStockConfirmed::class => [
            CreatePaymentOnOrderStockConfirmed::class,
        ],

        PaymentSucceeded::class => [
            HandlePaymentSucceeded::class,
            SendPaymentSuccessEmail::class,
        ],

        PaymentFailed::class => [
            HandlePaymentFailed::class,
        ],

        OrderCancelled::class => [
            RestockOnOrderCancelled::class,
            VoidPaymentOnOrderCancelled::class,
        ],

        OrderPaid::class => [
            // e.g. Order::SendOrderConfirmationEmail, analytics, etc.
        ],
    ];

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
