<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Chain\ChainFilterDTO;
use App\DTOs\Chain\CreateChainDTO;
use App\Models\ChainStatuse;
use App\Models\RestaurantChain;
use App\Models\User;
use App\Repositories\Contracts\ChainRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentChainRepository implements ChainRepositoryInterface
{
    public function findById(int $id): RestaurantChain|null
    {
        return RestaurantChain::with('status')->find($id);
    }

    public function create(CreateChainDTO $dto): RestaurantChain
    {

        $status = ChainStatuse::where('name', $dto->status)->firstOrFail();

        $chain = RestaurantChain::create([
            'name' => $dto->name,
            'status_id' => $status->id
        ]);

        return $chain->load('status');
    }

    public function update(RestaurantChain $chain, array $data): bool
    {
        return $chain->update($data);
    }

    public function delete(RestaurantChain $chain, bool $real = false): bool
    {
        return $real ? $chain->forceDelete() : $chain->delete();
    }

    public function applyFiltersAndPaginate(Builder $query, ChainFilterDTO $dto): LengthAwarePaginator
    {
        $query->when($dto->name, function ($q) use ($dto) {
            $q->whereLike('name', '%' . $dto->name . '%');
        });

        $query->when($dto->status, function ($q) use ($dto) {
            $q->whereHas('status', function ($statusQuery) use ($dto) {
                $statusQuery->where('name', $dto->status);
            });
        });

        $query->orderBy($dto->sort_by, $dto->sort_direction);

        return $query->paginate($dto->per_page);
    }

    public function getAllAdmins(RestaurantChain $chain): Collection
    {
        return $chain->superAdmins;
    }
}
