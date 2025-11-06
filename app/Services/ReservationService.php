<?php

namespace App\Services;

use App\DTOs\Reservation\CreateReservationDTO;
use App\DTOs\Reservation\UpdateReservationDTO;
use App\Exceptions\NotFoundException;
use App\Jobs\SendReservationReminder;
use App\Models\Reservation;
use App\Models\ScheduledReminder;
use App\Notifications\ReservationDeletedNotification;
use App\Notifications\ReservationStatusUpdatedNotification;
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

        ScheduledReminder::create([
            'reservation_id' => $reservation->id,
            'reminder_type_id' => $reminderType->id,
            'execute_at' => $executeAt,
            'status' => 'pending',
        ]);

        SendReservationReminder::dispatch($reservation)->delay($executeAt);
    }

    public function getReservation(int $id): Reservation
    {
        $reservation = $this->reservationRepository->getById($id);

        if (!$reservation) {
            throw new NotFoundException('Бронь не найдена!');
        }

        return $reservation->load('reminderType', 'status', 'user', 'table', 'restaurant');
    }

    public function updateReservation(Reservation $reservation, UpdateReservationDTO $dto): Reservation
    {
        $data = array_filter(
            $dto->toArray(),
            fn($value) => !is_null($value)
        );

        if (empty($data)) {
            return $reservation;
        }

        $oldStatusId = $reservation->status_id;
        $oldStartsAt = $reservation->starts_at;
        $oldReminderTypeId = $reservation->reminder_type_id;

        $updatedReservation = DB::transaction(function () use ($reservation, $data, $oldStatusId, $oldStartsAt, $oldReminderTypeId): Reservation {
            $this->reservationRepository->update($reservation, $data);

            $updatedReservation = $reservation->fresh();

            if (isset($data['status_id']) && $data['status_id'] !== $oldStatusId) {
                $updatedReservation->user->notify(new ReservationStatusUpdatedNotification($updatedReservation));
            }

            $timeChanged = isset($data['starts_at']) && !$updatedReservation->starts_at->equalTo($oldStartsAt);
            $reminderTypeChanged = isset($data['reminder_type_id']) && $data['reminder_type_id'] !== $oldReminderTypeId;

            if ($timeChanged || $reminderTypeChanged) {
                ScheduledReminder::where('reservation_id', $updatedReservation->id)->delete();

                $this->scheduleReminder($updatedReservation);
            }

            return $updatedReservation;
        });

        return $updatedReservation->load('reminderType', 'status', 'user', 'table', 'restaurant');
    }

    public function deleteReservation(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            ScheduledReminder::where('reservation_id', $reservation->id)->delete();

            $this->reservationRepository->delete($reservation);

            $reservation->user->notify(new ReservationDeletedNotification($reservation));
        });
    }
}
