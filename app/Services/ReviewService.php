<?php

namespace App\Services;

use App\DTOs\Review\CreateReviewDTO;
use App\DTOs\Review\ReviewFilterDTO;
use App\DTOs\Review\UpdateReviewDTO;
use App\Exceptions\NotFoundException;
use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    public function updateReview(Review $review, UpdateReviewDTO $dto): Review
    {
        $data = array_filter(
            $dto->toArray(),
            fn($value) => !is_null($value)
        );

        if (empty($data)) {
            return $review;
        }

        $this->reviewRepository->update($review, $data);

        return $review->refresh()->load(['restaurant.chain', 'user']);
    }

    public function deleteReview(Review $review, bool $real = false): void
    {
        DB::transaction(function () use ($review, $real): void {
            $this->reviewRepository->delete($review, $real);
        });
    }
}
