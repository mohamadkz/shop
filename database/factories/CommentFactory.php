<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\Comment;
use App\Domain\Catalog\Models\Item;
use App\Domain\Customer\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 *
 * user_id is a plain scalar FK (Catalog doesn't declare an Eloquent
 * relation into Customer's User model), so we resolve a real user id via
 * User::factory() by default, but CommentSeeder always overrides both
 * ids explicitly with rows it already seeded.
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
            'rating'  => fake()->numberBetween(1, 5),
            'comment' => fake()->realText(140),
        ];
    }
}
