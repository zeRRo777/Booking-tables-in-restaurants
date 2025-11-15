<?php

namespace App\Http\Controllers;

use App\DTOs\Restaurant\RestaurantScheduleShowDTO;
use App\Exceptions\NotFoundException;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Requests\Restaurant\Blocked\DeleteRequest as BlockedDeleteRequest;
use App\Http\Requests\Restaurant\Blocked\IndexRequest as BlockedIndexRequest;
use App\Http\Requests\Restaurant\Blocked\StoreRequest as BlockedStoreRequest;
use App\Http\Requests\Restaurant\ChangeStatusRequest;
use App\Http\Requests\Restaurant\CheckAvailabilityRequest;
use App\Http\Requests\Restaurant\IndexRequest;
use App\Http\Requests\Restaurant\Schedules\DeleteRequest;
use App\Http\Requests\Restaurant\Schedules\IndexRequest as SchedulesIndexRequest;
use App\Http\Requests\Restaurant\Schedules\ShowRequest;
use App\Http\Requests\Restaurant\Schedules\StoreRequest as SchedulesStoreRequest;
use App\Http\Requests\Restaurant\Schedules\UpdateRequest as SchedulesUpdateRequest;
use App\Http\Requests\Restaurant\StatRequest;
use App\Http\Requests\Restaurant\StoreRequest;
use App\Http\Requests\Restaurant\UpdateRequest;
use App\Http\Resources\BlockedUserCollection;
use App\Http\Resources\BlockedUserResource;
use App\Http\Resources\OccupancyStatsResource;
use App\Http\Resources\RestaurantCollection;
use App\Http\Resources\RestaurantResource;
use App\Http\Resources\RestaurantScheduleCollection;
use App\Http\Resources\RestaurantScheduleResource;
use App\Http\Resources\TableCollection;
use App\Http\Resources\UserCollection;
use App\Models\RestaurantSchedule;
use App\Services\RestaurantService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 * name="Restaurants",
 * description="API для получение информации о ресторанах"
 * )
 */
/**
 * @OA\Tag(
 * name="RestaurantsSchedules",
 * description="API для получение информации о дополнительном времени работы ресторана"
 * )
 */
class RestaurantController extends Controller
{
    public function __construct(
        protected RestaurantService $restaurantService,
        protected UserService $userService
    ) {}

    /**
     * @OA\Get(
     * path="/restaurants",
     * tags={"Restaurants"},
     * summary="Получение списка ресторанов",
     * description="Получение списка ресторанов",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Поиск по названию",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="type_kitchen",
     * in="query",
     * description="Поиск по типу кухни",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="chain",
     * in="query",
     * description="Поиск по названию сети",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="address",
     * in="query",
     * description="Поиск по адресу",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="status",
     * in="query",
     * description="Поиск по cтатусу ресторана",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"active", "moderation", "rejected", "closed"},
     * default="active"
     * )
     * ),
     * @OA\Parameter(
     * name="sort_by",
     * in="query",
     * description="Поле сортировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"id", "name", "created_at", "weekdays_opens_at", "weekdays_closes_at"},
     * default="id"
     * )
     * ),
     * @OA\Parameter(
     * name="sort_direction",
     * in="query",
     * description="Направление сортировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"asc", "desc"},
     * default="asc"
     * )
     * ),
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Номер страницы",
     * required=false,
     * @OA\Schema(type="integer", default=1)
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Количество элементов на странице",
     * required=false,
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение списка ресторанов",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * description="Массив ресторанов",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * @OA\Property(property="description", type="string", example="Тестовый описание"),
     * @OA\Property(property="address", type="string", example="Тестовый адрес"),
     * @OA\Property(property="type_kitchen", type="string", example="Тестовый тип кухни"),
     * @OA\Property(property="price_range", type="string", example="1000-2000"),
     * @OA\Property(
     * property="working_hours",
     * type="object",
     * description="Рабочие часы",
     * @OA\Property(
     * property="weekdays",
     * type="object",
     * description="Рабочие часы в будние дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="21.00"),
     * ),
     * @OA\Property(
     * property="weekend",
     * type="object",
     * description="Рабочие часы в выходные дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="23.00"),
     * ),
     * ),
     * @OA\Property(
     * property="chain",
     * type="object",
     * description="Сеть ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовая сеть"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * @OA\Property(property="status", type="string", example="active"),
     * @OA\Property(property="cancellation_policy", type="string", example="cancellation_policy"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * ),
     * @OA\Property(
     * property="meta",
     * type="object",
     * description="Пагинация",
     * @OA\Property(property="total", type="integer", example=56),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="count_pages", type="integer", example=6),
     * ),
     * )
     * )
     * )
     */
    public function index(IndexRequest $request): RestaurantCollection
    {
        $dto = $request->toDto();

        $user = $request->user();

        $restaurants = $this->restaurantService->getRestaurants($dto, $user);

        return new RestaurantCollection($restaurants);
    }


    /**
     * @OA\GET(
     * path="/restaurants/{id}",
     * tags={"Restaurants"},
     * summary="Получение ресторана по id",
     * description="Получение ресторана по id",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана для получения",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение ресторана",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * @OA\Property(property="description", type="string", example="Тестовый описание"),
     * @OA\Property(property="address", type="string", example="Тестовый адрес"),
     * @OA\Property(property="type_kitchen", type="string", example="Тестовый тип кухни"),
     * @OA\Property(property="price_range", type="string", example="1000-2000"),
     * @OA\Property(
     * property="working_hours",
     * type="object",
     * description="Рабочие часы",
     * @OA\Property(
     * property="weekdays",
     * type="object",
     * description="Рабочие часы в будние дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="21.00"),
     * ),
     * @OA\Property(
     * property="weekend",
     * type="object",
     * description="Рабочие часы в выходные дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="23.00"),
     * ),
     * ),
     * @OA\Property(
     * property="chain",
     * type="object",
     * description="Сеть ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовая сеть"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * @OA\Property(property="status", type="string", example="active"),
     * @OA\Property(property="cancellation_policy", type="string", example="cancellation_policy"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * )
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Ресторан не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/not-found"),
     * @OA\Property(property="title", type="string", example="Object Not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Ресторан не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1")
     * )
     * ),
     * )
     */
    public function show(int $id): RestaurantResource
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        if (Gate::denies('view', $restaurant)) {
            throw new NotFoundException('Ресторан не найден!');
        }

        return new RestaurantResource($restaurant);
    }

    /**
     * @OA\Post(
     * path="/restaurants",
     * tags={"Restaurants"},
     * summary="Добавление нового ресторана",
     * description="Создает новый ресторан и возвращает его данные",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "address"},
     * @OA\Property(property="name", type="string", example="New Restaurant"),
     * @OA\Property(property="address", type="string", example="New Address"),
     * @OA\Property(property="description", type="string", example="New description"),
     * @OA\Property(property="type_kitchen", type="string", example="New type_kitchen"),
     * @OA\Property(property="price_range", type="string", example="New price_range"),
     * @OA\Property(property="weekdays_opens_at", type="string", example="09:00"),
     * @OA\Property(property="weekdays_closes_at", type="string", example="22:00"),
     * @OA\Property(property="weekend_opens_at", type="string", example="11:00"),
     * @OA\Property(property="weekend_closes_at", type="string", example="23:00"),
     * @OA\Property(property="cancellation_policy", type="string", example="cancellation_policy"),
     * @OA\Property(property="restaurant_chain_id", type="string", example="1"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Ресторан успешно создан",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * @OA\Property(property="description", type="string", example="Тестовый описание"),
     * @OA\Property(property="address", type="string", example="Тестовый адрес"),
     * @OA\Property(property="type_kitchen", type="string", example="Тестовый тип кухни"),
     * @OA\Property(property="price_range", type="string", example="1000-2000"),
     * @OA\Property(
     * property="working_hours",
     * type="object",
     * description="Рабочие часы",
     * @OA\Property(
     * property="weekdays",
     * type="object",
     * description="Рабочие часы в будние дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="21.00"),
     * ),
     * @OA\Property(
     * property="weekend",
     * type="object",
     * description="Рабочие часы в выходные дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="23.00"),
     * ),
     * ),
     * @OA\Property(
     * property="chain",
     * type="object",
     * description="Сеть ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовая сеть"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * @OA\Property(property="status", type="string", example="active"),
     * @OA\Property(property="cancellation_policy", type="string", example="cancellation_policy"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * )
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/restaurants"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле email обязательно для заполнения."))),
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Нет прав",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants")
     * )
     * ),
     * )
     */
    public function store(StoreRequest $request): RestaurantResource
    {
        $dto = $request->toDto();

        $restaurant = $this->restaurantService->createRestaurant($dto);

        return new RestaurantResource($restaurant);
    }

    /**
     * @OA\Patch(
     * path="/restaurants/{id}",
     * tags={"Restaurants"},
     * summary="Изменение данных ресторана",
     * description="Изменение данных ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "address"},
     * @OA\Property(property="name", type="string", example="Updated Restaurant"),
     * @OA\Property(property="address", type="string", example="Updated Address"),
     * @OA\Property(property="description", type="string", example="Updated description"),
     * @OA\Property(property="type_kitchen", type="string", example="Updated type_kitchen"),
     * @OA\Property(property="price_range", type="string", example="Updated price_range"),
     * @OA\Property(property="weekdays_opens_at", type="string", example="09:00"),
     * @OA\Property(property="weekdays_closes_at", type="string", example="22:00"),
     * @OA\Property(property="weekend_opens_at", type="string", example="11:00"),
     * @OA\Property(property="weekend_closes_at", type="string", example="23:00"),
     * @OA\Property(property="cancellation_policy", type="string", example="updated cancellation_policy"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Ресторан успешно изменен",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * @OA\Property(property="description", type="string", example="Тестовый описание"),
     * @OA\Property(property="address", type="string", example="Тестовый адрес"),
     * @OA\Property(property="type_kitchen", type="string", example="Тестовый тип кухни"),
     * @OA\Property(property="price_range", type="string", example="1000-2000"),
     * @OA\Property(
     * property="working_hours",
     * type="object",
     * description="Рабочие часы",
     * @OA\Property(
     * property="weekdays",
     * type="object",
     * description="Рабочие часы в будние дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="21.00"),
     * ),
     * @OA\Property(
     * property="weekend",
     * type="object",
     * description="Рабочие часы в выходные дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="23.00"),
     * ),
     * ),
     * @OA\Property(
     * property="chain",
     * type="object",
     * description="Сеть ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовая сеть"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * @OA\Property(property="status", type="string", example="active"),
     * @OA\Property(property="cancellation_policy", type="string", example="cancellation_policy"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * )
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле email обязательно для заполнения."))),
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Нет прав",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Ресторан не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/not-found"),
     * @OA\Property(property="title", type="string", example="Chain not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Ресторан не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Внутрення ошибка сервера",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/database-error"),
     * @OA\Property(property="title", type="string", example="Database Error"),
     * @OA\Property(property="status", type="string", example="500"),
     * @OA\Property(property="detail", type="string", example="Произошла ошибка базы данных!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1"),
     * )
     * ),
     * )
     */
    public function update(UpdateRequest $request, int $id): RestaurantResource
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('update', $restaurant);

        $dto = $request->toDto();

        $updatedRestaurant = $this->restaurantService->updateRestaurant($restaurant, $dto);

        return new RestaurantResource($updatedRestaurant);
    }

    /**
     * @OA\Delete(
     * path="/restaurants/{id}",
     * tags={"Restaurants"},
     * summary="Удаление ресторана",
     * description="Удаление ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Ресторан успешно удален",
     *),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Нет прав",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Сеть ресторана не найдена",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/user-not-found"),
     * @OA\Property(property="title", type="string", example="Chain not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Сеть ресторана не найдена!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Внутрення ошибка сервера",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/database-error"),
     * @OA\Property(property="title", type="string", example="Database Error"),
     * @OA\Property(property="status", type="string", example="500"),
     * @OA\Property(property="detail", type="string", example="Произошла ошибка базы данных!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1"),
     * )
     * ),
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('delete', $restaurant);

        $this->restaurantService->deleteRestaurant($restaurant, true);

        return response()->json(null, 204);
    }

    /**
     * @OA\Patch(
     * path="/restaurants/{id}/status",
     * tags={"Restaurants"},
     * summary="Изменение статуса ресторана",
     * description="Изменение статуса ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"status"},
     * @OA\Property(property="status", type="string", example="active"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Ресторан успешно изменен",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * @OA\Property(property="description", type="string", example="Тестовый описание"),
     * @OA\Property(property="address", type="string", example="Тестовый адрес"),
     * @OA\Property(property="type_kitchen", type="string", example="Тестовый тип кухни"),
     * @OA\Property(property="price_range", type="string", example="1000-2000"),
     * @OA\Property(
     * property="working_hours",
     * type="object",
     * description="Рабочие часы",
     * @OA\Property(
     * property="weekdays",
     * type="object",
     * description="Рабочие часы в будние дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="21.00"),
     * ),
     * @OA\Property(
     * property="weekend",
     * type="object",
     * description="Рабочие часы в выходные дни",
     * @OA\Property(property="opens_at", type="string", example="09.00"),
     * @OA\Property(property="closes_at", type="string", example="23.00"),
     * ),
     * ),
     * @OA\Property(
     * property="chain",
     * type="object",
     * description="Сеть ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовая сеть"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * @OA\Property(property="status", type="string", example="active"),
     * @OA\Property(property="cancellation_policy", type="string", example="cancellation_policy"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * )
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/status"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле email обязательно для заполнения."))),
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Нет прав",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/status")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Ресторан не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/not-found"),
     * @OA\Property(property="title", type="string", example="Chain not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Ресторан не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/status")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Внутрення ошибка сервера",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/database-error"),
     * @OA\Property(property="title", type="string", example="Database Error"),
     * @OA\Property(property="status", type="string", example="500"),
     * @OA\Property(property="detail", type="string", example="Произошла ошибка базы данных!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/status"),
     * )
     * ),
     * )
     */
    public function changeStatus(ChangeStatusRequest $request, int $id): RestaurantResource
    {
        $dto = $request->toDto();

        $upadatedRestaurant = $this->restaurantService->changeStatus($id, $dto);

        return new RestaurantResource($upadatedRestaurant);
    }

    /**
     * @OA\Get(
     * path="/restaurants/{id}/schedules",
     * tags={"RestaurantSchedules"},
     * summary="Получение списка дполнительного времени работы ресторана",
     * description="Получение списка дполнительного времени работы ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана для получения",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Parameter(
     * name="date_start",
     * in="query",
     * description="Поиск по дате с",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * * @OA\Parameter(
     * name="date_end",
     * in="query",
     * description="Поиск по дате до",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="sort_by",
     * in="query",
     * description="Поле сортировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"date", "created_at"},
     * default="created_at"
     * )
     * ),
     * @OA\Parameter(
     * name="sort_direction",
     * in="query",
     * description="Направление сортировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"asc", "desc"},
     * default="asc"
     * )
     * ),
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Номер страницы",
     * required=false,
     * @OA\Schema(type="integer", default=1)
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Количество элементов на странице",
     * required=false,
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение списка дополнительного времени работы ресторана",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * description="Массив доп времени работы ресторана",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="date", type="string", example="28.02.2001"),
     * @OA\Property(property="opens_at", type="string", example="18.00"),
     * @OA\Property(property="closes_at", type="string", example="20:00"),
     * @OA\Property(property="is_closed", type="boolean", example="true"),
     * @OA\Property(property="description", type="string", example="тестовое описание"),
     * @OA\Property(
     * property="restaurant",
     * type="object",
     * description="Ресторан",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * ),
     * @OA\Property(
     * property="meta",
     * type="object",
     * description="Пагинация",
     * @OA\Property(property="total", type="integer", example=56),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="count_pages", type="integer", example=6),
     * ),
     * )
     * )
     * )
     */
    public function restaurantSchedules(SchedulesIndexRequest $request, int $id): RestaurantScheduleCollection
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('viewAny', [RestaurantSchedule::class, $restaurant]);

        $dto = $request->toDto();

        $schedules = $this->restaurantService->getSchedules($restaurant, $dto);

        return new RestaurantScheduleCollection($schedules);
    }

    /**
     * @OA\GET(
     * path="/restaurants/{id}/schedules/{date}",
     * tags={"RestaurantSchedules"},
     * summary="Получение дполнительного времени работы ресторана",
     * description="Получение дполнительного времени работы ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторанa",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Parameter(
     * name="date",
     * in="path",
     * description="дата",
     * required=true,
     * @OA\Schema(
     * type="string",
     * example="2001-02-28"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение доп времени",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект доп времениресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="date", type="string", example="28.02.2001"),
     * @OA\Property(property="opens_at", type="string", example="18.00"),
     * @OA\Property(property="closes_at", type="string", example="20:00"),
     * @OA\Property(property="is_closed", type="boolean", example="true"),
     * @OA\Property(property="description", type="string", example="тестовое описание"),
     * @OA\Property(
     * property="restaurant",
     * type="object",
     * description="Ресторан",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * ),
     * ),
     * @OA\Response(
     * response=404,
     * description="Расписание не найдено",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/not-found"),
     * @OA\Property(property="title", type="string", example="Object not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Расписание не найдено!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules/28.02.2001")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Нет прав",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules/28.02.2001")
     * )
     * ),
     * )
     */
    public function resraurantSchedule(ShowRequest $request, int $id, string $date): RestaurantScheduleResource
    {
        $dto = $request->toDto();

        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('view', [RestaurantSchedule::class, $restaurant]);

        $schedule = $this->restaurantService->getSchedule($dto);

        return new RestaurantScheduleResource($schedule);
    }

    /**
     * @OA\POST(
     * path="/restaurants/{id}/schedules",
     * tags={"RestaurantSchedules"},
     * summary="Добавление дополнительного времени работы ресторана",
     * description="Добавление дополнительного времени работы ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторанa",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Данные для создания",
     * @OA\JsonContent(
     * @OA\Property(property="date", type="string", example="28.02.2022"),
     * @OA\Property(property="opens_at", type="string", example="09:00"),
     * @OA\Property(property="closes_at", type="string", example="18:00"),
     * @OA\Property(property="is_closed", type="string", example="false"),
     * @OA\Property(property="description", type="string", example="Тестовое описание"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное добавление роли",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект доп времениресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="date", type="string", example="28.02.2001"),
     * @OA\Property(property="opens_at", type="string", example="18.00"),
     * @OA\Property(property="closes_at", type="string", example="20:00"),
     * @OA\Property(property="is_closed", type="boolean", example="true"),
     * @OA\Property(property="description", type="string", example="тестовое описание"),
     * @OA\Property(
     * property="restaurant",
     * type="object",
     * description="Ресторан",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Доступ запрещен (нет прав)",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="Forbidden"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле name обязательно для заполнения."))),
     * )
     * ),
     * )
     */
    public function restaurantScheduleStore(SchedulesStoreRequest $request, int $id): RestaurantScheduleResource
    {
        $dto = $request->toDto();

        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('view', [RestaurantSchedule::class, $restaurant]);

        $schedule = $this->restaurantService->createSchedule($dto);

        return new RestaurantScheduleResource($schedule);
    }

    /**
     * @OA\Patch(
     * path="/restaurants/{id}/schedules/{date}",
     * tags={"RestaurantSchedules"},
     * summary="Изменение дополнительного времени работы ресторана",
     * description="Изменение дополнительного времени работы ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторанa",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Parameter(
     * name="date",
     * in="path",
     * description="дата",
     * required=true,
     * @OA\Schema(
     * type="string",
     * example="2001-02-28"
     * )
     * ),
     * @OA\RequestBody(
     * required=false,
     * description="Данные для обновления",
     * @OA\JsonContent(
     * @OA\Property(property="opens_at", type="string", example="09:00"),
     * @OA\Property(property="closes_at", type="string", example="18:00"),
     * @OA\Property(property="is_closed", type="string", example="false"),
     * @OA\Property(property="description", type="string", example="Тестовое описание изменено"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное изменение роли",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект доп времениресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="date", type="string", example="28.02.2001"),
     * @OA\Property(property="opens_at", type="string", example="18.00"),
     * @OA\Property(property="closes_at", type="string", example="20:00"),
     * @OA\Property(property="is_closed", type="boolean", example="true"),
     * @OA\Property(property="description", type="string", example="тестовое описание"),
     * @OA\Property(
     * property="restaurant",
     * type="object",
     * description="Ресторан",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules/2001-02-28")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Доступ запрещен (нет прав)",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="Forbidden"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules/2001-02-28")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules/2001-02-28"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле name обязательно для заполнения."))),
     * )
     * ),
     * )
     */
    public function restaurantScheduleUpdate(SchedulesUpdateRequest $request, int $id, string $date): RestaurantScheduleResource
    {
        $dtoUpdate = $request->toDto();
        $restaurant = $this->restaurantService->getRestaurant($id);


        Gate::authorize('update', [RestaurantSchedule::class, $restaurant]);

        $dtoShow = RestaurantScheduleShowDTO::from([
            'date' => $date,
            'id' => $id,
        ]);

        $schedule = $this->restaurantService->getSchedule($dtoShow);

        $newSchedule = $this->restaurantService->updateSchedule($schedule, $dtoUpdate);

        return new RestaurantScheduleResource($newSchedule);
    }

    /**
     * @OA\Delete(
     * path="/restaurants/{id}/schedules/{date}",
     * tags={"RestaurantSchedules"},
     * summary="Удаление дополнительного времени работы ресторана",
     * description="Удаление дополнительного времени работы ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторанa",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Parameter(
     * name="date",
     * in="path",
     * description="дата",
     * required=true,
     * @OA\Schema(
     * type="string",
     * example="2001-02-28"
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Успешное удаление доп времени",
     * ),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules/2001-02-28")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Доступ запрещен (нет прав)",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="Forbidden"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules/2001-02-28")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/schedules/2001-02-28"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле name обязательно для заполнения."))),
     * )
     * ),
     * )
     */
    public function restaurantScheduleDestroy(DeleteRequest $request, int $id, string $date): JsonResponse
    {
        $dto = $request->toDto();

        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('delete', [RestaurantSchedule::class, $restaurant]);

        $schedule = $this->restaurantService->getSchedule($dto);

        $this->restaurantService->deleteSchedule($schedule, true);

        return response()->json(null, 204);
    }


    /**
     * @OA\Get(
     * path="/restaurants/{id}/admins",
     * tags={"Restaurants"},
     * summary="Получение списка админов ресторана",
     * description="Получение списка админов ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение списка админов ресторана",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * description="Массив админов ресторана",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Илларион Иванович Шарапов"),
     * @OA\Property(property="email", type="string", example="admin@admin.com"),
     * @OA\Property(property="phone", type="string", example="+590432015354"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * ),
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/admins")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/admins")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Ресторан не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/user-not-found"),
     * @OA\Property(property="title", type="string", example="Object not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Ресторан не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/admins")
     * )
     * ),
     * )
     */
    public function allAdmins(int $id): UserCollection
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('viewAdmins', $restaurant);

        $admins = $this->restaurantService->getAdmins($restaurant);

        return new UserCollection($admins);
    }

    /**
     * @OA\Post(
     * path="/restaurants/{id}/admins",
     * tags={"Restaurants"},
     * summary="Добавление нового админа ресторана",
     * description="Добавление нового админа ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email", "password", "password_confirmation", "name", "phone"},
     * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     * @OA\Property(property="name", type="string", example="Джон Доу"),
     * @OA\Property(property="phone", type="string", example="+89123456789")
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Пользователь успешно добавлен и сделал админом ресторана",
     * ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/users"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле email обязательно для заполнения."))),
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Нет прав",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/admins")
     * )
     * ),
     * )
     */
    public function storeAdmin(RegisterUserRequest $request, int $id): JsonResponse
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('addAdmin', $restaurant);

        $dto = $request->toDTO();

        $this->restaurantService->addAdmin($dto, $restaurant);

        return response()->json(null, 204);
    }

    /**
     * @OA\Delete(
     * path="/restaurants/{restaurant_id}/admins/{user_id}",
     * tags={"Restaurants"},
     * summary="Удаление админа ресторана",
     * description="Удаление админа ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="restaurant_id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Parameter(
     * name="user_id",
     * in="path",
     * description="ID пользователя",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Админ успешно удален",
     * ),
     * @OA\Response(
     * response=403,
     * description="Нет прав",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/admins/1")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Пользователь не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/user-not-found"),
     * @OA\Property(property="title", type="string", example="User not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Пользователь не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/admins/1")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Внутрення ошибка сервера",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/database-error"),
     * @OA\Property(property="title", type="string", example="Database Error"),
     * @OA\Property(property="status", type="string", example="500"),
     * @OA\Property(property="detail", type="string", example="Произошла ошибка базы данных!"),
     * @OA\Property(property="instance", type="string", example="/api/restaurants/1/admins/1"),
     * )
     * ),
     * )
     */
    public function destroyAdmin(int $restaurant_id, int $user_id): JsonResponse
    {
        $restaurant = $this->restaurantService->getRestaurant($restaurant_id);
        $user = $this->userService->getUser($user_id);

        Gate::authorize('removeAdmin', [$restaurant, $user]);

        $this->restaurantService->removeAdmin($user, $restaurant);

        return response()->json(null, 204);
    }

    /**
     * @OA\GET(
     * path="/restaurants/{id}/blocked-users",
     * tags={"Restaurants"},
     * summary="Получение списка заблокированных пользователей ресторана",
     * description="Получение списка заблокированных пользователей ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Поиск по имени",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="email",
     * in="query",
     * description="Поиск по email",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="phone",
     * in="query",
     * description="Поиск по phone",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="sort_by",
     * in="query",
     * description="Поле сортировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"id", "name", "email", "phone", "created_at"},
     * default="id"
     * )
     * ),
     * @OA\Parameter(
     * name="sort_direction",
     * in="query",
     * description="Направление сортировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"asc", "desc"},
     * default="asc"
     * )
     * ),
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Номер страницы",
     * required=false,
     * @OA\Schema(type="integer", default=1)
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Количество элементов на странице",
     * required=false,
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение списка пользователей",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * description="Массив пользователей",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="NAME"),
     * @OA\Property(property="email", type="string", example="email"),
     * @OA\Property(property="phone", type="string", example="phone"),
     * @OA\Property(
     * property="block_info",
     * type="object",
     * description="Информация о блокировке",
     * @OA\Property(property="blocked_reason", type="string", example="blocked_reason"),
     * @OA\Property(property="blocked_at", type="string", example="28.02.2001 12:12:12"),
     * @OA\Property(
     * property="blocked_by",
     * type="object",
     * description="Информация о пользователе, который заблокировал пользователя",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Илларион Иванович Шарапов"),
     * @OA\Property(property="email", type="string", example="admin@admin.com"),
     * @OA\Property(property="phone", type="string", example="+590432015354"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * ),
     * ),
     * ),
     * @OA\Property(
     * property="meta",
     * type="object",
     * description="Пагинация",
     * @OA\Property(property="total", type="integer", example=56),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="count_pages", type="integer", example=6),
     * ),
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/restaurants/1/blocked-users")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/restaurants/1/blocked-users")
     * )
     * ),
     * )
     */
    public function blockedUsers(BlockedIndexRequest $request, int $id): BlockedUserCollection
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('viewBlockedUsers', $restaurant);

        $dto = $request->toDto();

        $users = $this->restaurantService->getBlockedUsers($dto);

        return new BlockedUserCollection($users);
    }

    /**
     * @OA\Post(
     * path="/restaurants/{id}/blocked-users",
     * tags={"Restaurants"},
     * summary="Добавление заблокированного в ресторане пользователя",
     * description="Добавление заблокированного в ресторане пользователя",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"user_id"},
     * @OA\Property(property="user_id", type="integer", example="1"),
     * @OA\Property(property="block_reason", type="integer", example="тестовая причина"),
     * )
     * ),
     * @OA\Response(
     *         response=200,
     *         description="Успешное добавление блокировки пользователю",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Объект заблокированного пользователя",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="NAME"),
     *                 @OA\Property(property="email", type="string", example="email"),
     *                 @OA\Property(property="phone", type="string", example="phone"),
     *                 @OA\Property(
     * property="block_info",
     * type="object",
     * description="Информация о блокировке",
     * @OA\Property(property="blocked_reason", type="string", example="blocked_reason"),
     * @OA\Property(property="blocked_at", type="string", example="28.02.2001 12:12:12"),
     * @OA\Property(
     * property="blocked_by",
     * type="object",
     * description="Информация о пользователе, который заблокировал пользователя",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Илларион Иванович Шарапов"),
     * @OA\Property(property="email", type="string", example="admin@admin.com"),
     * @OA\Property(property="phone", type="string", example="+590432015354"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * ),
     *             )
     *         )
     *     ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/restaurants/1/blocked-users"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле email обязательно для заполнения."))),
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/restaurants/1/blocked-users")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/restaurants/1/blocked-users")
     * )
     * ),
     * )
     */
    public function addBlockedUser(BlockedStoreRequest $request, int $id): BlockedUserResource
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('addBlockedUser', $restaurant);

        $dto = $request->toDto();

        $user = $this->restaurantService->addBlockedUser($dto);

        return new BlockedUserResource($user);
    }

    /**
     * @OA\Delete(
     * path="/restaurants/{restaurant_id}/blocked-users/{user_id}",
     * tags={"Restaurants"},
     * summary="Разблокировка пользователя ресторана",
     * description="Разблокировка пользователя ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user_id",
     * in="path",
     * description="ID пользователя",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Parameter(
     * name="restaurant_id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Пользователь успешно разблокирован",
     *),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/restaurants/1/blocked-users/1")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Нет прав",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/restaurants/1/blocked-users/1")
     * )
     * ),
     * )
     */
    public function deleteBlockedUser(BlockedDeleteRequest $request, int $restaurant_id, int $user_id): JsonResponse
    {
        $restaurant = $this->restaurantService->getRestaurant($restaurant_id);

        Gate::authorize('deleteBlockedUser', $restaurant);

        $dto = $request->toDto();

        $this->restaurantService->deleteBlockedUser($dto, true);

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     * path="/restaurants/{id}/availability",
     * tags={"Restaurants"},
     * summary="Получение списка свободных столиков в ресторане",
     * description="Получение списка свободных столиков в ресторане",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана для получения",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Parameter(
     * name="zone",
     * in="query",
     * description="Поиск по зоне столика",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="date",
     * in="query",
     * description="Поиск по дате",
     * required=true,
     * @OA\Schema(type="string", example="2025-11-14")
     * ),
     * @OA\Parameter(
     * name="time",
     * in="query",
     * description="Поиск по времени",
     * required=true,
     * @OA\Schema(type="string", example="18:00")
     * ),
     * @OA\Parameter(
     * name="count_guests",
     * in="query",
     * description="Поиск по количесву гостей",
     * required=false,
     * @OA\Schema(type="integer", example="1")
     * ),
     * @OA\Parameter(
     * name="sort_by",
     * in="query",
     * description="Поле сортировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"number"},
     * default="number"
     * )
     * ),
     * @OA\Parameter(
     * name="sort_direction",
     * in="query",
     * description="Направление сортировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"asc", "desc"},
     * default="asc"
     * )
     * ),
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Номер страницы",
     * required=false,
     * @OA\Schema(type="integer", default=1)
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Количество элементов на странице",
     * required=false,
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение списка столиков в ресторане",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * description="Массив столиков в ресторане",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="number", type="integer", example=1),
     * @OA\Property(property="capacity_min", type="integer", example=1),
     * @OA\Property(property="capacity_max", type="integer", example=1),
     * @OA\Property(property="zone", type="string", example="Тестовая зона"),
     * ),
     * ),
     * @OA\Property(
     * property="meta",
     * type="object",
     * description="Пагинация",
     * @OA\Property(property="total", type="integer", example=56),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="count_pages", type="integer", example=6),
     * ),
     * )
     * )
     * )
     */
    public function checkAvailability(CheckAvailabilityRequest $request, int $id): TableCollection
    {
        $dto = $request->toDto();

        $tables = $this->restaurantService->checkAvailability($dto);

        return new TableCollection($tables);
    }

    /**
     * @OA\GET(
     *     path="/restaurants/{id}/occupancy-stats",
     *     tags={"Restaurants"},
     *     summary="Получение статистики ресторана по id",
     *     description="Получение статистики ресторана по id",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID ресторана для получения",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="query",
     *         description="Период",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"day", "month", "year"},
     *             default="day"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Дата",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="2025-11-14"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение статистики ресторана",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Объект статистики",
     *                 @OA\Property(property="restaurant_id", type="integer", example=1),
     *                 @OA\Property(property="period", type="string", example="day"),
     *                 @OA\Property(property="date", type="string", example="2025-10-12"),
     *                 @OA\Property(
     *                     property="summary",
     *                     type="object",
     *                     @OA\Property(property="total_reservations", type="integer", example=10),
     *                     @OA\Property(property="total_guests", type="integer", example=10),
     *                     @OA\Property(property="average_occupancy_percent", type="integer", example=50),
     *                     @OA\Property(property="peak_hour", type="string", example="18:00"),
     *                     @OA\Property(property="off_peak_hour", type="string", example="16:00")
     *                 ),
     *                 @OA\Property(
     *                     property="details",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="hour", type="string", example="16:00"),
     *                         @OA\Property(property="occupancy_percent", type="integer", example=50),
     *                         @OA\Property(property="reservations_count", type="integer", example=10),
     *                         @OA\Property(property="guests_count", type="integer", example=20)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Нет прав",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     *             @OA\Property(property="title", type="string", example="You not authorized"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     *             @OA\Property(property="instance", type="string", example="/api/restaurants/1/occupancy-stats")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ресторан не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="https://example.com/errors/not-found"),
     *             @OA\Property(property="title", type="string", example="Object Not Found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="detail", type="string", example="Ресторан не найден!"),
     *             @OA\Property(property="instance", type="string", example="/api/restaurants/1/occupancy-stats")
     *         )
     *     )
     * )
     */
    public function getStats(StatRequest $request, int $id): OccupancyStatsResource
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('viewStats', $restaurant);

        $dto = $request->toDto();

        $statsData = $this->restaurantService->getStats($restaurant, $dto);

        return new OccupancyStatsResource(
            $statsData,
            $restaurant->id,
            $dto->period,
            $dto->date
        );
    }
}
