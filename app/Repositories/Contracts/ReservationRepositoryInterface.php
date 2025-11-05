<?php

namespace App\Repositories\Contracts;

use App\DTOs\Reservation\CreateReservationDTO;
use App\Models\Reservation;

interface ReservationRepositoryInterface
{
    public function create(CreateReservationDTO $dto): Reservation;
}
