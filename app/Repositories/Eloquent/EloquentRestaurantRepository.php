<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RestaurantRepositoryInterface;
use App\DTOs\Restaurant\RestaurantFilterDTO;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentRestaurantRepository implements RestaurantRepositoryInterface
{
    public function getFiltered(RestaurantFilterDTO $dto, User|null $user): LengthAwarePaginator
    {
        $query = Restaurant::query()->with(['chain', 'status']);

        $this->applyPermissionScope($query, $user);

        $this->applyFilters($query, $dto);

        $query->orderBy($dto->sort_by, $dto->sort_direction);

        return $query->paginate($dto->per_page);
    }

    private function applyPermissionScope(Builder $query, ?User $user): void
    {
        if ($user && $user->hasRole('superadmin')) {
            return;
        }

        if ($user && $user->hasRole('admin_chain')) {

            $query->where(function (Builder $q) use ($user) {
                $q->whereHas('status', fn($sq) => $sq->where('name', 'active'))
                    ->orWhereIn('restaurant_chain_id', $user->administeredChains()->pluck('id'));
            });
            return;
        }

        if ($user && $user->hasRole('admin_restaurant')) {

            $query->where(function (Builder $q) use ($user) {
                $q->whereHas('status', fn($sq) => $sq->where('name', 'active'))
                    ->orWhereIn('id', $user->administeredRestaurants()->pluck('id'));
            });
            return;
        }

        $query->whereHas('status', fn($q) => $q->where('name', 'active'));
    }

    private function applyFilters(Builder $query, RestaurantFilterDTO $dto): void
    {
        $query->when($dto->name, function (Builder $q) use ($dto) {
            $q->whereLike('name', "%{$dto->name}%");
        });

        $query->when($dto->address, function (Builder $q) use ($dto) {
            $q->whereLike('address', "%{$dto->address}%");
        });

        $query->when($dto->type_kitchen, function (Builder $q, $type) {
            $q->whereLike('type_kitchen',  "%{$type}%");
        });

        $query->when($dto->chain, function (Builder $q, $chainName) {
            $q->whereHas('chain', fn(Builder $sq) => $sq->where('name', $chainName));
        });

        $query->when($dto->status, function (Builder $q, $statusName) {
            $q->whereHas('status', fn(Builder $sq) => $sq->where('name', 'active'));
        });
    }
}
