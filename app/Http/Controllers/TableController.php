<?php

namespace App\Http\Controllers;

use App\Http\Requests\Table\IndexRequest;
use App\Http\Resources\TableCollection;
use App\Http\Resources\TableResorce;
use App\Http\Resources\TableResource;
use App\Models\Table;
use App\Services\RestaurantService;
use App\Services\TableService;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 * name="Tables",
 * description="API для получение информации о столиках в ресторане"
 * )
 */
class TableController extends Controller
{
    public function __construct(
        protected TableService $tableService,
        protected RestaurantService $restaurantService
    ) {}

    /**
     * @OA\Get(
     * path="/restaurants/{id}/tables",
     * tags={"Tables"},
     * summary="Получение списка столиков в ресторане",
     * description="Получение списка столиков в ресторане",
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
     * name="sort_by",
     * in="query",
     * description="Поле сортировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"id", "capacity_min", "capacity_max"},
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
     * @OA\Property(
     * property="restaurant",
     * type="object",
     * description="Ресторан",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * ),
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

    public function index(IndexRequest $request, int $id): TableCollection
    {
        $restaurant = $this->restaurantService->getRestaurant($id);

        Gate::authorize('viewAny', [Table::class, $restaurant]);

        $dto = $request->toDto();

        $tables = $this->tableService->getTables($restaurant, $dto);

        return new TableCollection($tables);
    }

    /**
     * @OA\GET(
     * path="/tables/{id}",
     * tags={"Tables"},
     * summary="Получение столика в ресторане по id",
     * description="Получение столика в ресторане по id",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID сети столика в ресторане",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение столика",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект сети ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="number", type="integer", example=1),
     * @OA\Property(property="capacity_min", type="integer", example=1),
     * @OA\Property(property="capacity_max", type="integer", example=1),
     * @OA\Property(property="zone", type="string", example="Тестовая зона"),
     * @OA\Property(
     * property="restaurant",
     * type="object",
     * description="Ресторан",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * ),
     * ),
     * ),
     * @OA\Response(
     * response=404,
     * description="Столик не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/not-found"),
     * @OA\Property(property="title", type="string", example="Table not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Столик не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/tables/1")
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
     * @OA\Property(property="instance", type="string", example="/api/tables/1")
     * )
     * ),
     * )
     */
    public function show(int $id): TableResource
    {
        $table = $this->tableService->getTable($id);

        return new TableResource($table);
    }
}
