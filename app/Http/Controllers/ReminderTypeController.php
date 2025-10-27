<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReminderType\IndexRequest;
use App\Http\Resources\ReminderTypeCollection;
use App\Http\Resources\ReminderTypeResource;
use App\Services\ReminderTypeService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 * name="ReminderTypes",
 * description="API для получение информации о типах напоминаний"
 * )
 */
class ReminderTypeController extends Controller
{
    public function __construct(
        protected ReminderTypeService $reminderTypeService
    ) {}

    /**
     * @OA\Get(
     * path="/reminder_types",
     * tags={"ReminderTypes"},
     * summary="Получение списка типов напоминаний",
     * description="Получение списка типов напоминаний",
     * security={{"bearerAuth":{}}},
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
     * description="Массив типов напоминаний",
     * @OA\Items(
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="1d"),
     * @OA\Property(property="minutes", type="integer", example="1440"),
     * @OA\Property(property="is_default", type="boolean", example="true"),
     * @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09"),
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
     * response=422,
     * description="Ошибка валидации",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/validation-error"),
     * @OA\Property(property="title", type="string", example="Validation Error"),
     * @OA\Property(property="status", type="integer", example=422),
     * @OA\Property(property="detail", type="string", example="Произошла одна или несколько ошибок проверки."),
     * @OA\Property(property="instance", type="string", example="/api/reminder_types"),
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
     * @OA\Property(property="instance", type="string", example="/api/reminder_types")
     * )
     * ),
     * )
     */
    public function index(IndexRequest $request): ReminderTypeCollection
    {
        $dto = $request->toDto();

        $types = $this->reminderTypeService->getAll($dto);

        return new ReminderTypeCollection($types);
    }

    /**
     * @OA\GET(
     *     path="/reminder_types/{id}",
     *     tags={"ReminderTypes"},
     *     summary="Получение типа напоминания по id",
     *     description="Получение типа напоминания по id",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID типа напоминания",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение типа напоминания",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Объект типа напоминания",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="1d"),
     *                 @OA\Property(property="minutes", type="integer", example="1440"),
     *                 @OA\Property(property="is_default", type="boolean", example="true"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="13.10.2025 16:58:09")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Тип напоминания не найден",
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string", example="https://example.com/errors/chain-not-found"),
     *             @OA\Property(property="title", type="string", example="Chain not Found"),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="detail", type="string", example="Тип напоминания не найден!"),
     *             @OA\Property(property="instance", type="string", example="/api/reminder_types/1")
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
     *             @OA\Property(property="instance", type="string", example="/api/reminder_types/1")
     *         )
     *     )
     * )
     */
    public function show(int $id): ReminderTypeResource
    {
        $reminderType = $this->reminderTypeService->getType($id);

        return new ReminderTypeResource($reminderType);
    }
}
