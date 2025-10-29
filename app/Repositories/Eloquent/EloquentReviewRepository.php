<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Review\CreateReviewDTO;
use App\DTOs\Review\ReviewFilterDTO;
use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentReviewRepository implements ReviewRepositoryInterface
{
    public function getFiltered(ReviewFilterDTO $dto): LengthAwarePaginator
    {
        $query = Review::query();

        $query->where('restaurant_id', $dto->restaurant_id);

        $query->when($dto->rating, function ($q) use ($dto) {
            $q->where('rating', $dto->rating);
        });

        $query->orderBy($dto->sort_by, $dto->sort_direction);

        return $query->with(['restaurant.chain', 'user'])->paginate($dto->per_page);
    }

    public function findById(int $id): Review|null
    {
        return Review::find($id);
    }

    public function create(CreateReviewDTO $dto): Review
    {
        return Review::create([
            'description' => $dto->description,
            'restaurant_id' => $dto->restaurant_id,
            'user_id' => $dto->user_id,
            'rating' => $dto->rating,
        ]);
    }
}
