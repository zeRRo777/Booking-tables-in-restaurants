<?php

namespace App\DTOs\Review;

use Spatie\LaravelData\Data;

class ReviewFilterDTO extends Data
{
    public function __construct(
        public int $restaurant_id,
        public ?int $rating,
        public int $per_page = 10,
        public string $sort_by = 'created_at',
        public string $sort_direction = 'desc',
    ) {}
}
