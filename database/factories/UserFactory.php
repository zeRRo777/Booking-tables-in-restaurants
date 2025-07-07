<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'is_blocked' => false,
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_blocked' => true,
        ]);
    }
}
