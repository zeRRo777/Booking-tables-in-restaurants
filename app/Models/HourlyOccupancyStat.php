<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourlyOccupancyStat extends Model
{
    use HasFactory;

    protected $table = 'hourly_occupancy_stats';

    protected $fillable = [
        'restaurant_id',
        'date',
        'hour',
        'reservations_count',
        'guests_count',
        'occupancy_percent',
    ];
}
