<?php

namespace App\Repositories\Contracts;

use App\DTOs\Restaurant\RestaurantScheduleFilterDTO;
use App\Models\Restaurant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface RestaurantScheduleRepositoryInterface
{
    public function applyFiltersAndPaginate(Builder $query, RestaurantScheduleFilterDTO $dto): LengthAwarePaginator;
}
