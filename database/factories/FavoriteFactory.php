<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\Favorite;
use App\Domain\Catalog\Models\Item;
use App\Domain\Customer\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Favorite>
 */
class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
        ];
    }
}
