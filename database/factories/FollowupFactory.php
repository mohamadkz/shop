<?php

namespace Database\Factories;

use App\Models\Followup;
use App\Models\Order;
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
    public function definition(): array
    {
        return [
            'title' => 'ثبت سفارش',
            'description' => $this->faker->sentence()
        ];
    }
}
