<?php

namespace App\Services;

use App\DTOs\Restaurant\ChangeStatusDTO;
use App\DTOs\Restaurant\CreateRestaurantDTO;
use App\DTOs\Restaurant\RestaurantFilterDTO;
use App\DTOs\Restaurant\UpdateRestaurantDTO;
use App\Exceptions\NotFoundException;
use App\Models\Restaurant;
use App\Models\RestaurantStatuse;
use App\Models\User;
use App\Repositories\Contracts\RestaurantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RestaurantService
{
    public function __construct(
        protected RestaurantRepositoryInterface $restaurantRepository
    ) {}

    public function getRestaurants(RestaurantFilterDTO $dto, ?User $user): LengthAwarePaginator
    {
        $query = Restaurant::query()
            ->with(['status', 'chain']);

        $query->forUser($user);

        return $this->restaurantRepository->applyFiltersAndPaginate($query, $dto);
    }

    public function getRestaurant(int $id): Restaurant
    {
        $restaurant = $this->restaurantRepository->getById($id);

        if (!$restaurant) {
            throw new NotFoundException('Ресторан не найден!');
        }

        return $restaurant;
    }

    public function createRestaurant(CreateRestaurantDTO $dto): Restaurant
    {
        return $this->restaurantRepository->create($dto);
    }

    public function updateRestaurant(Restaurant $restaurant, UpdateRestaurantDTO $dto): Restaurant
    {
        $oldChainId = $restaurant->restaurant_chain_id;

        $data = array_filter(
            $dto->toArray(),
            fn($value) => !is_null($value)
        );

        if (empty($data)) {
            return $restaurant;
        }

        $isChainChanging = array_key_exists('restaurant_chain_id', $data) && $data['restaurant_chain_id'] !== $oldChainId;

        DB::transaction(function () use ($restaurant, $data, $isChainChanging): void {
            if ($isChainChanging) {
                $restaurant->administrators()->sync([]);
            }
            $this->restaurantRepository->update($restaurant, $data);
        });

        return $restaurant->refresh()->load(['status', 'chain']);
    }

    public function deleteRestaurant(Restaurant $restaurant, bool $real = false): void
    {
        DB::transaction(function () use ($restaurant, $real): void {
            $this->restaurantRepository->delete($restaurant, $real);
        });
    }

    public function changeStatus(int $id, ChangeStatusDTO $dto): Restaurant
    {
        $resataurant = $this->getRestaurant($id);

        if ($resataurant->status->name === $dto->status) {
            return $resataurant;
        }

        $status = RestaurantStatuse::where('name', $dto->status)->firstOrFail();

        $data = [
            'status_id' => $status->id,
        ];

        $this->restaurantRepository->update($resataurant, $data);

        return $resataurant->refresh()->load(['status', 'chain']);
    }
}
