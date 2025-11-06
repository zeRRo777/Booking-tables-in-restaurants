<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\DTOs\Reservation\CreateReservationDTO;
use App\Models\Reservation;
use App\Models\ReservationStatuse;

class EloquentReservationRepository implements ReservationRepositoryInterface
{
    public function create(CreateReservationDTO $dto): Reservation
    {
        $status = ReservationStatuse::where('name', $dto->status)->firstOrFail();

        return Reservation::create([
            'special_wish' => $dto->special_wish,
            'starts_at' => $dto->starts_at,
            'ends_at' => $dto->ends_at,
            'user_id' => $dto->user_id,
            'restaurant_id' => $dto->restaurant_id,
            'count_people' => $dto->count_people,
            'reminder_type_id' => $dto->reminder_type_id,
            'table_id' => $dto->table_id,
            'status_id' => $status->id,
        ]);
    }

    public function getById(int $id): Reservation|null
    {
        return Reservation::find($id);
    }

    public function update(Reservation $reservation, array $data): bool
    {
        return $reservation->update($data);
    }

    public function delete(Reservation $reservation): bool
    {
        return $reservation->forceDelete();
    }
}
