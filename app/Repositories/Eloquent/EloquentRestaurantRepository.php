<?php

namespace App\Repositories\Eloquent;

use App\DTOs\Restaurant\AvailabilityRestaurantDTO;
use App\DTOs\Restaurant\CreateRestaurantDTO;
use App\Repositories\Contracts\RestaurantRepositoryInterface;
use App\DTOs\Restaurant\RestaurantFilterDTO;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantStatuse;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

    public function create(CreateRestaurantDTO $dto): Restaurant
    {
        $status = RestaurantStatuse::where('name', $dto->status)->firstOrFail();

        $restaurant = Restaurant::create(
            [
                'name' => $dto->name,
                'address' => $dto->address,
                'description' => $dto->description,
                'type_kitchen' => $dto->type_kitchen,
                'price_range' => $dto->price_range,
                'weekdays_opens_at' => $dto->weekdays_opens_at,
                'weekdays_closes_at' => $dto->weekdays_closes_at,
                'weekend_opens_at' => $dto->weekend_opens_at,
                'weekend_closes_at' => $dto->weekend_closes_at,
                'cancellation_policy' => $dto->cancellation_policy,
                'restaurant_chain_id' => $dto->restaurant_chain_id,
                'status_id' => $status->id,
            ]
        );

        return $restaurant->load(['status', 'chain']);
    }

    public function update(Restaurant $restaurant, array $data): bool
    {
        return $restaurant->update($data);
    }

    public function delete(Restaurant $restaurant, bool $real = false): bool
    {
        return $real ? $restaurant->forceDelete() : $restaurant->delete();
    }

    public function getAllAdmins(Restaurant $restaurant): Collection
    {
        return $restaurant->administrators;
    }

    public function findAvailableTables(Carbon $startTime, Carbon $endTime, AvailabilityRestaurantDTO $dto): LengthAwarePaginator
    {
        $busyTableIds = Reservation::query()
            ->where('restaurant_id', $dto->restaurant_id)
            ->where(function (Builder $query) use ($startTime, $endTime) {
                $query->where('starts_at', '<', $endTime)
                    ->where('ends_at', '>', $startTime);
            })
            ->whereHas('status', function (Builder $query) {
                $query->whereNotIn('name', ['Cancelled', 'No-show']);
            })
            ->pluck('table_id')
            ->unique();

        $availableTablesQuery = Table::query()
            ->where('restaurant_id', $dto->restaurant_id)
            ->whereNotIn('id', $busyTableIds);

        $availableTablesQuery->when($dto->count_guests, function (Builder $query, $countGuests) {
            $query->where('capacity_min', '<=', $countGuests)
                ->where('capacity_max', '>=', $countGuests);
        });

        $availableTablesQuery->when($dto->zone, function (Builder $query, $zone) {
            $query->where('zone', $zone);
        });

        $availableTablesQuery->orderBy($dto->sort_by, $dto->sort_direction);

        return $availableTablesQuery->paginate($dto->per_page);
    }
}
