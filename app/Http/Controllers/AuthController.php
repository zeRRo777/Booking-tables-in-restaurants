<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 * name="Auth",
 * description="API для аутентификации и регистрации"
 * )
 */
class AuthController extends Controller
{

    public function __construct(
        protected AuthService $authService,
        protected UserService $userService
    ) {}

    /**
     * @OA\Post(
     * path="/login",
     * tags={"Auth"},
     * summary="Авторизация пользователя",
     * description="авторизует и возвращает его данные вместе с JWT-токеном.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email", "password"},
     * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Пользователь успешно авторизован",
     * @OA\JsonContent(
     * @OA\Property(property="user", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Джон Доу"),
     * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     * @OA\Property(property="phone", type="string", example="+89123456789"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
     * ),
     * @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
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
     * response=403,
     * description="Вы уже авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/already-authenticated"),
     * @OA\Property(property="title", type="string", example="Already Authenticated"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Вы уже авторизованы!"),
     * @OA\Property(property="instance", type="string", example="/api/login")
     * )
     * ),
     * @OA\Response(
     *     response=401,
     *     description="Ошибка авторизации",
     *     @OA\JsonContent(
     *         oneOf={
     *             @OA\Schema(
     *                 @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     *                 @OA\Property(property="title", type="string", example="Unauthorized"),
     *                 @OA\Property(property="status", type="integer", example=401),
     *                 @OA\Property(property="detail", type="string", example="Неверные учетные данные."),
     *                 @OA\Property(property="instance", type="string", example="/api/login")
     *             ),
     *             @OA\Schema(
     *                 @OA\Property(property="type", type="string", example="https://example.com/errors/token-not-found"),
     *                 @OA\Property(property="title", type="string", example="Token is invalid or has been revoked"),
     *                 @OA\Property(property="status", type="integer", example=401),
     *                 @OA\Property(property="detail", type="string", example="Токен недействителен или был отозван"),
     *                 @OA\Property(property="instance", type="string", example="/api/login")
     *             )
     *         }
     *     )
     * ),
     * @OA\Response(
     * response=500,
     * description="Внутренняя ошибка сервера",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/token"),
     * @OA\Property(property="title", type="string", example="An error occurred while validating token"),
     * @OA\Property(property="status", type="integer", example=500),
     * @OA\Property(property="detail", type="string", example="При проверке токена произошла ошибка"),
     * @OA\Property(property="instance", type="string", example="/api/login")
     * )
     * ),
     * )
     */
    public function login(LoginRequest $request)
    {
        $user = $this->authService->authenticate(
            $request->validated('email'),
            $request->validated('password')
        );

        if (!$user) {
            return response()->json([
                'type' => 'https://example.com/errors/unauthorized',
                'title' => 'Unauthorized',
                'status' => 401,
                'detail' => 'Неверные учетные данные.',
                'instance' => $request->getUri(),
            ], 401);
        }

        $userToken = $this->authService->createAndSaveToken($user);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $userToken->token,
        ], 200);
    }


    /**
     * @OA\Post(
     * path="/register",
     * tags={"Auth"},
     * summary="Регистрация нового пользователя",
     * description="Создает нового пользователя и возвращает его данные вместе с JWT-токеном.",
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
     * response=201,
     * description="Пользователь успешно зарегистрирован",
     * @OA\JsonContent(
     * @OA\Property(property="user", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Джон Доу"),
     * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     * @OA\Property(property="phone", type="string", example="+89123456789"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
     * ),
     * @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
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
     * @OA\Property(property="instance", type="string", example="/api/register"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле email обязательно для заполнения."))),
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Вы уже авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/already-authenticated"),
     * @OA\Property(property="title", type="string", example="Already Authenticated"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Вы уже авторизованы!"),
     * @OA\Property(property="instance", type="string", example="/api/register")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Токен недействителен или был отозван",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/token-not-found"),
     * @OA\Property(property="title", type="string", example="Token is invalid or has been revoked"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Токен недействителен или был отозван"),
     * @OA\Property(property="instance", type="string", example="/api/register")
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
     * @OA\Property(property="instance", type="string", example="/api/register")
     * )
     * ),
     * )
     */
    public function register(StoreUserRequest $request): JsonResponse
    {
        $userDTO = $request->toDTO();

        $data = $this->userService->createUser($userDTO);

        $user = $data['user'];
        $token = $data['token'];

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }
}
