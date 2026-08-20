<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\ToggleFavorite;
use App\Domain\Catalog\Models\Item;
use App\Http\Controllers\Controller;
use App\Shared\Http\ApiResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Item $item, ToggleFavorite $action)
    {
        $isFavorited = $action($item, $request->user()->id);

        return ApiResponse::success(
            ['is_favorited' => $isFavorited],
            $isFavorited ? 'به علاقه مندی ها اضافه شد' : 'از علاقه مندی ها حذف شد'
        );
    }
}
