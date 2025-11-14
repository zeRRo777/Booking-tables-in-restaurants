<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyOccupancyStat extends Model
{
    use HasFactory;

    protected $table = 'daily_occupancy_stats';

    protected $fillable = [
        'restaurant_id',
        'date',
        'total_reservations',
        'total_guests',
        'average_occupancy_percent',
        'peak_hour',
        'off_peak_hour',
    ];
}
