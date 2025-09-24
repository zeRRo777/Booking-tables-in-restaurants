<?php

namespace App\DTOs;

use Spatie\LaravelData\Data;

class CreateUserDTO extends Data
{
    public function __construct(
        public string $email,
        public string $name,
        public string $phone,
        public string $password,
    ) {}
}
