<?php

namespace Database\Seeders;

use App\Models\ReservationStatuse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reservation_statuses')->delete();

        $statuses = [
            ['name' => 'Pending'],
            ['name' => 'Confirmed'],
            ['name' => 'Cancelled'],
            ['name' => 'Seated'],
            ['name' => 'Completed'],
            ['name' => 'No-show'],
        ];

        foreach ($statuses as $status) {
            ReservationStatuse::create($status);
        }
    }
}
