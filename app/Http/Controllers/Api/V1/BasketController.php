<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ApplyDiscountRequest;
use App\Http\Requests\Api\V1\CheckoutRequest;
use App\Http\Requests\Api\V1\StoreBasketRequest;
use App\Http\Requests\Api\V1\UpdateBasketRequest;
use App\Models\Item;
use App\Services\BasketService;
use Illuminate\Http\Request;

class BasketController extends Controller
{
    public function __construct(private readonly BasketService $basketService)
    {
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->basketService->getBasket($request->user())
        );
    }

    public function store(StoreBasketRequest $request)
    {
        $data = $request->validated();

        $item = Item::findOrFail($data['item_id']);

        return response()->json(
            $this->basketService->addItem($request->user(), $item, $data['quantity'] ?? 1)
        );
    }

    public function update(UpdateBasketRequest $request, int $itemId)
    {
        $data = $request->validated();

        return response()->json(
            $this->basketService->updateItemQuantity($request->user(), $itemId, $data['quantity'])
        );
    }

    public function destroy(Request $request, int $itemId)
    {
        return response()->json(
            $this->basketService->removeItem($request->user(), $itemId)
        );
    }

    public function applyDiscount(ApplyDiscountRequest $request)
    {
        $data = $request->validated();

        return response()->json(
            $this->basketService->applyDiscountCode($request->user(), $data['code'])
        );
    }

    public function removeDiscount(Request $request)
    {
        return response()->json(
            $this->basketService->removeDiscountCode($request->user())
        );
    }

    public function checkout(CheckoutRequest $request)
    {
        $data = $request->validated();
        $order = $this->basketService->checkout(
            $request->user(),
            $data['address']
        );

        return response()->json([
            'message' => 'سفارش با موفقیت ایجاد شد',
            'order'   => $order,
        ], 201);
    }
}