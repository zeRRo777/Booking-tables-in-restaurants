<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\StoreRequest;
use App\Http\Requests\Reservation\UpdateRequest;
use App\Http\Resources\ReservationResource;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 * name="Reservations",
 * description="API для получение информации о броинрованиях"
 * )
 */
class ReservationController extends Controller
{
    public function __construct(
        protected ReservationService $reservationService,
    ) {}


    /**
     * @OA\Post(
     * path="/reservations",
     * tags={"Reservations"},
     * summary="Добавление нового бронирования",
     * description="Добавление нового бронирования",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"starts_at",
     * "ends_at",
     * "restaurant_id",
     * "count_people",
     * "reminder_type_id",
     * "table_id"
     * },
     * @OA\Property(property="special_wish", type="string", example="New special_wish"),
     * @OA\Property(property="starts_at", type="string", example="28.02.2026 18:00"),
     * @OA\Property(property="ends_at", type="string", example="28.02.2026 20:00"),
     * @OA\Property(property="user_id", type="integer", example="1"),
     * @OA\Property(property="restaurant_id", type="integer", example="1"),
     * @OA\Property(property="count_people", type="integer", example="1"),
     * @OA\Property(property="reminder_type_id", type="string", example="1d"),
     * @OA\Property(property="table_id", type="integer", example="1"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Столик успешно забронирован",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект Броинрования",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="start_at", type="string", example="28.02.2026 18:00"),
     * @OA\Property(property="ends_at", type="string", example="28.02.2026 20:00"),
     * @OA\Property(property="special_wish", type="string", example="special_wish"),
     * @OA\Property(property="count_people", type="integer", example="1"),
     * @OA\Property(
     * property="user",
     * type="object",
     * description="Объект пользователя",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="name", type="string", example="name"),
     * @OA\Property(property="email", type="string", example="test@mail.ru"),
     * @OA\Property(property="phone", type="string", example="+79190945566"),
     * @OA\Property(property="is_blocked", type="boolean", example="false"),
     * @OA\Property(property="created_at", type="string", example="20.02.2026 18:00:12"),
     * ),
     * @OA\Property(
     * property="table",
     * type="object",
     * description="Объект столика",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="number", type="integer", example="1"),
     * @OA\Property(property="capacity_min", type="integer", example="1"),
     * @OA\Property(property="capacity_max", type="integer", example="5"),
     * @OA\Property(property="zone", type="string", example="Терраса"),
     * @OA\Property(property="created_at", type="string", example="20.02.2026 18:00:12"),
     * ),
     * @OA\Property(
     * property="restaurant",
     * type="object",
     * description="Объект ресторана",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * @OA\Property(property="address", type="string", example="Тестовый адрес"),
     * ),
     * @OA\Property(
     * property="reminder_type",
     * type="object",
     * description="Объект типа напоминания",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="name", type="string", example="1d"),
     * @OA\Property(property="minutes", type="integer", example="1440"),
     * @OA\Property(property="is_default", type="boolean", example="false"),
     * @OA\Property(property="created_at", type="string", example="20.02.2026 18:00:12"),
     * ),
     * @OA\Property(property="status", type="string", example="Pending"),
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
     * @OA\Property(property="instance", type="string", example="/api/reservations"),
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
     * @OA\Property(property="instance", type="string", example="/api/reservations")
     * )
     * ),
     * )
     */
    public function store(StoreRequest $request): ReservationResource
    {
        $dto = $request->toDto();

        $reservation = $this->reservationService->createReservation($dto);

        return new ReservationResource($reservation);
    }

    /**
     * @OA\Patch(
     * path="/reservations/{id}",
     * tags={"Reservations"},
     * summary="Изменение бронирования",
     * description="Изменение бронирования",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID бронирования",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="special_wish", type="string", example="New special_wish"),
     * @OA\Property(property="starts_at", type="string", example="28.02.2026 18:00"),
     * @OA\Property(property="ends_at", type="string", example="28.02.2026 20:00"),
     * @OA\Property(property="count_people", type="integer", example="1"),
     * @OA\Property(property="reminder_type_id", type="string", example="1d"),
     * @OA\Property(property="table_id", type="integer", example="1"),
     * @OA\Property(property="status_id", type="string", example="Confirmed"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Бронь столика изменена",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект Броинрования",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="start_at", type="string", example="28.02.2026 18:00"),
     * @OA\Property(property="ends_at", type="string", example="28.02.2026 20:00"),
     * @OA\Property(property="special_wish", type="string", example="special_wish"),
     * @OA\Property(property="count_people", type="integer", example="1"),
     * @OA\Property(
     * property="user",
     * type="object",
     * description="Объект пользователя",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="name", type="string", example="name"),
     * @OA\Property(property="email", type="string", example="test@mail.ru"),
     * @OA\Property(property="phone", type="string", example="+79190945566"),
     * @OA\Property(property="is_blocked", type="boolean", example="false"),
     * @OA\Property(property="created_at", type="string", example="20.02.2026 18:00:12"),
     * ),
     * @OA\Property(
     * property="table",
     * type="object",
     * description="Объект столика",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="number", type="integer", example="1"),
     * @OA\Property(property="capacity_min", type="integer", example="1"),
     * @OA\Property(property="capacity_max", type="integer", example="5"),
     * @OA\Property(property="zone", type="string", example="Терраса"),
     * @OA\Property(property="created_at", type="string", example="20.02.2026 18:00:12"),
     * ),
     * @OA\Property(
     * property="restaurant",
     * type="object",
     * description="Объект ресторана",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * @OA\Property(property="address", type="string", example="Тестовый адрес"),
     * ),
     * @OA\Property(
     * property="reminder_type",
     * type="object",
     * description="Объект типа напоминания",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="name", type="string", example="1d"),
     * @OA\Property(property="minutes", type="integer", example="1440"),
     * @OA\Property(property="is_default", type="boolean", example="false"),
     * @OA\Property(property="created_at", type="string", example="20.02.2026 18:00:12"),
     * ),
     * @OA\Property(property="status", type="string", example="Pending"),
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
     * @OA\Property(property="instance", type="string", example="/api/reservations/1"),
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
     * @OA\Property(property="instance", type="string", example="/api/reservations/1")
     * )
     * ),
     * )
     */
    public function update(UpdateRequest $request, int $id): ReservationResource
    {
        $reservation = $this->reservationService->getReservation($id);

        Gate::authorize('update', $reservation);

        $dto = $request->toDto();

        $updateReservation = $this->reservationService->updateReservation($reservation, $dto);

        return new ReservationResource($updateReservation);
    }


    /**
     * @OA\Get(
     * path="/reservations/{id}",
     * tags={"Reservations"},
     * summary="Получение бронирования",
     * description="Получение бронирования",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID бронирования",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Бронь столика",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект Броинрования",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="start_at", type="string", example="28.02.2026 18:00"),
     * @OA\Property(property="ends_at", type="string", example="28.02.2026 20:00"),
     * @OA\Property(property="special_wish", type="string", example="special_wish"),
     * @OA\Property(property="count_people", type="integer", example="1"),
     * @OA\Property(
     * property="user",
     * type="object",
     * description="Объект пользователя",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="name", type="string", example="name"),
     * @OA\Property(property="email", type="string", example="test@mail.ru"),
     * @OA\Property(property="phone", type="string", example="+79190945566"),
     * @OA\Property(property="is_blocked", type="boolean", example="false"),
     * @OA\Property(property="created_at", type="string", example="20.02.2026 18:00:12"),
     * ),
     * @OA\Property(
     * property="table",
     * type="object",
     * description="Объект столика",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="number", type="integer", example="1"),
     * @OA\Property(property="capacity_min", type="integer", example="1"),
     * @OA\Property(property="capacity_max", type="integer", example="5"),
     * @OA\Property(property="zone", type="string", example="Терраса"),
     * @OA\Property(property="created_at", type="string", example="20.02.2026 18:00:12"),
     * ),
     * @OA\Property(
     * property="restaurant",
     * type="object",
     * description="Объект ресторана",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     * @OA\Property(property="address", type="string", example="Тестовый адрес"),
     * ),
     * @OA\Property(
     * property="reminder_type",
     * type="object",
     * description="Объект типа напоминания",
     * @OA\Property(property="id", type="integer", example="1"),
     * @OA\Property(property="name", type="string", example="1d"),
     * @OA\Property(property="minutes", type="integer", example="1440"),
     * @OA\Property(property="is_default", type="boolean", example="false"),
     * @OA\Property(property="created_at", type="string", example="20.02.2026 18:00:12"),
     * ),
     * @OA\Property(property="status", type="string", example="Pending"),
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
     * @OA\Property(property="instance", type="string", example="/api/reservations/1"),
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
     * @OA\Property(property="instance", type="string", example="/api/reservations/1")
     * )
     * ),
     * )
     */
    public function show(int $id): ReservationResource
    {
        $reservation = $this->reservationService->getReservation($id);

        Gate::authorize('view', $reservation);

        return new ReservationResource($reservation);
    }

    /**
     * @OA\Delete(
     *     path="/reservations/{id}",
     *     tags={"Reservations"},
     *     summary="Удаление бронирования",
     *     description="Полное удаление бронирования (доступно только суперадмину)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID бронирования для удаления",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Бронирование успешно удалено"
     *     ),
     * @OA\Response(
     * response=403,
     * description="Нет прав",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/forbidden"),
     * @OA\Property(property="title", type="string", example="You not authorized"),
     * @OA\Property(property="status", type="integer", example=403),
     * @OA\Property(property="detail", type="string", example="Доступ к ресурсу запрещен!"),
     * @OA\Property(property="instance", type="string", example="/api/reservations/1")
     * )
     * ),
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $reservation = $this->reservationService->getReservation($id);

        Gate::authorize('delete', $reservation);

        $this->reservationService->deleteReservation($reservation);

        return response()->json(null, 204);
    }
}
