<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Restaurant\RestaurantScheduleFilterDTO;
use App\DTOs\Restaurant\RestaurantScheduleShowDTO;
use App\Models\RestaurantSchedule;
use App\Repositories\Contracts\RestaurantScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentRestaurantScheduleRepository implements RestaurantScheduleRepositoryInterface
{
    public function applyFiltersAndPaginate(Builder $query, RestaurantScheduleFilterDTO $dto): LengthAwarePaginator
    {
        $this->applyFilters($query, $dto);

        $query->orderBy($dto->sort_by, $dto->sort_direction);

        return $query->paginate($dto->per_page);
    }

    private function applyFilters(Builder $query, RestaurantScheduleFilterDTO $dto): void
    {
        $query->when($dto->date_start, function (Builder $q) use ($dto) {
            $q->where('date', '>=', $dto->date_start);
        });

        $query->when($dto->date_end, function (Builder $q) use ($dto) {
            $q->where('date', '<=', $dto->date_end);
        });
    }

    public function findByRestaurantAndDate(RestaurantScheduleShowDTO $dto): RestaurantSchedule|null
    {
        return RestaurantSchedule::where('restaurant_id', $dto->id)
            ->where('date', $dto->date)
            ->first();
    }
}
