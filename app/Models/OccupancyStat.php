<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OccupancyStat extends Model
{
    protected $primaryKey = ['restaurant_id', 'date', 'hour'];
    protected $fillable = ['restaurant_id', 'date', 'hour', 'occupacity_percent'];
    public $incrementing = false;

    protected $casts = ['date' => 'date'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
