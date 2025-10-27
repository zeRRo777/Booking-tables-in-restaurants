<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationStatuse\StoreRequest;
use App\Http\Requests\ReservationStatuse\UpdateRequest;
use App\Http\Resources\ReservationStatuseCollection;
use App\Http\Resources\ReservationStatuseResource;
use App\Services\ReservationStatuseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 * name="ReservationStatuses",
 * description="API для получение информации о статусах бронирования"
 * )
 */
class ReservationStatuseController extends Controller
{
    public function __construct(
        protected ReservationStatuseService $reservationStatuseService,
    ) {}

    /**
     * @OA\Get(
     * path="/reservation_statuses",
     * tags={"ReservationStatuses"},
     * summary="Получение списка статусов бронирования",
     * description="Получение списка статусов бронирования",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Успешное получение списка статусов бронирования",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="array",
     * description="Массив статусов бронирования",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="pending"),
     * ),
     * ),
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
     * @OA\Property(property="instance", type="string", example="/api/reservation_statuses")
     * )
     * ),
     * )
     */
    public function index(Request $request): ReservationStatuseCollection
    {
        $statuses = $this->reservationStatuseService->getAll();

        return new ReservationStatuseCollection($statuses);
    }

    /**
     * @OA\GET(
     *     path="/reservation_statuses/{id}",
     *     tags={"ReservationStatuses"},
     *     summary="Получение статуса бронирования по id",
     *     description="Получение статуса бронирования по id",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID статуса бронирования",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение статуса бронирования",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Объект статуса броинрования",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="pending"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Статус бронирования не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="https://example.com/errors/chain-not-found"),
     *             @OA\Property(property="title", type="string", example="Object not Found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="detail", type="string", example="Статус бронирования не найден!"),
     *             @OA\Property(property="instance", type="string", example="/api/reservation_statuses/1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Нет прав",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     *             @OA\Property(property="title", type="string", example="You not authorized"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     *             @OA\Property(property="instance", type="string", example="/api/reservation_statuses/1")
     *         )
     *     )
     * )
     */
    public function show(int $id): ReservationStatuseResource
    {
        $status = $this->reservationStatuseService->getStatus($id);

        return new ReservationStatuseResource($status);
    }

    /**
     * @OA\Post(
     * path="/reservation_statuses",
     * tags={"ReservationStatuses"},
     * summary="Добавление нового статуса броинрования",
     * description="Добавление нового статуса броинрования",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name"},
     * @OA\Property(property="name", type="string", example="pending"),
     * )
     * ),
     * @OA\Response(
     *         response=200,
     *         description="Успешное добавление статуса бронирования",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Объект статуса бронирования",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="pending"),
     *             )
     *         )
     *     ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/reservation_statuses"),
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
     * @OA\Property(property="instance", type="string", example="/api/reservation_statuses")
     * )
     * ),
     * )
     */
    public function store(StoreRequest $request): ReservationStatuseResource
    {
        $dto = $request->toDto();

        $status = $this->reservationStatuseService->createStatus($dto);

        return new ReservationStatuseResource($status);
    }

    /**
     * @OA\Patch(
     * path="/reservation_statuses/{id}",
     * tags={"ReservationStatuses"},
     * summary="Обновление нового статуса броинрования",
     * description="Обновление нового статуса броинрования",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID статуса броинрования",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name"},
     * @OA\Property(property="name", type="string", example="pending"),
     * )
     * ),
     * @OA\Response(
     *         response=200,
     *         description="Успешное обновление статуса бронирования",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Объект статуса бронирования",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="pending"),
     *             )
     *         )
     *     ),
     * @OA\Response(
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/reservation_statuses/1"),
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
     * @OA\Property(property="instance", type="string", example="/api/reservation_statuses/1")
     * )
     * ),
     * )
     */
    public function update(UpdateRequest $request, int $id): ReservationStatuseResource
    {
        $status = $this->reservationStatuseService->getStatus($id);

        $dto = $request->toDto();

        $statusUpdated = $this->reservationStatuseService->updateStatus($dto, $status);

        return new ReservationStatuseResource($statusUpdated);
    }

    /**
     * @OA\Delete(
     * path="/reservation_statuses/{id}",
     * tags={"ReservationStatuses"},
     * summary="Удаление статуса броинрования",
     * description="Удаление статуса броинрования",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID статуса броинрования",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Статус броинрования успешно удален",
     *),
     * @OA\Response(
     * response=401,
     * description="Вы не авторизованы",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/unauthorized"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=401),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу доступен только авторизованным пользователям!"),
     * @OA\Property(property="instance", type="string", example="/api/reservation_statuses/1")
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
     * @OA\Property(property="instance", type="string", example="/api/reservation_statuses/1")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description=" Тип напоминания не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/chain-not-found"),
     * @OA\Property(property="title", type="string", example="Object Not found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Тип напоминания не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/reservation_statuses/1")
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
     * @OA\Property(property="instance", type="string", example="/api/reservation_statuses/1"),
     * )
     * ),
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $this->reservationStatuseService->deleteStatus($id);

        return response()->json(null, 204);
    }
}
