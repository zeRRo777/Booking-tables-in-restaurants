<?php

namespace App\Repositories\Contracts;

use App\DTOs\ReminderType\ReminderTypeFilterDTO;
use App\Models\ReminderType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReminderTypeInterface
{
    public function getAll(ReminderTypeFilterDTO $dto): LengthAwarePaginator;

    public function getById(int $id): ?ReminderType;
}
