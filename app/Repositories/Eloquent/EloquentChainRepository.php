<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Chain\ChainFilterDTO;
use App\Models\RestaurantChain;
use App\Models\User;
use App\Repositories\Contracts\ChainRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentChainRepository implements ChainRepositoryInterface
{
    public function getAllFiltered(ChainFilterDTO $dto): LengthAwarePaginator
    {
        $query = RestaurantChain::query();

        $this->applyCommonFilters($query, $dto);

        return $query->paginate($dto->per_page);
    }

    public function getForChainAdminFiltered(ChainFilterDTO $dto, User $user): LengthAwarePaginator
    {
        $query = RestaurantChain::query()->where(function ($q) use ($user) {
            $q->whereHas('status', fn($statusQuery) => $statusQuery->where('name', 'active'))
                ->orWhereHas('superAdmins', fn($adminQuery) => $adminQuery->where('users.id', $user->id));
        });

        $this->applyCommonFilters($query, $dto);

        return $query->paginate($dto->per_page);
    }

    private function applyCommonFilters(Builder $query, ChainFilterDTO $dto): void
    {
        $query->with('status');

        $query->when($dto->name, function ($q) use ($dto) {
            $q->where('name', 'like', '%' . $dto->name . '%');
        });

        $query->when($dto->status, function ($q) use ($dto) {
            $q->whereHas('status', function ($statusQuery) use ($dto) {
                $statusQuery->where('name', $dto->status);
            });
        });
    }
}
