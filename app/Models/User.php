<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'password',
        'name',
        'phone',
        'email_verified_at',
        'phone_verified_at',
        'is_blocked'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime'
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(UserToken::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(UserRestaurantStatuse::class);
    }

    public function administeredRestaurants(): BelongsToMany
    {
        return $this->belongsToMany(Restaurant::class, 'restaurant_admins');
    }

    public function administeredChains(): BelongsToMany
    {
        return $this->belongsToMany(RestaurantChain::class, 'chain_super_admins');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function blockedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'blocked_by');
    }
}
