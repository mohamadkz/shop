<?php

namespace Database\Factories;

use App\Domain\Customer\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /** Shared, pre-hashed password so seeded accounts are cheap to log into during local dev. */
    protected static ?string $sharedHashedPassword;

    public function definition(): array
    {
        return [
            'uuid'               => Str::random(10),
            'name'               => fake()->name(),
            'email'              => fake()->unique()->safeEmail(),
            // Iran-style mobile numbers to match the project's OTP/SMS flow.
            'phone'              => '09' . fake()->unique()->numerify('#########'),
            'email_verified_at'  => fake()->boolean(80) ? now() : null,
            'phone_verified_at'  => fake()->boolean(70) ? now() : null,
            'last_otp_sent_at'   => null,
            'password'           => static::$sharedHashedPassword ??= Hash::make('password'),
            'remember_token'     => Str::random(10),
        ];
    }

    /** State: guaranteed-verified account, useful for auth-flow tests/demos. */
    public function verified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
    }
}
