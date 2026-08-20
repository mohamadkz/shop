<?php

namespace App\Domain\Order\Actions;

use App\Domain\Order\DTOs\PlaceOrderData;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Events\OrderPlaced;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Services\OrderNumberGenerator;
use App\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Creates the Order + its OrderItem snapshot and dispatches OrderPlaced.
 * Does NOT touch stock, payments, or the cart — those are Catalog's,
 * Payment's, and Cart's own reactions to the event dispatched at the end.
 *
 * Idempotent via a client-supplied idempotency key: a duplicate request
 * (double-tap on "place order", retried request after a timeout) returns
 * the original order instead of creating a second one.
 */
class PlaceOrder
{
    public function __construct(private readonly OrderNumberGenerator $orderNumbers)
    {
    }

    public function __invoke(PlaceOrderData $data): Order
    {
        if (empty($data->lines)) {
            throw new DomainException('سبد خرید شما خالی است.', 422, 'empty_cart');
        }

        $existing = Order::where('user_id', $data->userId)
            ->where('idempotency_key', $data->idempotencyKey)
            ->first();

        if ($existing) {
            return $existing->load('items');
        }

        $order = DB::transaction(function () use ($data) {
            $order = Order::create([
                'user_id'         => $data->userId,
                'order_number'    => $this->orderNumbers->generate(),
                'idempotency_key' => $data->idempotencyKey,
                'subtotal'        => $data->subtotal,
                'discount_amount' => $data->discountAmount,
                'discount_code'   => $data->discountCode,
                'tax'             => $data->tax,
                'total_price'     => $data->total,
                'address'         => $data->address,
                'status'          => OrderStatus::Pending,
            ]);

            foreach ($data->lines as $line) {
                $order->items()->create([
                    'item_id'    => $line->itemId,
                    'item_name'  => $line->itemName,
                    'quantity'   => $line->quantity,
                    'unit_price' => $line->unitPrice,
                    'line_total' => $line->lineTotal(),
                ]);
            }

            return $order;
        });

        $order->load('items');

        // Dispatched after commit (queued listeners only pick it up once
        // the transaction is durable) — Catalog and Cart react from here.
        OrderPlaced::dispatch($order->id, $order->uuid, $order->user_id, $order->toStockLines());

        return $order;
    }
}
