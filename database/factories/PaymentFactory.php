<?php

namespace Database\Factories;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id'       => Order::factory(),
            'uuid'           => Str::random(10),
            'user_id'        => fake()->numberBetween(1, 1000), // overridden with the order's real user_id
            'authority'      => strtoupper(fake()->bothify('A########################')),
            'amount'         => fake()->randomFloat(2, 50_000, 5_000_000),
            'payment_method' => 'zarinpal',
            'ref_id'         => null,
            'card_pan'       => null,
            'status'         => PaymentStatus::Pending,
            'paid_at'        => null,
        ];
    }

    /** State: a successfully completed, gateway-verified payment. */
    public function successful(): static
    {
        return $this->state(fn () => [
            'status'   => PaymentStatus::Success,
            'ref_id'   => (string) fake()->unique()->numberBetween(100000, 999999),
            'card_pan' => '6037-****-****-' . fake()->numerify('####'),
            'paid_at'  => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => PaymentStatus::Failed]);
    }
}
