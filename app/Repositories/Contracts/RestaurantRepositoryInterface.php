<?php

namespace App\Repositories\Contracts;

use App\DTOs\Restaurant\AvailabilityRestaurantDTO;
use App\DTOs\Restaurant\CreateRestaurantDTO;
use App\DTOs\Restaurant\RestaurantFilterDTO;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface RestaurantRepositoryInterface
{
    public function applyFiltersAndPaginate(Builder $query, RestaurantFilterDTO $dto): LengthAwarePaginator;

    public function getById(int $id): ?Restaurant;

    public function create(CreateRestaurantDTO $dto): Restaurant;

    public function update(Restaurant $restaurant, array $data): bool;

    public function delete(Restaurant $restaurant, bool $real = false): bool;

    public function getAllAdmins(Restaurant $restaurant): Collection;

    public function findAvailableTables(Carbon $startTime, Carbon $endTime, AvailabilityRestaurantDTO $dto): LengthAwarePaginator;
}
