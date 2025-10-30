<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Restaurant\BlockedUserFilterDTO;
use App\DTOs\Restaurant\CreateUserRestaurantBlockedDTO;
use App\DTOs\Restaurant\DeleteUserRestaurantBlockedDTO;
use App\Models\UserRestaurantBlocked;
use App\Repositories\Contracts\UserRestaurantStatuseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentUserRestaurantStatuseRepository implements UserRestaurantStatuseRepositoryInterface
{
    public function getBlockedUsersforRestaurant(BlockedUserFilterDTO $dto): LengthAwarePaginator
    {
        $query = UserRestaurantBlocked::query();

        $query->where('restaurant_id', $dto->restaurant_id);

        $query->when($dto->email, function ($q) use ($dto) {
            $q->whereLike('email', $dto->email);
        });

        $query->when($dto->name, function ($q) use ($dto) {
            $q->whereLike('email', $dto->name);
        });

        $query->when($dto->phone, function ($q) use ($dto) {
            $q->whereLike('email', $dto->phone);
        });

        $query->orderBy($dto->sort_by, $dto->sort_direction);

        return $query->with(['user', 'restaurant', 'blocker'])->paginate($dto->per_page);
    }

    public function create(CreateUserRestaurantBlockedDTO $dto): UserRestaurantBlocked
    {
        return UserRestaurantBlocked::create([
            'user_id' => $dto->user_id,
            'restaurant_id' => $dto->restaurant_id,
            'blocked_by' => $dto->blocked_by,
            'block_reason' => $dto->block_reason,
        ]);
    }

    public function delete(DeleteUserRestaurantBlockedDTO $dto, bool $real = false): bool
    {
        if ($real) {
            return UserRestaurantBlocked::where('user_id', $dto->user_id)
                ->where('restaurant_id', $dto->restaurant_id)
                ->forceDelete();
        }

        return UserRestaurantBlocked::where('user_id', $dto->user_id)
            ->where('restaurant_id', $dto->restaurant_id)
            ->delete();
    }
}
