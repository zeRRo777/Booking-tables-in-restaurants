<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderType extends Model
{
    protected $fillable = [
        'name',
        'minutes_before',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function sentReminders(): HasMany
    {
        return $this->hasMany(SentReminder::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function scheduledReminders(): HasMany
    {
        return $this->hasMany(ScheduledReminder::class);
    }
}
