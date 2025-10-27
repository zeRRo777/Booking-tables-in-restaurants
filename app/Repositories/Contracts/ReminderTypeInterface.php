<?php

namespace App\Repositories\Contracts;

use App\DTOs\ReminderType\CreateReminderTypeDTO;
use App\DTOs\ReminderType\ReminderTypeFilterDTO;
use App\Models\ReminderType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReminderTypeInterface
{
    public function getAll(ReminderTypeFilterDTO $dto): LengthAwarePaginator;

    public function getById(int $id): ?ReminderType;

    public function create(CreateReminderTypeDTO $dto): ReminderType;

    public function resetDefault(): bool;

    public function update(ReminderType $reminderType, array $data): bool;
}
