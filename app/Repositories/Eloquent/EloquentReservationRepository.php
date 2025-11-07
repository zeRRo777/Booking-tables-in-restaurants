<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\DTOs\Reservation\CreateReservationDTO;
use App\DTOs\Reservation\ListReservationDTO;
use App\DTOs\Reservation\ListReservationForRestaurantDTO;
use App\DTOs\Reservation\ListReservationForUserDTO;
use App\Models\Reservation;
use App\Models\ReservationStatuse;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentReservationRepository implements ReservationRepositoryInterface
{
    public function create(CreateReservationDTO $dto): Reservation
    {
        $status = ReservationStatuse::where('name', $dto->status)->firstOrFail();

        return Reservation::create([
            'special_wish' => $dto->special_wish,
            'starts_at' => $dto->starts_at,
            'ends_at' => $dto->ends_at,
            'user_id' => $dto->user_id,
            'restaurant_id' => $dto->restaurant_id,
            'count_people' => $dto->count_people,
            'reminder_type_id' => $dto->reminder_type_id,
            'table_id' => $dto->table_id,
            'status_id' => $status->id,
        ]);
    }

    public function getById(int $id): Reservation|null
    {
        return Reservation::find($id);
    }

    public function update(Reservation $reservation, array $data): bool
    {
        return $reservation->update($data);
    }

    public function delete(Reservation $reservation): bool
    {
        return $reservation->forceDelete();
    }

    public function list(ListReservationDTO $dto, User|Restaurant $scope): LengthAwarePaginator
    {
        $query = Reservation::query();

        $this->applyScope($query, $scope);

        $this->applyFilters($query, $dto);

        $this->applySorting($query, $dto);

        $query->with(['user', 'table', 'restaurant', 'status', 'reminderType']);

        return $query->paginate($dto->per_page);
    }

    private function applyFilters(Builder $query, ListReservationDTO $dto): void
    {
        $query->when($dto->status, fn($query, $status) => $query->where('status_id', $status));

        $query->when($dto->date_from, fn($query, $date) => $query->whereDate('starts_at', '>=', $date));

        $query->when($dto->date_to, fn($query, $date) => $query->whereDate('starts_at', '<=', $date));

        if ($dto instanceof ListReservationForUserDTO) {
            $query->when($dto->restaurant, fn($query, $restaurant_id) => $query->where('restaurant_id', $restaurant_id));
        }

        if ($dto instanceof ListReservationForRestaurantDTO) {
            $query->when($dto->table_number, function ($query, $table_number) {
                $query->whereHas('table', function ($query) use ($table_number) {
                    $query->where('number', $table_number);
                });
            });

            $query->when($dto->table_zone, function ($query, $table_zone) {
                $query->whereHas('table', function ($query) use ($table_zone) {
                    $query->where('zone', 'like', "%{$table_zone}%");
                });
            });
        }
    }

    private function applyScope(Builder $query, User|Restaurant $scope): void
    {
        if ($scope instanceof User) {
            $query->where('user_id', $scope->id);
        } elseif ($scope instanceof Restaurant) {
            $query->where('restaurant_id', $scope->id);
        }
    }

    public function applySorting(Builder $query, ListReservationDTO $dto): void
    {
        $query->orderBy($dto->sort_by, $dto->sort_direction);
    }
}
