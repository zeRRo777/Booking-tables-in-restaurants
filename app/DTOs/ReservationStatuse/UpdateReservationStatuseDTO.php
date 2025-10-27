<?php

namespace App\DTOs\ReservationStatuse;

use Spatie\LaravelData\Data;

class UpdateReservationStatuseDTO extends Data
{
    public function __construct(
        public ?string $name,
    ) {}
}
