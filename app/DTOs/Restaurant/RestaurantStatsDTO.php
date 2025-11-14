<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class RestaurantStatsDTO extends Data
{
    public function __construct(
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon $date,
        public string $period,
    ) {}
}
