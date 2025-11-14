<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyOccupancyStat extends Model
{
    use HasFactory;

    protected $table = 'monthly_occupancy_stats';

    protected $fillable = [
        'restaurant_id',
        'year',
        'month',
        'total_reservations',
        'total_guests',
        'average_occupancy_percent',
        'peak_day',
        'off_peak_day',
    ];
}
