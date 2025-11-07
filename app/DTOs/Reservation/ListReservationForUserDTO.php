<?php

namespace App\DTOs\Reservation;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class ListReservationForUserDTO extends ListReservationDTO
{
    public function __construct(
        ?int $status,
        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y')]
        ?Carbon $date_from,
        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y')]
        ?Carbon $date_to,
        ?string $sort_by,
        ?string $sort_direction,
        ?int $per_page,
        public ?int $restaurant
    ) {
        parent::__construct($status, $date_from, $date_to, $sort_by, $sort_direction, $per_page);
    }
}
