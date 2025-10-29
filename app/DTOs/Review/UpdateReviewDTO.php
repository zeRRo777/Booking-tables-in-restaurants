<?php

namespace App\DTOs\Review;

use Spatie\LaravelData\Data;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class UpdateReviewDTO extends Data
{
    public function __construct(
        public ?string $description,
        public ?int $rating,
    ) {}
}
