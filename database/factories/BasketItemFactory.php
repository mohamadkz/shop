<?php

namespace Database\Factories;

use App\Domain\Cart\Models\BasketItem;
use App\Domain\Catalog\Models\Item;
use App\Domain\Cart\Models\Basket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BasketItem>
 */
class BasketItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = BasketItem::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(1, 5),
            'price' => $this->faker->numberBetween(100000, 5000000)
        ];
    }
}
