<?php

namespace Database\Factories;

use App\Domain\Catalog\Enums\ItemStatus;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            // Falls back to creating a Category only if none is supplied —
            // ItemSeeder always passes an explicit category_id from
            // already-seeded categories, so this factory-created fallback
            // only fires when Item::factory() is used standalone (e.g. in tests).
            'uuid'        => Str::random(10),
            'category_id' => Category::factory(),
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'price'       => fake()->randomFloat(2, 50_000, 5_000_000),
            'stock'       => fake()->numberBetween(0, 200),
            'image'       => null,
            'status'      => ItemStatus::Active,
        ];
    }

    /** State: out of stock, still visible/purchasable-flagged for UI testing. */
    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => ItemStatus::Draft]);
    }
}
