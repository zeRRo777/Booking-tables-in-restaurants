<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRestaurantStatuse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'is_blocked',
        'block_reason',
        'blocked_by'
    ];

    protected $casts = [
        'is_blocked' => 'boolean'
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
