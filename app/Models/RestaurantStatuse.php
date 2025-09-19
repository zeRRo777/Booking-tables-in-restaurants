<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantStatuse extends Model
{
    protected $fillable = ['name'];

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }
}
