<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantShedule extends Model
{
    use SoftDeletes;

    protected $primaryKey = ['restaurant_id', 'date'];

    public $incrementing = false;

    protected $fillable = ['restaurant_id', 'date', 'opens_at', 'closes_at', 'is_closed', 'description'];

    protected $casts = [
        'date' => 'date',
        'opens_at' => 'datetime:H:i',
        'closes_at' => 'datetime:H:i',
        'is_closed' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
