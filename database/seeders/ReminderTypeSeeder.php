<?php

namespace Database\Seeders;

use App\Models\ReminderType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReminderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reminder_types')->delete();

        $types = [
            ['name' => '1h', 'minutes_before' => 60],
            ['name' => '2h', 'minutes_before' => 120, 'is_default' => true],
            ['name' => '3h', 'minutes_before' => 180],
            ['name' => '4h', 'minutes_before' => 240],
            ['name' => '5h', 'minutes_before' => 300],
            ['name' => '6h', 'minutes_before' => 360],
            ['name' => '8h', 'minutes_before' => 480],
            ['name' => '12h', 'minutes_before' => 720],
            ['name' => '24h', 'minutes_before' => 1440],
            ['name' => '2d', 'minutes_before' => 2880],
        ];

        foreach ($types as $type) {
            ReminderType::create($type);
        }
    }
}
