<?php

namespace App\Services;

use App\DTOs\ReminderType\CreateReminderTypeDTO;
use App\DTOs\ReminderType\ReminderTypeFilterDTO;
use App\DTOs\ReminderType\UpdateReminderTypeDTO;
use App\Exceptions\NotFoundException;
use App\Models\ReminderType;
use App\Repositories\Contracts\ReminderTypeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReminderTypeService
{
    public function __construct(
        protected ReminderTypeInterface $reminderTypeRepository
    ) {}

    public function getAll(ReminderTypeFilterDTO $dto): LengthAwarePaginator
    {
        return $this->reminderTypeRepository->getAll($dto);
    }

    public function getType(int $id): ReminderType
    {
        $reminderType = $this->reminderTypeRepository->getById($id);

        if (!$reminderType) {
            throw new NotFoundException('Тип напоминания не найден');
        }

        return $reminderType;
    }

    public function createType(CreateReminderTypeDTO $dto): ReminderType
    {
        return DB::transaction(function () use ($dto): ReminderType {
            if ($dto->is_default) {
                $this->reminderTypeRepository->resetDefault();
            }

            return $this->reminderTypeRepository->create($dto);
        });
    }

    public function updateType(ReminderType $type, UpdateReminderTypeDTO $dto): ReminderType
    {
        $data = array_filter(
            $dto->toArray(),
            fn($value) => !is_null($value)
        );

        if (empty($data)) {
            return $type;
        }

        DB::transaction(function () use ($type, $data, $dto): void {
            if ($dto->is_default) {
                $this->reminderTypeRepository->resetDefault();
            }

            $this->reminderTypeRepository->update($type, $data);
        });

        return $type->refresh();
    }

    public function deleteType(int $id): void
    {
        $type = $this->getType($id);

        DB::transaction(function () use ($type): void {
            $this->reminderTypeRepository->delete($type);
        });
    }
}
