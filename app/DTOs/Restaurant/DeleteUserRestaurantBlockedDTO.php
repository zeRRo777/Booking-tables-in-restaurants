<?php

namespace App\DTOs\Restaurant;

use Spatie\LaravelData\Data;

class DeleteUserRestaurantBlockedDTO extends Data
{
    public function __construct(
        public int $user_id,
        public int $restaurant_id,
    ) {}
}
