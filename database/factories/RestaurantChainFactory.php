<?php

namespace Database\Factories;

use App\Models\Restaurant_chain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Restaurant_chain>
 */
class RestaurantChainFactory extends Factory
{
    protected $model = Restaurant_chain::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Chain',
        ];
    }
}
