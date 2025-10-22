<?php

namespace App\Http\Controllers;

use App\Http\Requests\Restaurant\IndexRequest;
use App\Http\Resources\RestaurantCollection;
use App\Services\RestaurantService;


/**
 * @OA\Tag(
 * name="Restaurants",
 * description="API для получение информации о ресторанах"
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
}
