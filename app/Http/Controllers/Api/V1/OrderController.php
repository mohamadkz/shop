<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Resources\OrderResource;
use App\Http\Controllers\Controller;
use App\Shared\Http\ApiResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->ownedBy($request->user()->id)
            ->with('items')
            ->latest()
            ->paginate(min($request->integer('per_page', 15), 50));

        return ApiResponse::success(OrderResource::collection($orders));
    }

    public function show(Request $request, Order $order)
    {
        // Ownership check: a user may only view their own orders, even
        // though route-model binding already resolved the model by UUID.
        if ($order->user_id !== $request->user()->id) {
            return ApiResponse::error('این سفارش متعلق به شما نیست.', 403, [], 'forbidden');
        }

        return ApiResponse::success(new OrderResource($order->load('items')));
    }
}
