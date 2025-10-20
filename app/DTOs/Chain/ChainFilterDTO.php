<?php

namespace App\DTOs\Chain;

use Spatie\LaravelData\Data;

class ChainFilterDTO extends Data
{
    public function __construct(
        public ?string $name = null,
        public int $per_page = 10,
        public ?string $status = null,
    ) {}
}
