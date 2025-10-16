<?php

namespace App\DTOs;

use App\DTOs\Contracts\UpdateUserDtoInterface;
use Spatie\LaravelData\Data;

class UpdateMeDTO extends Data implements UpdateUserDtoInterface
{
    public function __construct(
        public ?string $name,
    ) {}
}
