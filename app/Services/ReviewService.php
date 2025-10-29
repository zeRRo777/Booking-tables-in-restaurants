<?php

namespace App\Services;

use App\DTOs\Review\CreateReviewDTO;
use App\DTOs\Review\ReviewFilterDTO;
use App\Exceptions\NotFoundException;
use App\Models\Review;
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

    public function getReview(int $id): Review
    {
        $review = $this->reviewRepository->findById($id);

        if (!$review) {
            throw new NotFoundException('Отзыв не найден!');
        }

        return $review->load(['restaurant.chain', 'user']);
    }

    public function createReview(CreateReviewDTO $dto): Review
    {
        $review = $this->reviewRepository->create($dto);

        return $review->load(['restaurant.chain', 'user']);
    }
}
