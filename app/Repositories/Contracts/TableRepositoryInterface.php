<?php

namespace App\Repositories\Contracts;

use App\DTOs\Table\TableFilterDTO;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TableRepositoryInterface
{
    public function getFiltered(Restaurant $restaurant, TableFilterDTO $dto): LengthAwarePaginator;

    public function findById(int $id): ?Table;
}
