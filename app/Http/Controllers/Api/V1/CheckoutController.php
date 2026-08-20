<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Cart\Actions\GetCart;
use App\Domain\Catalog\Models\Item;
use App\Domain\Order\Actions\PlaceOrder;
use App\Domain\Order\DTOs\OrderLineData;
use App\Domain\Order\DTOs\PlaceOrderData;
use App\Domain\Order\Requests\CheckoutRequest;
use App\Domain\Order\Resources\OrderResource;
use App\Http\Controllers\Controller;
use App\Shared\Http\ApiResponse;

/**
 * The one place in the application allowed to read from both Cart and
 * Order: HTTP controllers orchestrate across domains, domains never call
 * each other's Actions/Services directly. From here on, Cart and Catalog
 * find out about the order asynchronously via the OrderPlaced event.
 */
class CheckoutController extends Controller
{
    public function store(CheckoutRequest $request, GetCart $getCart, PlaceOrder $placeOrder)
    {
        $data = $request->validated();
        $userId = $request->user()->id;

        $cart = $getCart($userId);

        if ($cart->isEmpty()) {
            return ApiResponse::error('سبد خرید شما خالی است.', 422, [], 'empty_cart');
        }

        $itemNames = Item::whereIn('id', array_map(fn ($l) => $l->itemId, $cart->lines))
            ->pluck('name', 'id');

        $lines = array_map(fn ($line) => new OrderLineData(
            itemId: $line->itemId,
            itemName: $itemNames->get($line->itemId, 'کالا'),
            quantity: $line->quantity,
            unitPrice: $line->unitPrice,
        ), $cart->lines);

        $order = $placeOrder(new PlaceOrderData(
            userId: $userId,
            address: $data['address'],
            lines: $lines,
            discountCode: $cart->discountCode,
            subtotal: $cart->subtotal,
            discountAmount: $cart->discountAmount,
            tax: $cart->tax,
            total: $cart->total,
            idempotencyKey: $data['idempotency_key'],
        ));

        return ApiResponse::success(new OrderResource($order), 'سفارش با موفقیت ثبت شد', 201);
    }
}
