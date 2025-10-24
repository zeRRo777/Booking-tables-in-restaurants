<?php

namespace App\Http\Controllers;

use App\Http\Requests\Table\IndexRequest;
use App\Http\Requests\Table\StoreRequest;
use App\Http\Requests\Table\UpdateRequest;
use App\Http\Resources\TableCollection;
use App\Http\Resources\TableResorce;
use App\Http\Resources\TableResource;
use App\Models\Table;
use App\Services\RestaurantService;
use App\Services\TableService;
use Illuminate\Http\JsonResponse;
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

    /**
     * @OA\POST(
     * path="/tables",
     * tags={"Tables"},
     * summary="Добавление столика в ресторане",
     * description="Добавление столика в ресторане",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Данные для создания",
     * @OA\JsonContent(
     * @OA\Property(property="number", type="integer", example="1"),
     * @OA\Property(property="capacity_min", type="integer", example="1"),
     * @OA\Property(property="capacity_max", type="integer", example="1"),
     * @OA\Property(property="zone", type="string", example="zone"),
     * @OA\Property(property="restaurant_id", type="integer", example="1"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное добавление роли",
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
     * @OA\Property(property="instance", type="string", example="/api/tables")
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
     * @OA\Property(property="instance", type="string", example="/api/tables")
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
     * @OA\Property(property="instance", type="string", example="/api/tables"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле name обязательно для заполнения."))),
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
     * @OA\Property(property="instance", type="string", example="/api/tables"),
     * )
     * ),
     * )
     */
    public function store(StoreRequest $request): TableResource
    {
        $dto = $request->toDto();

        $table = $this->tableService->createTable($dto);

        return new TableResource($table);
    }

    /**
     * @OA\Patch(
     * path="/tables/{id}",
     * tags={"Tables"},
     * summary="Изменение данных столика в ресторане",
     * description="Изменение данных столика в ресторане",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID столика",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="zone", type="string", example="Updated zone"),
     * @OA\Property(property="number", type="integer", example="1"),
     * @OA\Property(property="capacity_min", type="integer", example="1"),
     * @OA\Property(property="capacity_max", type="integer", example="1"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Столик в ресторане успешно изменен",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект столика ресторана",
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
     * @OA\Property(property="instance", type="string", example="/api/tables/1"),
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
     * @OA\Property(property="instance", type="string", example="/api/tables/1")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Ресторан не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/not-found"),
     * @OA\Property(property="title", type="string", example="Not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Столик не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/tables/1")
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
     * @OA\Property(property="instance", type="string", example="/api/tables/1"),
     * )
     * ),
     * )
     */
    public function update(UpdateRequest $request, int $id): TableResource
    {
        $table = $this->tableService->getTable($id);

        $dto = $request->toDto();

        $table = $this->tableService->updateTable($table, $dto);

        return new TableResource($table);
    }

    /**
     * @OA\Delete(
     * path="/tables/{id}",
     * tags={"Tables"},
     * summary="Удаление столика в ресторане",
     * description="Удаление столика в ресторане",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID столика в ресторане",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Столик в ресторане успешно удален",
     *),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
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
     * @OA\Response(
     * response=404,
     * description="Столик в ресторане не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/not-found"),
     * @OA\Property(property="title", type="string", example="Not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Столик в ресторане не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/tables/1")
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
     * @OA\Property(property="instance", type="string", example="/api/tables/1"),
     * )
     * ),
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $table = $this->tableService->getTable($id);

        Gate::authorize('delete', $table);

        $this->tableService->deleteTable($table, true);

        return response()->json(null, 204);
    }
}
