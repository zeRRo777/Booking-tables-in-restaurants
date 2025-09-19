<?php

namespace Database\Seeders;

use App\Models\RestaurantStatuse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestaurantStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('restaurant_statuses')->delete();

        $statuses = [
            ['name' => 'active'],
            ['name' => 'moderation'],
            ['name' => 'rejected'],
            ['name' => 'closed'],
        ];

        foreach ($statuses as $status) {
            RestaurantStatuse::create($status);
        }
    }
}
