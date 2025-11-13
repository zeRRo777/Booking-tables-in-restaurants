<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class AvailabilityRestaurantDTO extends Data
{
    public function __construct(
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon $date,
        #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
        public Carbon $time,
        public ?string $zone,
        public ?int $count_guests,
        public int $restaurant_id,
        public string $sort_by = 'number',
        public string $sort_direction = 'asc',
        public int $per_page = 10,
    ) {}
}
