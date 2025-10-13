<?php

namespace App\Http\Controllers;

use App\Http\Requests\Verify\VerifyEmailConfirmRequest;
use App\Http\Requests\Verify\VerifyPhoneConfirmRequest;
use App\Services\VerifyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 * name="Verify",
 * description="API для подтверждения почты и телефона"
 * )
 */
class VerifyController extends Controller
{
    public function __construct(
        protected VerifyService $verifyService
    ) {}


    /**
     * @OA\Post(
     * path="/verify/email/send",
     * tags={"Verify"},
     * summary="Отправка кода подтверждения на почту пользователя для подтверждения почты",
     * description="Отправка кода подтверждения на почту пользователя для подтверждения почты",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Код подтверждения успешно отправлен",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Код верификации отправлен на вашу почту"),
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
     * @OA\Property(property="instance", type="string", example="/api/verify/email/send")
     * )
     * ),
     * @OA\Response(
     * response=400,
     * description="Ошибка верификации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/user-already-verfied"),
     * @OA\Property(property="title", type="string", example="User already verfied"),
     * @OA\Property(property="status", type="integer", example=400),
     * @OA\Property(property="detail", type="string", example="User already verfied"),
     * @OA\Property(property="instance", type="string", example="/api/verify/email/send")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Ошибка БД",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/database-error"),
     * @OA\Property(property="title", type="string", example="Database Error"),
     * @OA\Property(property="status", type="integer", example=500),
     * @OA\Property(property="detail", type="string", example="Произошла ошибка базы данных!"),
     * @OA\Property(property="instance", type="string", example="/api/verify/email/send")
     * )
     * )
     * )
     */
    public function prepareEmailVerify(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->verifyService->sendVerifyEmail($user);

        return response()->json([
            'message' => 'Код верификации отправлен на вашу почту'
        ]);
    }


    /**
     * @OA\Post(
     * path="/verify/email/confirm",
     * tags={"Verify"},
     * summary="Подтверждение почты пользователя",
     * description="Подтверждение почты пользователя",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Данные для смены подтверждения почты",
     * @OA\JsonContent(
     * @OA\Property(property="code", type="string", example="111111"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Пользователь успешно подтвердил почту",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Email успешно подтвержден"),
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
     * @OA\Property(property="instance", type="string", example="/api/verify/email/confirm")
     * )
     * ),
     * @OA\Response(
     * response=400,
     * description="Ошибка верификации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/user-already-verfied"),
     * @OA\Property(property="title", type="string", example="User already verfied"),
     * @OA\Property(property="status", type="integer", example=400),
     * @OA\Property(property="detail", type="string", example="User already verfied"),
     * @OA\Property(property="instance", type="string", example="/api/verify/email/confirm")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Ошибка БД",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/database-error"),
     * @OA\Property(property="title", type="string", example="Database Error"),
     * @OA\Property(property="status", type="integer", example=500),
     * @OA\Property(property="detail", type="string", example="Произошла ошибка базы данных!"),
     * @OA\Property(property="instance", type="string", example="/api/verify/email/send")
     * )
     * )
     * )
     */
    public function verifyEmail(VerifyEmailConfirmRequest $request): JsonResponse
    {
        $this->verifyService->verifyEmail(
            $request->user(),
            $request->validated(['code'])
        );

        return response()->json([
            'message' => 'Email успешно подтвержден'
        ]);
    }


    /**
     * @OA\Post(
     * path="/verify/phone/send",
     * tags={"Verify"},
     * summary="Отправка кода подтверждения на телефон пользователя для подтверждения телефона",
     * description="Отправка кода подтверждения на телефон пользователя для подтверждения телефона",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Код подтверждения успешно отправлен",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Код верификации отправлен на ваш телефон"),
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
     * @OA\Property(property="instance", type="string", example="/api/verify/phone/send")
     * )
     * ),
     * @OA\Response(
     * response=400,
     * description="Ошибка верификации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/user-already-verfied"),
     * @OA\Property(property="title", type="string", example="User already verfied"),
     * @OA\Property(property="status", type="integer", example=400),
     * @OA\Property(property="detail", type="string", example="User already verfied"),
     * @OA\Property(property="instance", type="string", example="/api/verify/email/send")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Ошибка БД",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/database-error"),
     * @OA\Property(property="title", type="string", example="Database Error"),
     * @OA\Property(property="status", type="integer", example=500),
     * @OA\Property(property="detail", type="string", example="Произошла ошибка базы данных!"),
     * @OA\Property(property="instance", type="string", example="/api/verify/email/send")
     * )
     * )
     * )
     */
    public function preparePhoneVerify(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->verifyService->sendVerifyPhone($user);

        return response()->json([
            'message' => 'Код верификации отправлен на ваш телефон'
        ]);
    }


    /**
     * @OA\Post(
     * path="/verify/phone/confirm",
     * tags={"Verify"},
     * summary="Подтверждение телефона пользователя",
     * description="Подтверждение телефона пользователя",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Данные для подтверждения телефона",
     * @OA\JsonContent(
     * @OA\Property(property="code", type="string", example="111111"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Пользователь успешно подтвердил номер телефона",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Телефон успешно подтвержден"),
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
     * @OA\Property(property="instance", type="string", example="/api/verify/phone/confirm")
     * )
     * ),
     * @OA\Response(
     * response=400,
     * description="Ошибка верификации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/user-already-verfied"),
     * @OA\Property(property="title", type="string", example="User already verfied"),
     * @OA\Property(property="status", type="integer", example=400),
     * @OA\Property(property="detail", type="string", example="User already verfied"),
     * @OA\Property(property="instance", type="string", example="/api/verify/phone/confirm")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Ошибка БД",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/database-error"),
     * @OA\Property(property="title", type="string", example="Database Error"),
     * @OA\Property(property="status", type="integer", example=500),
     * @OA\Property(property="detail", type="string", example="Произошла ошибка базы данных!"),
     * @OA\Property(property="instance", type="string", example="/api/verify/phone/send")
     * )
     * )
     * )
     */
    public function verifyPhone(VerifyPhoneConfirmRequest $request): JsonResponse
    {
        $this->verifyService->verifyPhone(
            $request->user(),
            $request->validated(['code'])
        );

        return response()->json([
            'message' => 'Телефон успешно подтвержден'
        ]);
    }
}
