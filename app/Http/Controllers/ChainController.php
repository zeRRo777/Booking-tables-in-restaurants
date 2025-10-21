<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chain\IndexRequest;
use App\Http\Requests\Chain\StoreRequest;
use App\Http\Requests\Chain\UpdateRequest;
use App\Http\Resources\ChainCollection;
use App\Http\Resources\ChainResourse;
use App\Services\ChainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 * name="Chains",
 * description="API для получение информации о сети ресторанов"
 * )
 */
class ChainController extends Controller
{
    public function __construct(
        protected ChainService $chainService
    ) {}


    /**
     * @OA\Get(
     * path="/chains",
     * tags={"Chains"},
     * summary="Получение списка сетей ресторанов",
     * description="Получение списка сетей ресторанов",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Поиск по названию",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="status",
     * in="query",
     * description="Поиск по cтатусу сети ресторана",
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
     * enum={"id", "name", "created_at"},
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
     * description="Успешное получение списка сетей ресторанов",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * description="Массив сетей ресторанов",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовая сеть"),
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
    public function index(IndexRequest $request): ChainCollection
    {
        $filterDto = $request->toDto();

        $chains = $this->chainService->getChains($filterDto, $request->user());

        return new ChainCollection($chains);
    }

    /**
     * @OA\GET(
     * path="/chains/{id}",
     * tags={"Chains"},
     * summary="Получение cети ресторана по id",
     * description="Получение cети ресторана по id",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID сети ресторана для получения",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение списка пользователей",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект сети ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовая сеть ресторана"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * )
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Сеть ресторана не найдена",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/chain-not-found"),
     * @OA\Property(property="title", type="string", example="Chain not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Сеть ресторана не найдена!"),
     * @OA\Property(property="instance", type="string", example="/api/chains/1")
     * )
     * ),
     * )
     */
    public function show(int $id): ChainResourse
    {
        $chain = $this->chainService->getChain($id);

        return new ChainResourse($chain);
    }

    /**
     * @OA\Post(
     * path="/chains",
     * tags={"Chains"},
     * summary="Добавление новой Сети ресторана",
     * description="Создает новую сеть и возвращает ее данные",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "status"},
     * @OA\Property(property="name", type="string", example="New Company"),
     * @OA\Property(property="status", type="string", example="moderation")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Сеть ресторана успешно создана",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект сети ресторана",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовая сеть ресторана"),
     * @OA\Property(property="status", type="string", example="active"),
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
     * @OA\Property(property="instance", type="string", example="/api/chains"),
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
     * @OA\Property(property="instance", type="string", example="/api/chains")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Внутренняя ошибка сервера",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/token"),
     * @OA\Property(property="title", type="string", example="An error occurred while validating token"),
     * @OA\Property(property="status", type="integer", example=500),
     * @OA\Property(property="detail", type="string", example="При проверке токена произошла ошибка"),
     * @OA\Property(property="instance", type="string", example="/api/chains")
     * )
     * ),
     * )
     */
    public function store(StoreRequest $request): ChainResourse
    {
        $dto = $request->toDto();

        $chain = $this->chainService->createChain($dto);

        return new ChainResourse($chain);
    }

    /**
     * @OA\Patch(
     * path="/chains/{id}",
     * tags={"Chains"},
     * summary="Изменение данных сети ресторана",
     * description="Изменение данных сети ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID сети ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Updated Company"),
     * @OA\Property(property="status", type="string", example="active"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Сеть ресторна успешно изменена",
     * @OA\JsonContent(
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Updated Company"),
     * @OA\Property(property="status", type="string", example="active"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
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
     * @OA\Property(property="instance", type="string", example="/api/chains/1"),
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
     * @OA\Property(property="instance", type="string", example="/api/chains/1")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Сеть ресторана не найдена",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/chain-not-found"),
     * @OA\Property(property="title", type="string", example="Chain not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Сеть ресторана не найдена!"),
     * @OA\Property(property="instance", type="string", example="/api/chains/1")
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
     * @OA\Property(property="instance", type="string", example="/api/chains/1"),
     * )
     * ),
     * )
     */
    public function update(UpdateRequest $request, int $id): ChainResourse
    {
        $chain = $this->chainService->getChain($id);

        Gate::authorize('update', $chain);

        $dto = $request->toDto();

        $updatedChain = $this->chainService->updateChain($chain, $dto);

        return new ChainResourse($updatedChain);
    }

    /**
     * @OA\Delete(
     * path="/chains/{id}",
     * tags={"Chains"},
     * summary="Удаление сети ресторана",
     * description="Удаление сети ресторана",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID сети ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Сеть ресторана успешно удалена",
     *),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/api/chains/1")
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
     * @OA\Property(property="instance", type="string", example="/api/chains/1")
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
     * @OA\Property(property="instance", type="string", example="/api/chains/1")
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
     * @OA\Property(property="instance", type="string", example="/api/chains/1"),
     * )
     * ),
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $this->chainService->deleteChain($id);

        return response()->json(null, 204);
    }
}
