<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use SoftDeletes, HasFactory;

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

    public function occupancyStats(): HasMany
    {
        return $this->hasMany(OccupancyStat::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RestaurantSchedule::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function usersStatuses(): HasMany
    {
        return $this->hasMany(UserRestaurantStatuse::class);
    }

    public function chain(): BelongsTo
    {
        return $this->belongsTo(RestaurantChain::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
