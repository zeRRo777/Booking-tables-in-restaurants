<?php

namespace App\Repositories\Contracts;

use App\DTOs\Reservation\CreateReservationDTO;
use App\Models\Reservation;

interface ReservationRepositoryInterface
{
    public function create(CreateReservationDTO $dto): Reservation;

    public function getById(int $id): ?Reservation;

    public function update(Reservation $reservation, array $data): bool;

    public function delete(Reservation $reservation): bool;
}
