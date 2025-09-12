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
        $min = $this->faker->randomElement([2, 4, 6]);
        $max = $min + $this->faker->randomElement([0, 2]);

        return [
            'number' => fake()->numberBetween(1, 100),
            'capacity_min' => $min,
            'capacity_max' => $max,
            'zone' => fake()->randomElement(['Main Hall', 'Terrace', 'VIP', 'Bar']),
            'restaurant_id' => Restaurant::factory(),
        ];
    }
}
