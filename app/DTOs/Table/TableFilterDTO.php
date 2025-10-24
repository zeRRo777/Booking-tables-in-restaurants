<?php

namespace App\DTOs\Table;

use Spatie\LaravelData\Data;

class TableFilterDTO extends Data
{
    public function __construct(
        public ?string $zone,
        public int $per_page = 10,
        public string $sort_by = 'id',
        public string $sort_direction = 'asc',
    ) {}
}
