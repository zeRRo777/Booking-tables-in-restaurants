<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentReminder extends Model
{
    protected $fillable = [
        'reservation_id',
        'sent_at',
        'recipient_email',
        'reminder_type_id',
        'status',
        'error_message',
        'created_at'
    ];

    protected $casts = [
        'sent_at' => 'timestamp',
        'created_at' => 'timestamp'
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
