<?php

namespace App\Models;

use Database\Factories\RestaurantChainFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantChain extends Model
{
    use SoftDeletes;
}
