<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;

class BlockedUserFilterDTO extends Data
{
    public function __construct(
        public ?string $email,
        public ?string $name,
        public ?string $phone,
        public int $restaurant_id,
        public int $per_page = 10,
        public string $sort_by = 'id',
        public string $sort_direction = 'asc',
    ) {}
}
