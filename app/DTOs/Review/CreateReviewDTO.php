<?php

namespace App\DTOs\Review;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class CreateReviewDTO extends Data
{
    public function __construct(
        public string $description,
        public int $user_id,
        public int $restaurant_id,
        public int $rating,
    ) {}
}
