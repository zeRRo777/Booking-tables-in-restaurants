<?php

namespace App\Repositories\Contracts;

use App\DTOs\Restaurant\BlockedUserFilterDTO;
use App\DTOs\Restaurant\CreateUserRestaurantBlockedDTO;
use App\DTOs\Restaurant\DeleteUserRestaurantBlockedDTO;
use App\Models\UserRestaurantBlocked;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRestaurantStatuseRepositoryInterface
{
    public function getBlockedUsersforRestaurant(BlockedUserFilterDTO $dto): LengthAwarePaginator;

    public function create(CreateUserRestaurantBlockedDTO $dto): UserRestaurantBlocked;

    public function delete(DeleteUserRestaurantBlockedDTO $dto, bool $real = false): bool;
}
