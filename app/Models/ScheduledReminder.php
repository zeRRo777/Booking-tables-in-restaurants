<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledReminder extends Model
{
    protected $fillable = [
        'reservation_id',
        'reminder_type_id',
        'execute_at',
        'attempts',
        'status',
    ];

    protected $casts = [
        'execute_at' => 'timestamp',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reminderType(): BelongsTo
    {
        return $this->belongsTo(ReminderType::class);
    }
}
