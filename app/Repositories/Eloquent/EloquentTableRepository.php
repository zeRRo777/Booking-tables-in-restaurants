<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Table\CreateTableDTO;
use App\DTOs\Table\TableFilterDTO;
use App\Models\Restaurant;
use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentTableRepository implements TableRepositoryInterface
{
    public function getFiltered(Restaurant $restaurant, TableFilterDTO $dto): LengthAwarePaginator
    {
        $query = Table::query();

        $query->where('restaurant_id', $restaurant->id);

        $query->when($dto->zone, function ($q) use ($dto) {
            $q->where('zone', $dto->zone);
        });

        $query->orderBy($dto->sort_by, $dto->sort_direction);

        return $query->with('restaurant')->paginate($dto->per_page);
    }

    public function findById(int $id): Table|null
    {
        return Table::with('restaurant')->find($id);
    }

    public function create(CreateTableDTO $dto): Table
    {
        $table = Table::create([
            'number' => $dto->number,
            'restaurant_id' => $dto->restaurant_id,
            'capacity_min' => $dto->capacity_min,
            'capacity_max' => $dto->capacity_max,
            'zone' => $dto->zone,
        ]);

        return $table->load('restaurant');
    }

    public function update(Table $table, array $data): bool
    {
        return $table->update($data);
    }

    public function delete(Table $table, bool $real = false): bool
    {
        return $real ? $table->forceDelete() : $table->delete();
    }
}
