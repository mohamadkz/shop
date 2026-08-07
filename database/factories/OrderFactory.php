<?php

namespace Database\Factories;

use App\Domain\Order\Models\Order;
use App\Domain\Cart\Models\Basket;
use App\Domain\Customer\Models\User;
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

    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'total_price' => 0,
            'address' => $this->faker->address(),
            'status' => 'pending'
        ];
    }
}
