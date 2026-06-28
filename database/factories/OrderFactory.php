<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Basket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total_price' => 0,
            'address' => $this->faker->address(),
            'status' => 'pending'
        ];
    }
}
