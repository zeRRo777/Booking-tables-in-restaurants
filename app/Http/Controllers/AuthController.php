<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ChangeEmailUserConfirmRequest;
use App\Http\Requests\Auth\ChangeEmailUserRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordResetConfirmRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
     * path="/auth/login",
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
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authService->authenticate(
            $request->validated('email'),
            $request->validated('password')
        );

        $userToken = $this->authService->createAndSaveToken($user);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $userToken->token,
        ], 200);
    }


    /**
     * @OA\Post(
     * path="/auth/register",
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
    public function register(RegisterUserRequest $request): JsonResponse
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

    /**
     * @OA\Post(
     * path="/auth/logout",
     * tags={"Auth"},
     * summary="Выход пользователя из системы",
     * description="Удаляет JWT токен из бд",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Пользователь успешно вышел из системы",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Вы успешно вышли из системы."),
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
     * )
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return response()->json(['message' => 'Вы успешно вышли из системы.'], 200);
    }

    /**
     * @OA\Post(
     * path="/auth/password/change",
     * tags={"Auth"},
     * summary="Смена пароля текущего пользователя",
     * description="Смена пароля текущего пользователя",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Данные для обновления",
     * @OA\JsonContent(
     * @OA\Property(property="old_password", type="string", example="password"),
     * @OA\Property(property="password", type="string", example="new_password"),
     * @OA\Property(property="password_confirmation", type="string", example="new_password"),
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Пользователь успешно обновил пароль",
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
     * @OA\Property(property="instance", type="string", example="/api/logout")
     * )
     * ),
     * )
     */
    public function changePassword(ChangeEmailUserRequest $request): JsonResponse
    {
        $user = $request->user();

        $dataValidated = $request->validated();

        $this->authService->changePassword($user, $dataValidated['password'], true);

        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     * path="/auth/password/reset",
     * tags={"Auth"},
     * summary="Подготовка сброса пароля пользователя",
     * description="Подготовка сброса пароля  пользователя",
     * @OA\RequestBody(
     * required=true,
     * description="Данные для сброса пароля",
     * @OA\JsonContent(
     * @OA\Property(property="email", type="string", example="test@gmail.com"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Пользователь успешно обновил пароль",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Если ваша электронная почта зарегистрирована, вы получите ссылку для сброса пароля.")
     * ),
     * ),
     * )
     */
    public function preperationResetPassword(PasswordResetRequest $request): JsonResponse
    {
        $this->authService->sendResetLink($request->validated('email'));

        return response()->json([
            'message' => 'Если ваша электронная почта зарегистрирована, вы получите ссылку для сброса пароля.',
        ]);
    }

    /**
     * @OA\Post(
     * path="/auth/password/reset/confirm",
     * tags={"Auth"},
     * summary="Подтверждение сброса пароля пользователя",
     * description="Подтверждение сброса пароля пользователя",
     * @OA\RequestBody(
     * required=true,
     * description="Данные для сброса пароля",
     * @OA\JsonContent(
     * @OA\Property(property="email", type="string", example="test@gmail.com"),
     * @OA\Property(property="password", type="string", example="new_password"),
     * @OA\Property(property="password_confirmation", type="string", example="new_password"),
     * @OA\Property(property="token", type="string", example="fdfdfdfd545gfgfg3435454fdgfg"),
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Пользователь успешно обновил пароль",
     * ),
     * @OA\Response(
     * response=422,
     * description="Данные не верны",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://bookingService/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Неверные данные для сброса пароля."),
     * @OA\Property(property="instance", type="string", example="/auth/password/reset/confirm")
     * )
     * ),
     * )
     */
    public function resetPassword(PasswordResetConfirmRequest $request): JsonResponse
    {
        $dataValidated = $request->validated();

        $this->authService->resetPassword(
            $dataValidated['email'],
            $dataValidated['token'],
            $dataValidated['password']
        );

        return response()->json(null, 204);
    }


    /**
     * @OA\Post(
     * path="/auth/email/change",
     * tags={"Auth"},
     * summary="Подгтовка к смене почты пользователя",
     * description="Подгтовка к смене почты пользователя",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Данные для смены почты",
     * @OA\JsonContent(
     * @OA\Property(property="new_email", type="string", example="test@gmail.com"),
     * @OA\Property(property="password", type="string", example="password"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Сообщение о смене почты успешно отрпвлено на почту",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Если ваша электронная почта существует, вы получите ссылку для смены почты."),
     * ),
     * ),
     * @OA\Response(
     * response=422,
     * description="Данные не верны",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://bookingService/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Неверные данные"),
     * @OA\Property(property="instance", type="string", example="/auth/email/change")
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
     * @OA\Property(property="instance", type="string", example="/api/auth/email/change")
     * )
     * ),
     * )
     */
    public function prepareChangeEmail(ChangeEmailUserRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->authService->sendChangeEmailLink($user, $request->validated(['new_email']));

        return response()->json([
            'message' => 'Если ваша электронная почта существует, вы получите ссылку для смены почты.',
        ]);
    }

    /**
     * @OA\Post(
     * path="/auth/email/change/confirm",
     * tags={"Auth"},
     * summary="Подтверждение смены почты пользователя",
     * description="Подтверждение смены почты пользователя",
     * @OA\RequestBody(
     * required=true,
     * description="Данные для смены почты",
     * @OA\JsonContent(
     * @OA\Property(property="token", type="string", example="iSkJPWlhmMSIsInN1"),
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Пользователь успешно сменил почту",
     * ),
     * @OA\Response(
     * response=422,
     * description="Данные не верны",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://bookingService/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Неверные данные для смены почты."),
     * @OA\Property(property="instance", type="string", example="/auth/email/change/confirm")
     * )
     * ),
     * )
     */
    public function changeEmail(ChangeEmailUserConfirmRequest $request): JsonResponse
    {
        $this->authService->changeEmail($request->validated(['token']));

        return response()->json(null, 204);
    }
}
