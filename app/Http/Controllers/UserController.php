<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateMeRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\Finder\Exception\AccessDeniedException;

/**
 * @OA\Tag(
 * name="ME",
 * description="API для получение информации о пользователе"
 * )
 */
class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
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
     * @OA\Property(property="user", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Джон Доу"),
     * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     * @OA\Property(property="phone", type="string", example="+89123456789"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
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
     * @OA\Property(property="instance", type="string", example="/api/me")
     * )
     * ),
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => new UserResource($user)
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
     * @OA\Property(
     * property="user",
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Джон Доу"),
     * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     * @OA\Property(property="phone", type="string", example="+89123456789"),
     * @OA\Property(property="is_blocked", type="boolean", example=false),
     * @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z"),
     * @OA\Property(property="updated_at", type="string", format="date-time", example="2023-10-27T10:00:00.000000Z")
     * ),
     * @OA\Property(property="result", type="boolean", example=true)
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
     * )
     */
    public function updateMe(UpdateMeRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!Gate::allows('updateMe', $user)) {
            throw new AccessDeniedException('Access denied');
        }

        try {
            $updatedUser = $this->userService->updateUser($user, $request->validated());

            return response()->json([
                'user' => new UserResource($user),
                'result' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Произошла ошибка при обновлении данных!'
            ], 500);
        }
    }
}
