<?php

namespace App\DTOs\ReservationStatuse;

use Spatie\LaravelData\Data;

class CreateReservationStatuseDTO extends Data
{
    public function __construct(
        public string $name,
    ) {}
}
