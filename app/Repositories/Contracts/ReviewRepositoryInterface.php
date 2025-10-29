<?php

namespace App\Repositories\Contracts;

use App\DTOs\Review\CreateReviewDTO;
use App\DTOs\Review\ReviewFilterDTO;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function getFiltered(ReviewFilterDTO $dto): LengthAwarePaginator;

    public function findById(int $id): ?Review;

    public function create(CreateReviewDTO $dto): Review;
}
