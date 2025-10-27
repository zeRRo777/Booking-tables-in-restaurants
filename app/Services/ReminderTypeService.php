<?php

namespace App\Services;

use App\DTOs\ReminderType\ReminderTypeFilterDTO;
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
}
