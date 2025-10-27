<?php

namespace App\Repositories\Eloquent;

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
}
