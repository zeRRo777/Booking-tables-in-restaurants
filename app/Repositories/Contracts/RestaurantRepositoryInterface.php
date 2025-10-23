<?php

namespace App\Repositories\Contracts;

use App\DTOs\Restaurant\CreateRestaurantDTO;
use App\DTOs\Restaurant\RestaurantFilterDTO;
use App\Models\Restaurant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface RestaurantRepositoryInterface
{
    public function applyFiltersAndPaginate(Builder $query, RestaurantFilterDTO $dto): LengthAwarePaginator;

    public function getById(int $id): ?Restaurant;

    public function create(CreateRestaurantDTO $dto): Restaurant;

    public function update(Restaurant $restaurant, array $data): bool;

    public function delete(Restaurant $restaurant, bool $real = false): bool;
}
