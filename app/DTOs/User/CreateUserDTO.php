<?php

namespace App\DTOs\User;

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
