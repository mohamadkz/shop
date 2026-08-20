<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Cart\Actions\AddItemToCart;
use App\Domain\Cart\Actions\ApplyDiscountCode;
use App\Domain\Cart\Actions\GetCart;
use App\Domain\Cart\Actions\RemoveDiscountCode;
use App\Domain\Cart\Actions\RemoveItemFromCart;
use App\Domain\Cart\Actions\UpdateCartItemQuantity;
use App\Domain\Cart\Requests\ApplyDiscountRequest;
use App\Domain\Cart\Requests\StoreCartItemRequest;
use App\Domain\Cart\Requests\UpdateCartItemRequest;
use App\Domain\Cart\Resources\CartResource;
use App\Domain\Catalog\Models\Item;
use App\Domain\Catalog\Requests\StoreItemRequest;
use App\Http\Controllers\Controller;
use App\Shared\Http\ApiResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request, GetCart $action)
    {
        return ApiResponse::success(new CartResource($action($request->user()->id)));
    }

    public function store(StoreCartItemRequest $request, AddItemToCart $action)
    {
        $data = $request->validated();

        // Cart controller reads the item's current price from Catalog —
        // this is orchestration at the HTTP layer, not a domain calling
        // another domain's Action/Service.
        $item = Item::active()->findOrFail($data['item_id']);

        $cart = $action($request->user()->id, $item->id, $data['quantity'] ?? 1, (float) $item->price);

        return ApiResponse::success(new CartResource($cart), 'کالا به سبد خرید اضافه شد');
    }

    public function update(UpdateCartItemRequest $request, int $itemId, UpdateCartItemQuantity $action)
    {
        $cart = $action($request->user()->id, $itemId, $request->validated()['quantity']);

        return ApiResponse::success(new CartResource($cart));
    }

    public function destroy(Request $request, int $itemId, RemoveItemFromCart $action)
    {
        $cart = $action($request->user()->id, $itemId);

        return ApiResponse::success(new CartResource($cart), 'کالا از سبد خرید حذف شد');
    }

    public function applyDiscount(ApplyDiscountRequest $request, ApplyDiscountCode $action)
    {
        $cart = $action($request->user()->id, $request->validated()['code']);

        return ApiResponse::success(new CartResource($cart), 'کد تخفیف اعمال شد');
    }

    public function removeDiscount(Request $request, RemoveDiscountCode $action)
    {
        $cart = $action($request->user()->id);

        return ApiResponse::success(new CartResource($cart), 'کد تخفیف حذف شد');
    }
}
