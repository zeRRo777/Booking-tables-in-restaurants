<?php

namespace App\Repositories\Contracts;

use App\DTOs\ReminderType\ReminderTypeFilterDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReminderTypeInterface
{
    public function getAll(ReminderTypeFilterDTO $dto): LengthAwarePaginator;
}
