<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;

class CreateUserRestaurantBlockedDTO extends Data
{
    public function __construct(
        public int $user_id,
        public int $restaurant_id,
        public ?string $block_reason,
        public int $blocked_by,
    ) {}
}
