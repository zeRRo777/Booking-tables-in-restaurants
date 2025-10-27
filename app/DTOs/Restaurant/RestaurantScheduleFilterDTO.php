<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class RestaurantScheduleFilterDTO extends Data
{
    public function __construct(
        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y')]
        public ?Carbon $date_start,
        #[WithCast(DateTimeInterfaceCast::class, format: 'd.m.Y')]
        public ?Carbon $date_end,
        public int $per_page = 10,
        public string $sort_by = 'created_at',
        public string $sort_direction = 'asc',
    ) {}
}
