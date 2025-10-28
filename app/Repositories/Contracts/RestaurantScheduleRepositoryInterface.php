<?php

namespace App\Repositories\Contracts;

use App\DTOs\Restaurant\CreateRestaurantScheduleDTO;
use App\DTOs\Restaurant\RestaurantScheduleFilterDTO;
use App\DTOs\Restaurant\RestaurantScheduleShowDTO;
use App\Models\RestaurantSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface RestaurantScheduleRepositoryInterface
{
    public function applyFiltersAndPaginate(Builder $query, RestaurantScheduleFilterDTO $dto): LengthAwarePaginator;

    public function findByRestaurantAndDate(RestaurantScheduleShowDTO $dto): ?RestaurantSchedule;

    public function create(CreateRestaurantScheduleDTO $dto): RestaurantSchedule;
}
