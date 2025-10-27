<?php

namespace App\Repositories\Eloquent;

use App\DTOs\ReservationStatuse\CreateReservationStatuseDTO;
use App\DTOs\ReservationStatuse\UpdateReservationStatuseDTO;
use App\Models\ReservationStatuse;
use App\Repositories\Contracts\ReservationStatuseRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentReservationStatuseRepository implements ReservationStatuseRepositoryInterface
{
    public function getAll(): Collection
    {
        return ReservationStatuse::all();
    }

    public function getById(int $id): ?ReservationStatuse
    {
        return ReservationStatuse::find($id);
    }

    public function create(CreateReservationStatuseDTO $dto): ReservationStatuse
    {
        return ReservationStatuse::create([
            'name' => $dto->name,
        ]);
    }

    public function update(ReservationStatuse $status, array $data): bool
    {
        return $status->update($data);
    }

    public function delete(ReservationStatuse $status): bool
    {
        return $status->delete();
    }
}
