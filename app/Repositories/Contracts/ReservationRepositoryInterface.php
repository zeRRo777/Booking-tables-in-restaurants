<?php

namespace App\Repositories\Contracts;

use App\DTOs\Reservation\CreateReservationDTO;
use App\DTOs\Reservation\ListReservationDTO;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReservationRepositoryInterface
{
    public function create(CreateReservationDTO $dto): Reservation;

    public function getById(int $id): ?Reservation;

    public function update(Reservation $reservation, array $data): bool;

    public function delete(Reservation $reservation): bool;

    public function list(ListReservationDTO $dto, User|Restaurant $scope): LengthAwarePaginator;
}
