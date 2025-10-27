<?php

namespace App\Services;

use App\DTOs\ReminderType\ReminderTypeFilterDTO;
use App\Exceptions\NotFoundException;
use App\Models\ReminderType;
use App\Repositories\Contracts\ReminderTypeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReminderTypeService
{
    public function __construct(
        protected ReminderTypeInterface $reminderTypeRepository
    ) {}

    public function getAll(ReminderTypeFilterDTO $dto): LengthAwarePaginator
    {
        return $this->reminderTypeRepository->getAll($dto);
    }

    public function getType(int $id): ReminderType
    {
        $reminderType = $this->reminderTypeRepository->getById($id);

        if (!$reminderType) {
            throw new NotFoundException('Тип напоминания не найден');
        }

        return $reminderType;
    }
}
