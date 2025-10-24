<?php


namespace App\Services;

use App\DTOs\Table\TableFilterDTO;
use App\Exceptions\NotFoundException;
use App\Models\Restaurant;
use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TableService
{
    public function __construct(
        protected TableRepositoryInterface $tableRepository,
    ) {}

    public function getTables(Restaurant $restaurant, TableFilterDTO $dto): LengthAwarePaginator
    {
        return $this->tableRepository->getFiltered($restaurant, $dto);
    }

    public function getTable(int $id): Table
    {
        $table = $this->tableRepository->findById($id);

        if (!$table) {
            throw new NotFoundException('Стол не найден!');
        }

        return $table;
    }
}
