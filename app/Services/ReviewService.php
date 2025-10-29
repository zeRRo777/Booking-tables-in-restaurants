<?php

namespace App\Services;

use App\DTOs\Review\ReviewFilterDTO;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
    public function __construct(
        protected ReviewRepositoryInterface $reviewRepository,
    ) {}

    public function getReviews(ReviewFilterDTO $dto): LengthAwarePaginator
    {
        return $this->reviewRepository->getFiltered($dto);
    }
}
