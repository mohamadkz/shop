<?php

namespace Database\Factories;

use App\Models\BasketItem;
use App\Models\Item;
use App\Models\Basket;
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
    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(1, 5),
            'price' => $this->faker->numberBetween(100000, 5000000)
        ];
    }
}
