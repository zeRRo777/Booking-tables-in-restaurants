<?php

namespace App\DTOs\Table;

use Spatie\LaravelData\Data;

class CreateTableDTO extends Data
{
    public function __construct(
        public int $number,
        public int $capacity_min,
        public int $capacity_max,
        public ?string $zone,
        public int $restaurant_id,
    ) {}
}
