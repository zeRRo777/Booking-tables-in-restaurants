<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\IndexRequest;
use App\Http\Resources\ReviewCollection;
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
}
