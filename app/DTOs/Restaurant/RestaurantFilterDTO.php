<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;

class RestaurantFilterDTO extends Data
{
    public function __construct(
        public ?string $name,
        public ?string $type_kitchen,
        public ?string $chain,
        public ?string $address,
        public ?string $status,
        public int $per_page = 10,
        public string $sort_by = 'id',
        public string $sort_direction = 'asc',
    ) {}
}
