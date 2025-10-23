<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;


class ChangeStatusDTO extends Data
{
    public function __construct(
        public string $status,
    ) {}
}
