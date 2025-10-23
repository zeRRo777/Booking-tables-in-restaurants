<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class CreateRestaurantDTO extends Data
{
    public function __construct(
        public string $name,
        public string $address,
        public ?string $description,
        public ?string $type_kitchen,
        public ?string $price_range,

        #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
        public ?Carbon $weekdays_opens_at,

        #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
        public ?Carbon $weekdays_closes_at,

        #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
        public ?Carbon $weekend_opens_at,

        #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
        public ?Carbon $weekend_closes_at,

        public ?string $cancellation_policy,
        public ?int $restaurant_chain_id,
        public string $status = 'moderation',
    ) {}
}
