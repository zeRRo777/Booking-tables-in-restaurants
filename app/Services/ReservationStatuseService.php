<?php

namespace App\Services;

use App\DTOs\ReservationStatuse\CreateReservationStatuseDTO;
use App\DTOs\ReservationStatuse\UpdateReservationStatuseDTO;
use App\Exceptions\NotFoundException;
use App\Models\ReservationStatuse;
use App\Repositories\Contracts\ReservationStatuseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReservationStatuseService
{
    public function __construct(
        protected ReservationStatuseRepositoryInterface $reservationStatuseRepository,
    ) {}

    public function getAll(): Collection
    {
        return $this->reservationStatuseRepository->getAll();
    }

    public function getStatus(int $id): ?ReservationStatuse
    {
        $status = $this->reservationStatuseRepository->getById($id);

        if (!$status) {
            throw new NotFoundException('Статус бронирования не найден');
        }

        return $status;
    }

    public function createStatus(CreateReservationStatuseDTO $dto): ReservationStatuse
    {
        return $this->reservationStatuseRepository->create($dto);
    }

    public function deleteStatus(int $id): void
    {
        $status = $this->getStatus($id);

        DB::transaction(function () use ($status) {
            $this->reservationStatuseRepository->delete($status);
        });
    }

    public function updateStatus(UpdateReservationStatuseDTO $dto, ReservationStatuse $status): ReservationStatuse
    {
        $data = array_filter(
            $dto->toArray(),
            fn($value) => !is_null($value)
        );

        if (empty($data)) {
            return $status;
        }

        $this->reservationStatuseRepository->update($status, $data);

        return $status->refresh();
    }
}
