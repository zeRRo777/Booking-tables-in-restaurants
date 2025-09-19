<?php

namespace Database\Factories;

use App\Models\RestaurantStatuse;
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
            'description' => fake()->paragraph(3),
            'address' => fake()->unique()->address(),
            'type_kitchen' => fake()->randomElement([
                'Italian',
                'French',
                'Japanese',
                'Mexican',
                'Indian'
            ]),
            'price_range' => fake()->randomElement(['500-1000', '1000-2000', '2000-3000']),
            'weekdays_opens_at' => '09:00:00',
            'weekdays_closes_at' => '22:00:00',
            'weekend_opens_at' => '10:00:00',
            'weekend_closes_at' => '23:00:00',
            'cancellation_policy' => fake()->sentence(10),
            'restaurant_chain_id' => null,
            'status_id' => RestaurantStatuse::query()->where('name', 'moderation')->firstOrCreate(['name' => 'moderation'])->id,
        ];
    }
}
