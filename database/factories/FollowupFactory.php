<?php

namespace Database\Factories;

use App\Domain\Order\Models\Followup;
use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Followup>
 */
class FollowupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Followup::class;

    public function definition(): array
    {
        return [
            'title' => 'ثبت سفارش',
            'description' => $this->faker->sentence()
        ];
    }
}
