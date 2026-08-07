<?php

namespace Database\Factories;

use App\Domain\Payment\Models\Payment;
use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'amount' => 0,
            'payment_method' => 'online',
            'transaction_id' => $this->faker->uuid(),
            'status' => 'success',
            'paid_at' => now()
        ];
    }
}
