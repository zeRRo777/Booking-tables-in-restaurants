<?php

namespace App\DTOs\User;

use App\DTOs\Contracts\UpdateUserDtoInterface;
use Spatie\LaravelData\Data;

class UpdateUserDTO extends Data implements UpdateUserDtoInterface
{
    public function __construct(
        public ?string $email,
        public ?string $name,
        public ?string $phone,
        public ?bool $is_blocked
    ) {}
}
