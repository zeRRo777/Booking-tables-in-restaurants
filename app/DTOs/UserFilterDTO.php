<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class UserFilterDTO extends Data
{
    public function __construct(
        public ?string $email,
        public ?string $name,
        public ?string $phone,
        public ?bool $is_blocked,
        public int $per_page = 10,
    ) {}
}
