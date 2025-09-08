<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'address',
        'type_kitchen',
        'price_range',
        'weekdays_opens_at',
        'weekdays_closes_at',
        'weekend_opens_at',
        'weekend_closes_at',
        'cancellation_policy',
        'restaurant_chain_id',
    ];

    protected $casts = [
        'weekdays_opens_at' => 'datetime:H:i',
        'weekdays_closes_at' => 'datetime:H:i',
        'weekend_opens_at' => 'datetime:H:i',
        'weekend_closes_at' => 'datetime:H:i',
    ];

    public function administrators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_admins');
    }
}
