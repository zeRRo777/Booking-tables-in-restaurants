<?php

namespace App\Repositories\Contracts;

use App\DTOs\ReservationStatuse\CreateReservationStatuseDTO;
use App\DTOs\ReservationStatuse\UpdateReservationStatuseDTO;
use App\Models\ReservationStatuse;
use Illuminate\Support\Collection;

interface ReservationStatuseRepositoryInterface
{
    public function getAll(): Collection;

    public function getById(int $id): ?ReservationStatuse;

    public function create(CreateReservationStatuseDTO $dto): ReservationStatuse;

    public function update(ReservationStatuse $status, array $data): bool;

    public function delete(ReservationStatuse $status): bool;
}
