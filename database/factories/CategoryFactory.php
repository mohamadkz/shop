<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->boolean(70) ? fake()->sentence() : null,
            'parent_id'   => null, // top-level by default; CategorySeeder assigns children explicitly
        ];
    }

    /** State: a child category under a given parent. */
    public function childOf(Category $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }
}
