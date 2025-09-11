<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentReminder extends Model
{
    protected $fillable = [
        'reservation_id',
        'recipient_email',
        'reminder_type_id',
        'status',
        'error_message',
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
