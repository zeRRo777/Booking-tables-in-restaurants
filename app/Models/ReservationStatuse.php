<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationStatuse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'status_id');
    }
}
