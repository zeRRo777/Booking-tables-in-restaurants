<?php

namespace App\Models;

use Database\Factories\RestaurantChainFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantChain extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['name', 'status_id'];

    public function superAdmins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chain_super_admins');
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ChainStatuse::class);
    }
}
