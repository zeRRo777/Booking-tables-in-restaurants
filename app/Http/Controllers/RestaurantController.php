<?php

namespace App\Http\Controllers;

use App\DTOs\Restaurant\UpdateRestaurantDTO;
use App\Exceptions\NotFoundException;
use App\Http\Requests\Restaurant\ChangeStatusRequest;
use App\Http\Requests\Restaurant\IndexRequest;
use App\Http\Requests\Restaurant\Schedules\IndexRequest as SchedulesIndexRequest;
use App\Http\Requests\Restaurant\Schedules\ShowRequest;
use App\Http\Requests\Restaurant\StoreRequest;
use App\Http\Requests\Restaurant\UpdateRequest;
use App\Http\Resources\RestaurantCollection;
use App\Http\Resources\RestaurantResource;
use App\Http\Resources\RestaurantScheduleCollection;
use App\Http\Resources\RestaurantScheduleResource;
use App\Models\RestaurantSchedule;
use App\Services\RestaurantService;
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
        protected RestaurantService $restaurantService
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
     * example="28.02.2001"
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
}
