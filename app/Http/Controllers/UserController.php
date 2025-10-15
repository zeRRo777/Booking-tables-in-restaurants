<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\IndexRequest;
use App\Http\Requests\User\UpdateMeRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UsersCollection;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 * name="ME",
 * description="API для получение информации о пользователе"
 * )
 * * @OA\Tag(
 * name="Users",
 * description="API для управления другими пользователями (административные функции)"
 * )
 */
class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected AuthService $authService,
    ) {}


    /**
     * @OA\GET(
     * path="/me",
     * tags={"ME"},
     * summary="Получение информации о себе",
     * description="Получение иноформации о себе",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Успешное получение данных о себе",
     * @OA\JsonContent(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Джон Доу"),
     * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     * @OA\Property(property="phone", type="string", example="+89123456789"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
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
     * @OA\Property(property="instance", type="string", example="/api/me")
     * )
     * ),
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            new UserResource($user)
        ]);
    }


    /**
     * @OA\Patch(
     * path="/me",
     * tags={"ME"},
     * summary="Обновление текущего пользователя",
     * description="Обновление ифнормации о текущем пользователе",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Данные для обновления",
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="New Name"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Пользователь успешно изменил данные",
     * @OA\JsonContent(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Джон Доу"),
     * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     * @OA\Property(property="phone", type="string", example="+89123456789"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z")
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
     * @OA\Property(property="instance", type="string", example="/api/login"),
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
     * @OA\Property(property="instance", type="string", example="/api/me")
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
     * @OA\Property(property="instance", type="string", example="/api/me"),
     * )
     * ),
     * )
     */
    public function updateMe(UpdateMeRequest $request): JsonResponse
    {
        $user = $request->user();

        $updatedUser = $this->userService->updateUser($user, $request->validated());

        return response()->json([
            new UserResource($updatedUser),
        ]);
    }

    /**
     * @OA\Delete(
     * path="/me",
     * tags={"ME"},
     * summary="Удаление текущего пользователя",
     * description="Удаление текущего пользователя",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=204,
     * description="Пользователь успешно удален",
     *),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/api/me")
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
     * @OA\Property(property="instance", type="string", example="/api/me"),
     * )
     * ),
     * )
     */
    public function deleteMe(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->userService->deleteUser($user, true);

        $this->authService->logout();

        return response()->json(null, 204);
    }

    /**
     * @OA\GET(
     * path="/users",
     * tags={"Users"},
     * summary="Получение списка пользователей",
     * description="Получение списка всех пользователей. Доступно только администраторам.",
     * security={{"bearerAuth":{}}},
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
     * name="is_blocked",
     * in="query",
     * description="Фильтрация по статусу блокировки",
     * required=false,
     * @OA\Schema(
     * type="string",
     * enum={"true", "false"},
     * default="false"
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
     * @OA\Property(property="name", type="string", example="Илларион Иванович Шарапов"),
     * @OA\Property(property="email", type="string", example="admin@admin.com"),
     * @OA\Property(property="phone", type="string", example="+590432015354"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
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
     * ),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/api/users")
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
     * @OA\Property(property="instance", type="string", example="/api/users")
     * )
     * ),
     * )
     */
    public function index(IndexRequest $request): UsersCollection
    {
        $filterDto = $request->toDto();

        $users = $this->userService->getUsers($filterDto);

        return new UsersCollection($users);
    }
}
