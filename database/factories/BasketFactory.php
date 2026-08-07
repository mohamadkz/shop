<?php

namespace Database\Factories;

use App\Domain\Cart\Models\Basket;
use App\Domain\Customer\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Basket>
 */
class BasketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Basket::class;

    public function definition(): array
    {
        return [
            'status' => true
        ];
    }
}
