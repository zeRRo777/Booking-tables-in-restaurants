<?php

namespace App\DTOs\User;

use Spatie\LaravelData\Data;

class AddUserRoleDTO extends Data
{
    public function __construct(
        public string $name,
    ) {}
}
