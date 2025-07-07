<?php

namespace Database\Factories;

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
            'weekdays_opens_at' => '08:00',
            'weekdays_closes_at' => '22:00',
            'weekend_opens_at' => '10:00',
            'weekend_closes_at' => '23:00',
            'cancellation_policy' => fake()->sentence(),
            'restaurant_chain_id' => null,
        ];
    }
}
