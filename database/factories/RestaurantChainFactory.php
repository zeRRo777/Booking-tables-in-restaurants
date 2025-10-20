<?php

namespace Database\Factories;

use App\Models\ChainStatuse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RestaurantChain>
 */
class RestaurantChainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company() . ' Group',
            'status_id' => ChainStatuse::query()->where('name', 'moderation')->firstOrCreate(['name' => 'moderation'])->id,
        ];
    }
}
