 <?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class Reservation extends Model
    {
        use SoftDeletes;

        protected $fillable = [
            'user_id',
            'table_id',
            'restaurant_id',
            'count_people',
            'special_wish',
            'status_id',
            'date_start',
            'date_end',
            'reminder_type_id'
        ];

        protected $casts = [
            'date_start' => 'datetime',
            'date_end' => 'datetime'
        ];

        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }

        public function table(): BelongsTo
        {
            return $this->belongsTo(Table::class);
        }

        public function restaurant(): BelongsTo
        {
            return $this->belongsTo(Restaurant::class);
        }

        public function reminderType(): BelongsTo
        {
            return $this->belongsTo(ReminderType::class);
        }

        public function status(): BelongsTo
        {
            return $this->belongsTo(ReservationStatuse::class);
        }

        public function sentReminders(): HasMany
        {
            return $this->hasMany(SentReminder::class);
        }

        public function scheduledReminders(): HasMany
        {
            return $this->hasMany(ScheduledReminder::class);
        }
    }
