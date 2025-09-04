<?php

namespace Database\Factories;

use App\Models\Restaurant_chain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'description' => fake()->paragraph(),
            'address' => fake()->unique()->address(),
            'type_kitchen' => fake()->randomElement(['Italian', 'Japanese', 'Mexican', 'Russian', 'Fusion']),
            'price_range' => fake()->randomElement(['500-1000', '1000-2000', '5000-10000']),
            'weekdays_opens_at' => fake()->time('H:i', '10:00'),
            'weekdays_closes_at' => fake()->time('H:i', '23:00'),
            'weekend_opens_at' => fake()->time('H:i', '11:00'),
            'weekend_closes_at' => fake()->time('H:i', '01:00'),
            'cancellation_policy' => fake()->sentence(),
            'restaurant_chain_id' => null,
        ];
    }

    public function withChain(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'restaurant_chain_id' => Restaurant_chain::factory()
            ];
        });
    }
}
