<?php

namespace App\Services;

use App\DTOs\Restaurant\BlockedUserFilterDTO;
use App\DTOs\Restaurant\ChangeStatusDTO;
use App\DTOs\Restaurant\CreateRestaurantDTO;
use App\DTOs\Restaurant\CreateRestaurantScheduleDTO;
use App\DTOs\Restaurant\CreateUserRestaurantBlockedDTO;
use App\DTOs\Restaurant\CreateUserRestaurantStatusDTO;
use App\DTOs\Restaurant\DeleteUserRestaurantBlockedDTO;
use App\DTOs\Restaurant\RestaurantFilterDTO;
use App\DTOs\Restaurant\RestaurantScheduleFilterDTO;
use App\DTOs\Restaurant\RestaurantScheduleShowDTO;
use App\DTOs\Restaurant\UpdateRestaurantDTO;
use App\DTOs\Restaurant\UpdateRestaurantScheduleDTO;
use App\DTOs\User\CreateUserDTO;
use App\Exceptions\NotFoundException;
use App\Exceptions\UserNotHaveRoleException;
use App\Models\Restaurant;
use App\Models\RestaurantSchedule;
use App\Models\RestaurantStatuse;
use App\Models\User;
use App\Models\UserRestaurantBlocked;
use App\Models\UserRestaurantStatuse;
use App\Repositories\Contracts\RestaurantRepositoryInterface;
use App\Repositories\Contracts\RestaurantScheduleRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRestaurantStatuseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RestaurantService
{
    public function __construct(
        protected RestaurantRepositoryInterface $restaurantRepository,
        protected RestaurantScheduleRepositoryInterface $restaurantScheduleRepository,
        protected UserService $userService,
        protected RoleRepositoryInterface $roleRepository,
        protected UserRestaurantStatuseRepositoryInterface $userRestaurantStatuseRepository
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

    public function getSchedules(Restaurant $restaurant, RestaurantScheduleFilterDTO $dto): LengthAwarePaginator
    {
        $query = $restaurant->schedules()->getQuery();

        return $this->restaurantScheduleRepository->applyFiltersAndPaginate($query, $dto);
    }

    public function getSchedule(RestaurantScheduleShowDTO $dto): RestaurantSchedule
    {
        $schedule = $this->restaurantScheduleRepository->findByRestaurantAndDate($dto);

        if (!$schedule) {
            throw new NotFoundException('Расписание не найдено!');
        }

        return $schedule->load(['restaurant']);
    }

    public function createSchedule(CreateRestaurantScheduleDTO $dto): RestaurantSchedule
    {
        $schedule = $this->restaurantScheduleRepository->create($dto);

        return $schedule->load(['restaurant']);
    }

    public function updateSchedule(RestaurantSchedule $schedule, UpdateRestaurantScheduleDTO $dto): RestaurantSchedule
    {
        $data = $dto->except('opens_at', 'closes_at')->toArray();

        if (!is_null($dto->opens_at)) {
            $data['opens_at'] = $dto->opens_at->format('H:i:s');
        }

        if (!is_null($dto->closes_at)) {
            $data['closes_at'] = $dto->closes_at->format('H:i:s');
        }

        $data = array_filter($data, fn($value) => !is_null($value));

        if (empty($data)) {
            return $schedule;
        }

        $this->restaurantScheduleRepository->update($schedule, $data);

        $dtoShow = RestaurantScheduleShowDTO::from([
            'date' => $schedule->date,
            'id' => $schedule->restaurant_id,
        ]);

        return $this->restaurantScheduleRepository->findByRestaurantAndDate($dtoShow)->load(['restaurant']);
    }

    public function deleteSchedule(RestaurantSchedule $schedule, bool $real = false): void
    {
        DB::transaction(function () use ($schedule, $real): void {
            $this->restaurantScheduleRepository->delete($schedule, $real);
        });
    }

    public function getAdmins(Restaurant $restaurant): Collection
    {
        return $this->restaurantRepository->getAllAdmins($restaurant);
    }

    public function addAdmin(CreateUserDTO $dto, Restaurant $restaurant): void
    {
        DB::transaction(function () use ($dto, $restaurant) {
            $user = $this->userService->createUser($dto);
            $role = $this->roleRepository->findByName('restaurant_admin');
            $restaurant->administrators()->attach($user->id);
            if ($role) {
                $user->roles()->attach($role->id);
            }
        });
    }

    public function removeAdmin(User $user, Restaurant $restaurant): void
    {
        DB::transaction(function () use ($user, $restaurant) {
            $restaurant->administrators()->detach($user->id);
            if ($user->administeredRestaurants()->count() === 0) {
                $role = $this->roleRepository->findByName('restaurant_admin');
                if ($role) {
                    $user->roles()->detach($role->id);
                }
            }
        });
    }

    public function getBlockedUsers(BlockedUserFilterDTO $dto): LengthAwarePaginator
    {
        return $this->userRestaurantStatuseRepository->getBlockedUsersforRestaurant($dto);
    }

    public function addBlockedUser(CreateUserRestaurantBlockedDTO $dto): UserRestaurantBlocked
    {
        $blockedUser = $this->userRestaurantStatuseRepository->create($dto);

        return $blockedUser->load(['user', 'blocker', 'restaurant']);
    }

    public function deleteBlockedUser(DeleteUserRestaurantBlockedDTO $dto, bool $real = false): void
    {
        $this->userRestaurantStatuseRepository->delete($dto, $real);
    }
}
