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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Favorite::class;

    public function definition(): array
    {
        return [];
    }
}
