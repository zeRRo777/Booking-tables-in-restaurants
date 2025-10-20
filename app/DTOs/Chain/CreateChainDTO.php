<?php

namespace App\DTOs\Chain;

use Spatie\LaravelData\Data;

class CreateChainDTO extends Data
{
    public function __construct(
        public string $name,
        public string $status,
    ) {}
}
