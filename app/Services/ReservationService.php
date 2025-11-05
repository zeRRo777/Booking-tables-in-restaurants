<?php

namespace App\Services;

use App\DTOs\Reservation\CreateReservationDTO;
use App\Jobs\SendReservationReminder;
use App\Models\Reservation;
use App\Notifications\SuccessReservationNotification;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

class ReservationService
{
    public function __construct(
        protected ReservationRepositoryInterface $reservationRepository
    ) {}

    public function createReservation(CreateReservationDTO $dto): Reservation
    {
        $reservation = DB::transaction(function () use ($dto) {
            $newReservation = $this->reservationRepository->create($dto);

            $newReservation->load('reminderType', 'status', 'user', 'table', 'restaurant');

            $this->scheduleReminder($newReservation);

            $newReservation->user->notify(new SuccessReservationNotification($newReservation));

            return $newReservation;
        });

        return $reservation;
    }

    protected function scheduleReminder(Reservation $reservation): void
    {
        $reminderType = $reservation->reminderType;
        $executeAt = $reservation->starts_at->subMinutes($reminderType->minutes_before);

        if ($executeAt->isPast()) {
            return;
        }

        Schedule::create([
            'reservation_id' => $reservation->id,
            'reminder_type_id' => $reminderType->id,
            'execute_at' => $executeAt,
            'status' => 'pending',
        ]);

        SendReservationReminder::dispatch($reservation)->delay($executeAt);
    }
}
