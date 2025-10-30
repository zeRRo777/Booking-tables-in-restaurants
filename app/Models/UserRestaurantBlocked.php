<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRestaurantBlocked extends Model
{
    use SoftDeletes;

    protected $primaryKey = ['user_id', 'restaurant_id'];
    protected $table = 'user_restaurant_blocked';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'block_reason',
        'blocked_by'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }
}
