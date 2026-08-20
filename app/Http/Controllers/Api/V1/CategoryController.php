<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Resources\CategoryResource;
use App\Http\Controllers\Controller;
use App\Shared\Http\ApiResponse;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()->whereNull('parent_id')->with('children')->get();

        return ApiResponse::success(CategoryResource::collection($categories));
    }
}
