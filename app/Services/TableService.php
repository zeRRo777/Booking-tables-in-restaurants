<?php


namespace App\Services;

use App\DTOs\Table\CreateTableDTO;
use App\DTOs\Table\TableFilterDTO;
use App\DTOs\Table\UpdateTableDTO;
use App\Exceptions\NotFoundException;
use App\Models\Restaurant;
use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    public function createTable(CreateTableDTO $dto): Table
    {
        return $this->tableRepository->create($dto);
    }

    public function updateTable(Table $table, UpdateTableDTO $dto): Table
    {
        $data = array_filter(
            $dto->toArray(),
            fn($value) => !is_null($value)
        );

        if (empty($data)) {
            return $table;
        }

        $this->tableRepository->update($table, $data);

        return $table->refresh()->load('restaurant');
    }

    public function deleteTable(Table $table, bool $real = false): void
    {
        DB::transaction(function () use ($table, $real): void {
            $this->tableRepository->delete($table, $real);
        });
    }
}
