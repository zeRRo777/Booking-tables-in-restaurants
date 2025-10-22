<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RestaurantRepositoryInterface;
use App\DTOs\Restaurant\RestaurantFilterDTO;
use App\Models\Restaurant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentRestaurantRepository implements RestaurantRepositoryInterface
{
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
            $q->whereHas('status', fn(Builder $sq) => $sq->where('name', $statusName));
        });
    }

    public function getById(int $id): Restaurant|null
    {
        return Restaurant::with(['status', 'chain'])->find($id);
    }

    public function applyFiltersAndPaginate(Builder $query, RestaurantFilterDTO $dto): LengthAwarePaginator
    {
        $this->applyFilters($query, $dto);

        $query->orderBy($dto->sort_by, $dto->sort_direction);

        return $query->paginate($dto->per_page);
    }
}
