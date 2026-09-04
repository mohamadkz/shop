<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\CreateItem;
use App\Domain\Catalog\Actions\DeleteItem;
use App\Domain\Catalog\Actions\UpdateItem;
use App\Domain\Catalog\DTOs\ItemData;
use App\Domain\Catalog\Enums\ItemStatus;
use App\Shared\Http\ApiResponse;


use App\Http\Controllers\Controller;
use App\Domain\Catalog\Requests\StoreItemRequest;
use App\Domain\Catalog\Requests\UpdateItemRequest;
use App\Domain\Catalog\Resources\ItemResource;
use App\Domain\Catalog\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $data = $request->validate([
            'q' => ['nullable', 'string', 'min:2', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $data['per_page'] ?? 20;

        if (! empty($data['q'])) {
            $query = Item::search($data['q'])
                ->where('status', ItemStatus::Active->value);

            if (isset($data['category_id'])) {
                $query->where('category_id', $data['category_id']);
            }

            return ApiResponse::success(ItemResource::collection($query->paginate($perPage)));
        }

        $items = Item::query()
            ->with('category')
            ->active()
            ->when(isset($data['category_id']), fn ($q) => $q->where('category_id', $data['category_id']))
            ->paginate($perPage);

        return ApiResponse::success(ItemResource::collection($items));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request, CreateItem $action)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('items', 'public');
        }

        $item = $action(ItemData::fromArray($data));
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

        $data = $request->validated();

        if ($request->hasFile('image')) {
            // delete previous image if present
            if (! empty($item->image)) {
                Storage::disk('public')->delete($item->image);
            }

            $data['image'] = $request->file('image')->store('items', 'public');
        }

        $item = $action($item, ItemData::fromArray(array_merge($current, $data)));

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
