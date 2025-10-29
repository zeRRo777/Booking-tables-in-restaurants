<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\IndexRequest;
use App\Http\Requests\Review\StoreRequest;
use App\Http\Resources\ReviewCollection;
use App\Http\Resources\ReviewResource;
use App\Services\ReviewService;
use Illuminate\Http\Request;


/**
 * @OA\Tag(
 * name="Reviews",
 * description="API для получение информации об отзывах в ресторане"
 * )
 */
class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService,
    ) {}


    /**
     * @OA\Get(
     *     path="/restaurants/{id}/reviews",
     *     tags={"Reviews"},
     *     summary="Получение списка отзывов для ресторана",
     *     description="Возвращает постраничный список отзывов для указанного ресторана с возможностью фильтрации и сортировки.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID ресторана",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="rating",
     *         in="query",
     *         description="Фильтрация по рейтингу",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             enum={1, 2, 3, 4, 5},
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Поле для сортировки",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"id", "rating", "created_at"},
     *             default="created_at"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Направление сортировки",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"},
     *             default="desc"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Количество элементов на странице",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное получение списка отзывов",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Массив отзывов",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Тестовый отзыв"),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         description="Автор отзыва",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Вася")
     *                     ),
     *                     @OA\Property(property="rating", type="integer", example=4),
     *                     @OA\Property(
     *                         property="restaurant",
     *                         type="object",
     *                         description="Ресторан, к которому относится отзыв",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     *                         @OA\Property(property="address", type="string", example="Тестовый address"),
     *                         @OA\Property(property="type_kitchen", type="string", example="Тестовый type_kitchen"),
     *                         @OA\Property(
     *                             property="chain",
     *                             type="object",
     *                             description="Сеть ресторанов",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Тестовая сеть")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 description="Информация о пагинации",
     *                 @OA\Property(property="total", type="integer", example=56),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=6)
     *             )
     *         )
     *     )
     * )
     */
    public function index(IndexRequest $request, int $id): ReviewCollection
    {
        $dto = $request->toDto();

        $reviews = $this->reviewService->getReviews($dto);

        return new ReviewCollection($reviews);
    }

    /**
     * @OA\GET(
     * path="/reviews/{id}",
     * tags={"Reviews"},
     * summary="Получение отзыва по id",
     * description="Получение отзыва по id",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID отзыва для получения",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное получение отзыва",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект отзыва",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый отзыв"),
     * @OA\Property(
     *   property="user",
     *   type="object",
     *   description="Автор отзыва",
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="name", type="string", example="Вася")
     * ),
     * @OA\Property(property="rating", type="integer", example=4),
     * @OA\Property(
     *   property="restaurant",
     *   type="object",
     *   description="Ресторан, к которому относится отзыв",
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     *   @OA\Property(property="address", type="string", example="Тестовый address"),
     *   @OA\Property(property="type_kitchen", type="string", example="Тестовый type_kitchen"),
     *   @OA\Property(
     *     property="chain",
     *     type="object",
     *     description="Сеть ресторанов",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Тестовая сеть")
     *   )
     * )
     * ),
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Отзыв не найден",
     * @OA\JsonContent(
     * @OA\Property(property="type", type="string", example="https://example.com/errors/not-found"),
     * @OA\Property(property="title", type="string", example="Object not Found"),
     * @OA\Property(property="status", type="integer", example=404),
     * @OA\Property(property="detail", type="string", example="Отзыв не найден!"),
     * @OA\Property(property="instance", type="string", example="/api/reviews/1")
     * )
     * ),
     * )
     */
    public function show($id): ReviewResource
    {
        $review = $this->reviewService->getReview($id);

        return new ReviewResource($review);
    }


    /**
     * @OA\POST(
     * path="/restaurants/{id}/reviews",
     * tags={"Reviews"},
     * summary="Добавление отзыва",
     * description="Добавление отзыва",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID ресторана",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * example=1
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Данные для создания",
     * @OA\JsonContent(
     * @OA\Property(property="description", type="string", example="New description"),
     * @OA\Property(property="rating", type="integer", example="1"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Успешное добавление отзыва",
     * @OA\JsonContent(
     * @OA\Property(
     * property="data",
     * type="object",
     * description="Объект отзыва",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Тестовый отзыв"),
     * @OA\Property(
     *   property="user",
     *   type="object",
     *   description="Автор отзыва",
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="name", type="string", example="Вася")
     * ),
     * @OA\Property(property="rating", type="integer", example=4),
     * @OA\Property(
     *   property="restaurant",
     *   type="object",
     *   description="Ресторан, к которому относится отзыв",
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="name", type="string", example="Тестовый ресторан"),
     *   @OA\Property(property="address", type="string", example="Тестовый address"),
     *   @OA\Property(property="type_kitchen", type="string", example="Тестовый type_kitchen"),
     *   @OA\Property(
     *     property="chain",
     *     type="object",
     *     description="Сеть ресторанов",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Тестовая сеть")
     *   )
     * )
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
     * @OA\Property(property="instance", type="string", example="/restaurants/1/reviews")
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
     * @OA\Property(property="instance", type="string", example="/restaurants/1/reviews")
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
     * @OA\Property(property="instance", type="string", example="/restaurants/1/reviews"),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="Поле name обязательно для заполнения."))),
     * )
     * ),
     * )
     */
    public function store(StoreRequest $request, int $id): ReviewResource
    {
        $dto = $request->toDto();

        $review = $this->reviewService->createReview($dto);

        return new ReviewResource($review);
    }
}
