<?php

namespace App\Repositories\Contracts;

use App\DTOs\Restaurant\RestaurantFilterDTO;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RestaurantRepositoryInterface
{
    public function getFiltered(RestaurantFilterDTO $dto, ?User $user): LengthAwarePaginator;

    public function getById(int $id): ?Restaurant;
}
