<?php

namespace Database\Factories;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $unitPrice = fake()->randomFloat(2, 50_000, 2_000_000);

        return [
            'order_id'   => Order::factory(),
            'item_id'    => fake()->numberBetween(1, 1000), // overridden by OrderSeedingService with a real item id
            'item_name'  => fake()->words(3, true),
            'quantity'   => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => round($quantity * $unitPrice, 2),
        ];
    }
}
