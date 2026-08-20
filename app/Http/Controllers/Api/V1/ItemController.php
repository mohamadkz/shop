<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\CreateItem;
use App\Domain\Catalog\Actions\DeleteItem;
use App\Domain\Catalog\Actions\UpdateItem;
use App\Domain\Catalog\DTOs\ItemData;
use App\Shared\Http\ApiResponse;


use App\Http\Controllers\Controller;
use App\Domain\Catalog\Requests\StoreItemRequest;
use App\Domain\Catalog\Requests\UpdateItemRequest;
use App\Domain\Catalog\Resources\ItemResource;
use App\Domain\Catalog\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class ItemController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = Item::query()
            ->with('category')
            ->active()
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->string('q') . '%'))
            ->paginate(min($request->integer('per_page', 20), 100));
        return ApiResponse::success(ItemResource::collection($items));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request, CreateItem $action)
    {
        $item = $action(ItemData::fromArray($request->validated()));
        return ApiResponse::success(new ItemResource($item), 'کالا با موفقیت ایجاد شد', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return ApiResponse::success(new ItemResource($item->load('category')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, Item $item, UpdateItem $action)
    {
        $current = [
            'category_id' => $item->category_id,
            'name'        => $item->name,
            'slug'        => $item->slug,
            'description' => $item->description,
            'price'       => (string) $item->price,
            'stock'       => $item->stock,
            'image'       => $item->image,
            'status'      => $item->status->value,
        ];

        $item = $action($item, ItemData::fromArray(array_merge($current, $request->validated())));

        return ApiResponse::success(new ItemResource($item), 'کالا با موفقیت به‌روزرسانی شد');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item, DeleteItem $action)
    {
        $action($item);

        return ApiResponse::success(null, 'کالا حذف شد');
    }
}
