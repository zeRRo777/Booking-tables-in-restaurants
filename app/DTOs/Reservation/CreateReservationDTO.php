<?php

namespace App\DTOs\Reservation;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class CreateReservationDTO extends Data
{
    public function __construct(
        public ?string $special_wish,
        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y H:i')]
        public Carbon $starts_at,
        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y H:i')]
        public Carbon $ends_at,
        public int $user_id,
        public int $restaurant_id,
        public int $count_people,
        public int $reminder_type_id,
        public int $table_id,
        public string $status = 'Pending',
    ) {}
}
