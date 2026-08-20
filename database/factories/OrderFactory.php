<?php

namespace Database\Factories;

use App\Domain\Customer\Models\User;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 *
 * Produces a structurally valid but financially *empty* order (all money
 * columns default to 0) — OrderSeeder's support service always overwrites
 * subtotal/tax/total after generating real OrderItem rows, since those
 * totals must be derived from actual line items to stay consistent, never
 * faked independently.
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'uuid'            => Str::random(10),
            'order_number'    => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'idempotency_key' => (string) Str::uuid(),
            'subtotal'        => 0,
            'discount_amount' => 0,
            'discount_code'   => null,
            'tax'             => 0,
            'total_price'     => 0,
            'address'         => fake()->address(),
            'status'          => OrderStatus::Pending,
        ];
    }
}
