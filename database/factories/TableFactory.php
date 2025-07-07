<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Table>
 */
class TableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = fake()->numberBetween(2, 4);
        $max = $min + fake()->numberBetween(1, 4);

        return [
            'number' => fake()->unique()->numberBetween(1, 50),
            'capacity_min' => $min,
            'capacity_max' => $max,
            'zone' => fake()->randomElement(['Terrace', 'Main Hall', 'VIP', 'Garden']),
            'restaurant_id' => Restaurant::factory(),
        ];
    }
}
