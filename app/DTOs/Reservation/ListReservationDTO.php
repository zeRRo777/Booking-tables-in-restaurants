<?php

namespace App\DTOs\Reservation;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class ListReservationDTO extends Data
{
    public function __construct(
        public ?int $status,
        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y')]
        public ?Carbon $date_from,
        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y')]
        public ?Carbon $date_to,
        public ?string $sort_by = 'created_at',
        public ?string $sort_direction = 'asc',
        public ?int $per_page = 10,
    ) {}
}
