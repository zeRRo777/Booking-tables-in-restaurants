<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\UpdateRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 * name="Roles",
 * description="API для ролей"
 * )
 */
class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roleService
    ) {}

    /**
     * @OA\Get(
     * path="/roles",
     * tags={"Roles"},
     * summary="Получение всех ролей",
     * description="Получение всех ролей",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Успешное получение всех ролей",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * description="Массив ролей",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="user"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09")
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
     * @OA\Property(property="instance", type="string", example="/api/logout")
     * )
     * ),
     * * @OA\Response(
     * response=403,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/roles")
     * )
     * ),
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $roles = $this->roleService->getAllRoles();

        return RoleResource::collection($roles);
    }

    /**
     * @OA\Get(
     * path="/roles/{id}",
     * tags={"Roles"},
     * summary="Получение одной роли",
     * description="Получение одной роли по её уникальному идентификатору (ID)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID роли для получения",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение роли",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект роли",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="administrator"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09")
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
     * @OA\Property(property="instance", type="string", example="/api/roles/1")
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
     * @OA\Property(property="instance", type="string", example="/api/roles/1")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Роль не найдена",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/role-not-found"),
     * @OA\Property(property="title", type="string", example="Role not found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Role not found."),
     * @OA\Property(property="instance", type="string", example="/api/roles/92323")
     * )
     * ),
     * )
     */
    public function show(int $id): RoleResource
    {
        return new RoleResource($this->roleService->findRoleById($id));
    }

    /**
     * @OA\Delete(
     * path="/roles/{id}",
     * tags={"Roles"},
     * summary="Удаление роли",
     * description="Удаление по её уникальному идентификатору (ID)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID роли",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение роли",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Роль успешно удалена.")
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
     * @OA\Property(property="instance", type="string", example="/api/roles/1")
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
     * @OA\Property(property="instance", type="string", example="/api/roles/1")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Роль не найдена",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/role-not-found"),
     * @OA\Property(property="title", type="string", example="Role not found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Role not found."),
     * @OA\Property(property="instance", type="string", example="/api/roles/92323")
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
     * @OA\Property(property="instance", type="string", example="/api/roles"),
     * )
     * ),
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $this->roleService->deleteRole($id);

        return response()->json(['message' => 'Роль успешно удалена'], 200);
    }

    /**
     * @OA\POST(
     * path="/roles",
     * tags={"Roles"},
     * summary="Добавление роли",
     * description="Добавление роли",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Данные для создания",
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="New ROLE"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное добавление роли",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект роли",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="administrator"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09")
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
     * @OA\Property(property="instance", type="string", example="/api/roles")
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
     * @OA\Property(property="instance", type="string", example="/api/roles")
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
     * @OA\Property(property="instance", type="string", example="/api/roles"),
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
     * @OA\Property(property="instance", type="string", example="/api/roles"),
     * )
     * ),
     * )
     */
    public function store(StoreRequest $request): RoleResource
    {
        $role = $this->roleService->createRole($request->validated(['name']));

        return new RoleResource($role);
    }


    /**
     * @OA\Patch(
     * path="/roles/{id}",
     * tags={"Roles"},
     * summary="Обновление роли",
     * description="Обновление роли",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID роли",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Данные для обновления",
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="updated ROLE"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное обновление роли",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект роли",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="administrator"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="13.10.2025 16:58:09")
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
     * @OA\Property(property="instance", type="string", example="/api/roles/11")
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
     * @OA\Property(property="instance", type="string", example="/api/roles/11")
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
     * @OA\Property(property="instance", type="string", example="/api/roles/11"),
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
     * @OA\Property(property="instance", type="string", example="/api/roles/11"),
     * )
     * ),
     * )
     */
    public function update(UpdateRequest $request, int $id): RoleResource
    {
        $role = $this->roleService->updateRole($id, $request->validated(['name']));

        return new RoleResource($role);
    }
}
