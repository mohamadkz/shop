<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 99999)),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(100000, 10000000),
            'stock' => $this->faker->numberBetween(1, 50),
            'image' => 'default.png',
            'status' => true
        ];
    }
}
