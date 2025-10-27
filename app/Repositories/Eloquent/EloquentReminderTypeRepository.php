<?php

namespace App\Repositories\Eloquent;

use App\DTOs\ReminderType\CreateReminderTypeDTO;
use App\DTOs\ReminderType\ReminderTypeFilterDTO;
use App\Models\ReminderType;
use App\Repositories\Contracts\ReminderTypeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentReminderTypeRepository implements ReminderTypeInterface
{
    public function getAll(ReminderTypeFilterDTO $dto): LengthAwarePaginator
    {
        $query = ReminderType::query();

        $query->orderBy($dto->sort_by, $dto->sort_direction);

        return $query->paginate($dto->per_page);
    }

    public function getById(int $id): ReminderType|null
    {
        return ReminderType::find($id);
    }

    public function create(CreateReminderTypeDTO $dto): ReminderType
    {
        return ReminderType::create(
            [
                'name' => $dto->name,
                'minutes_before' => $dto->minutes_before,
                'is_default' => $dto->is_default,
            ]
        );
    }

    public function resetDefault(): bool
    {
        return ReminderType::where('is_default', true)->update(['is_default' => false]);
    }

    public function update(ReminderType $reminderType, array $data): bool
    {
        return $reminderType->update($data);
    }
}
