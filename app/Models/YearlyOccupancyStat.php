<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YearlyOccupancyStat extends Model
{
    use HasFactory;

    protected $table = 'yearly_occupancy_stats';

    protected $fillable = [
        'restaurant_id',
        'year',
        'total_reservations',
        'total_guests',
        'average_occupancy_percent',
        'peak_month',
        'off_peak_month',
    ];
}
