<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Models\SentReminder;
use App\Notifications\ReminderReservationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendReservationReminder implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Reservation $reservation,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reservation = $this->reservation->fresh(['status', 'user', 'reminderType']);

        if ($reservation->status->name !== 'Confirmed') {
            Log::info("Reservation reminder skipped for reservation #{$reservation->id} due to status: {$reservation->status->name}");
            return;
        }

        DB::transaction(function () use ($reservation) {
            $scheduled = $reservation->scheduledReminders()->first();

            if ($scheduled) {
                $scheduled->update(['status' => 'processing']);
            }

            try {
                $this->reservation->user->notify(new ReminderReservationNotification($this->reservation));

                SentReminder::create([
                    'reservation_id' => $reservation->id,
                    'recipient_email' => $reservation->user->email,
                    'reminder_type_id' => $reservation->reminder_type_id,
                    'status' => 'success',
                ]);

                if ($scheduled) {
                    $scheduled->attempts = ($scheduled->attempts ?? 0) + 1;
                    $scheduled->status = 'sent';
                    $scheduled->save();
                }
            } catch (\Exception $e) {
                SentReminder::create([
                    'reservation_id' => $reservation->id,
                    'recipient_email' => $reservation->user->email,
                    'reminder_type_id' => $reservation->reminder_type_id,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                if ($scheduled) {
                    $scheduled->attempts = ($scheduled->attempts ?? 0) + 1;
                    $scheduled->status = 'failed';
                    $scheduled->save();
                }

                throw $e;
            }
        });
    }
}
