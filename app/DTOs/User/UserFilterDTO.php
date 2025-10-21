<?php

namespace App\DTOs\User;

use Spatie\LaravelData\Data;

class UserFilterDTO extends Data
{
    public function __construct(
        public ?string $email,
        public ?string $name,
        public ?string $phone,
        public ?bool $is_blocked,
        public int $per_page = 10,
        public string $sort_by = 'id',
        public string $sort_direction = 'asc',
    ) {}
}
