<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class UpdateRestaurantScheduleDTO extends Data
{
    public function __construct(
        #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
        public ?Carbon $opens_at,
        #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
        public ?Carbon $closes_at,
        public ?bool $is_closed,
        public ?string $description,
    ) {}
}
