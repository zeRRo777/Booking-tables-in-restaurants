<?php

namespace App\DTOs\ReminderType;

use Spatie\LaravelData\Data;

class ReminderTypeFilterDTO extends Data
{
    public function __construct(
        public int $per_page = 10,
        public string $sort_by = 'id',
        public string $sort_direction = 'asc',
    ) {}
}
