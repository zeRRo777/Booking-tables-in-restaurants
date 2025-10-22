<?php

namespace App\Services;

use App\DTOs\Restaurant\RestaurantFilterDTO;
use App\Exceptions\NotFoundException;
use App\Models\Restaurant;
use App\Models\User;
use App\Repositories\Contracts\RestaurantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}
