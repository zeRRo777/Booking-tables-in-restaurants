<?php

namespace App\Repositories\Eloquent;

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
}
