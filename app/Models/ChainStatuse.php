<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChainStatuse extends Model
{
    protected $fillable = ['name'];
    protected $table = 'chain_statuses';

    public function chains(): HasMany
    {
        return $this->hasMany(RestaurantChain::class);
    }
}
