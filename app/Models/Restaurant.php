<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Scope;

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
        'status_id'
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
        return $this->belongsTo(RestaurantChain::class, 'restaurant_chain_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(RestaurantStatuse::class);
    }

    #[Scope]
    public function active(Builder $query): void
    {
        $query->whereHas('status', function (Builder $q) {
            $q->where('name', 'active');
        });
    }

    #[Scope]
    public function forUser(Builder $query, ?User $user): void
    {
        if (!$user || !$user->hasAnyRole(['superadmin', 'admin_chain', 'admin_restaurant'])) {
            $query->active();
            return;
        }

        if ($user->hasRole('superadmin')) {
            return;
        }

        if ($user->hasRole('admin_restaurant')) {
            $query->where(function (Builder $q) use ($user) {
                $q->active()
                    ->orWhereHas('administrators', fn($adminQuery) => $adminQuery->where('user_id', $user->id));
            });
        }
    }
}
