<?php

namespace Database\Factories;

use App\Domain\Cart\Enums\DiscountType;
use App\Domain\Cart\Models\DiscountCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountCode>
 */
class DiscountCodeFactory extends Factory
{
    protected $model = DiscountCode::class;

    public function definition(): array
    {
        $type = fake()->randomElement(DiscountType::cases());

        return [
            'code'         => strtoupper(fake()->unique()->bothify('SAVE-####')),
            'type'         => $type,
            'percent'      => $type === DiscountType::Percentage ? fake()->numberBetween(5, 50) : null,
            'fixed_amount' => $type === DiscountType::Fixed ? fake()->numberBetween(20_000, 300_000) : null,
            'max_discount' => $type === DiscountType::Percentage ? fake()->numberBetween(100_000, 500_000) : null,
            'expired_at'   => fake()->dateTimeBetween('+1 week', '+6 months'),
            'usage_limit'  => fake()->boolean(60) ? fake()->numberBetween(10, 500) : null,
            'used_count'   => 0,
        ];
    }

    /** State: already expired — useful for testing the `valid()` scope excludes it. */
    public function expired(): static
    {
        return $this->state(fn () => ['expired_at' => now()->subDay()]);
    }
}
