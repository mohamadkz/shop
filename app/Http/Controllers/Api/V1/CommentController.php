<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\AddComment;
use App\Domain\Catalog\Models\Item;
use App\Domain\Catalog\Requests\StoreCommentRequest;
use App\Domain\Catalog\Resources\CommentResource;
use App\Http\Controllers\Controller;
use App\Shared\Http\ApiResponse;

class CommentController extends Controller
{
    public function index(Item $item)
    {
        return ApiResponse::success(CommentResource::collection($item->comments()->latest()->paginate(20)));
    }

    public function store(StoreCommentRequest $request, Item $item, AddComment $action)
    {
        $data = $request->validated();

        $comment = $action($item, $request->user()->id, $data['rating'], $data['comment']);

        return ApiResponse::success(new CommentResource($comment), 'نظر شما ثبت شد', 201);
    }
}
