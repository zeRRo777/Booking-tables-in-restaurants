<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Restaurant\CreateRestaurantScheduleDTO;
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

    public function create(CreateRestaurantScheduleDTO $dto): RestaurantSchedule
    {
        return RestaurantSchedule::create([
            'date' => $dto->date,
            'restaurant_id' => $dto->restaurant_id,
            'opens_at' => $dto->opens_at,
            'closes_at' => $dto->closes_at,
            'is_closed' => $dto->is_closed,
            'description' => $dto->description,
        ]);
    }

    public function update(RestaurantSchedule $schedule, array $data): bool
    {
        return RestaurantSchedule::where('restaurant_id', $schedule->restaurant_id)
            ->where('date', $schedule->date)
            ->update($data);
    }
}
